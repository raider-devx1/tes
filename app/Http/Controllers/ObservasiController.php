<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ObservasiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROLE: GURU PEMBIMBING (mengisi lembar observasi)
    |--------------------------------------------------------------------------
    */

    public function indexGuru(Request $request)
    {
        $q = trim($request->get('q', ''));

        $baseQuery = Observasi::where('guru_id', Auth::id())
            ->whereHas('user', fn ($u) => $u->siswaBerjalan()->where('status_pkl', 'aktif'));

        $rekap = [
            'total'       => (clone $baseQuery)->count(),
            'draft'       => (clone $baseQuery)->where('status', 'draft')->count(),
            'menunggu'    => (clone $baseQuery)->where('status', 'diajukan')->count(),
            'tervalidasi' => (clone $baseQuery)->where('status', 'tervalidasi')->count(),
        ];

        $observasi = (clone $baseQuery)
            ->with(['user', 'items'])
            ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Guru yang berstatus Wakasek boleh memvalidasi lembar observasinya sendiri.
        $isWakasek = (bool) (Auth::user()->is_wakasek ?? false);

        return view('guru.observasi.index', compact('observasi', 'q', 'rekap', 'isWakasek'));
    }

    public function createGuru()
    {
        $siswas = User::siswa()
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->orderBy('name')
            ->get();

        return view('guru.observasi.create', compact('siswas'));
    }

    /**
     * Guru membuat lembar observasi baru.
     * Alur baru: cukup isi permasalahan & solusi -> tersimpan sebagai DRAFT.
     * Foto dokumentasi & foto lembar berparaf diunggah nanti pada tahap validasi.
     */
    public function storeGuru(Request $request)
    {
        $validated = $request->validate([
            'user_id'              => 'required|exists:users,id',
            'hari_tanggal'         => 'required|date',
            'pekerjaan_projek'     => 'nullable|string|max:255',
            'items'                => 'required|array|min:1',
            'items.*.permasalahan' => 'required|string',
            'items.*.solusi'       => 'required|string',
        ], [
            'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
            'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
            'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
        ]);

        $siswa = User::where('id', $validated['user_id'])
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        DB::transaction(function () use ($validated, $siswa) {
            $observasi = Observasi::create([
                'user_id'          => $siswa->id,
                'guru_id'          => Auth::id(),
                'hari_tanggal'     => $validated['hari_tanggal'],
                'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
                'status'           => 'draft',
            ]);

            foreach ($validated['items'] as $item) {
                $observasi->items()->create([
                    'permasalahan' => $item['permasalahan'],
                    'solusi'       => $item['solusi'],
                ]);
            }
        });

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Lembar observasi berhasil dibuat (status: draft). Silakan cetak draf, minta paraf instruktur & guru pembimbing, lalu lakukan validasi.');
    }

    public function editGuru($id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->with('items')
            ->firstOrFail();

        $siswas = User::siswa()
            ->where('guru_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('guru.observasi.edit', compact('observasi', 'siswas'));
    }

    /**
     * Guru mengubah isi lembar observasi.
     * Karena isinya berubah, status dikembalikan ke DRAFT & validasi lama dibatalkan
     * (harus dicetak & divalidasi ulang).
     */
    public function updateGuru(Request $request, $id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'user_id'              => 'required|exists:users,id',
            'hari_tanggal'         => 'required|date',
            'pekerjaan_projek'     => 'nullable|string|max:255',
            'items'                => 'required|array|min:1',
            'items.*.permasalahan' => 'required|string',
            'items.*.solusi'       => 'required|string',
        ], [
            'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
            'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
            'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
        ]);

        $siswa = User::where('id', $validated['user_id'])
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        DB::transaction(function () use ($observasi, $validated, $siswa) {
            $observasi->update([
                'user_id'              => $siswa->id,
                'hari_tanggal'         => $validated['hari_tanggal'],
                'pekerjaan_projek'     => $validated['pekerjaan_projek'] ?? null,
                'status'               => 'draft',
                'validated_by_guru_id' => null,
                'validated_at'         => null,
            ]);

            $observasi->items()->delete();
            foreach ($validated['items'] as $item) {
                $observasi->items()->create([
                    'permasalahan' => $item['permasalahan'],
                    'solusi'       => $item['solusi'],
                ]);
            }
        });

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Lembar observasi diperbarui. Status kembali ke draft dan perlu divalidasi ulang.');
    }

    /**
     * Validasi + penyimpanan bukti pengajuan lembar observasi (ALUR BARU).
     *
     * Guru TIDAK lagi memotret lembar observasi yang sudah diparaf. Yang dikirim:
     *   - foto_dokumentasi : BUKTI FOTO OBSERVASI (wajib, tetap berupa unggahan foto)
     *   - ttd_guru         : paraf digital guru pembimbing (kanvas di web/HP, wajib)
     *   - ttd_instruktur   : paraf digital instruktur, dibubuhkan di bawah paraf guru (wajib)
     *
     * @return array<string, mixed> data siap dipakai $observasi->update()
     */
    private function simpanBuktiObservasi(Request $request, Observasi $observasi): array
    {
        // Aturan khusus: isi input harus berupa data URL gambar hasil kanvas.
        $aturanParaf = function ($atribut, $nilai, $gagal) {
            if (! TandaTangan::valid($nilai)) {
                $gagal('Paraf digital tidak terbaca. Mohon bubuhkan ulang paraf pada kotak yang tersedia.');
            }
        };

        $request->validate([
            'foto_dokumentasi' => 'required|image|mimes:jpeg,png,jpg|max:3072',
            'ttd_guru'         => ['required', 'string', $aturanParaf],
            'ttd_instruktur'   => ['required', 'string', $aturanParaf],
        ], [
            'foto_dokumentasi.required' => 'Bukti foto observasi wajib diunggah.',
            'foto_dokumentasi.image'    => 'Bukti foto observasi harus berupa gambar.',
            'foto_dokumentasi.mimes'    => 'Format bukti foto observasi harus JPG, JPEG, atau PNG.',
            'foto_dokumentasi.max'      => 'Ukuran bukti foto observasi maksimal 3 MB.',
            'ttd_guru.required'         => 'Paraf digital guru pembimbing wajib diisi.',
            'ttd_instruktur.required'   => 'Paraf digital instruktur wajib diisi.',
        ]);

        // Bersihkan bukti lama bila mengajukan / memvalidasi ulang.
        if ($observasi->foto_dokumentasi && Storage::disk('public')->exists($observasi->foto_dokumentasi)) {
            Storage::disk('public')->delete($observasi->foto_dokumentasi);
        }
        // Foto lembar berparaf (alur lama) tidak dipakai lagi -> ikut dibersihkan.
        if ($observasi->foto_lembar_observasi && Storage::disk('public')->exists($observasi->foto_lembar_observasi)) {
            Storage::disk('public')->delete($observasi->foto_lembar_observasi);
        }
        TandaTangan::hapus($observasi->ttd_guru);
        TandaTangan::hapus($observasi->ttd_instruktur);

        $namaInstruktur = $observasi->user->instruktur->name ?? null;
        if ($namaInstruktur === 'Belum Diatur') {
            $namaInstruktur = null;
        }

        return [
            'foto_dokumentasi'         => ImageCompressor::store($request->file('foto_dokumentasi'), 'observasi/dokumentasi'),
            'foto_lembar_observasi'    => null, // alur lama dinonaktifkan
            'ttd_guru'                 => TandaTangan::simpan($request->input('ttd_guru'), 'ttd/observasi/guru'),
            'ttd_guru_nama'            => Auth::user()->name ?? null,
            'ttd_guru_signed_at'       => now(),
            'ttd_instruktur'           => TandaTangan::simpan($request->input('ttd_instruktur'), 'ttd/observasi/instruktur'),
            'ttd_instruktur_nama'      => $namaInstruktur,
            'ttd_instruktur_signed_at' => now(),
        ];
    }

    /**
     * VALIDASI oleh Guru Pembimbing.
     * Guru mengunggah bukti foto observasi + membubuhkan paraf digital guru
     * pembimbing & instruktur langsung di layar. Status -> tervalidasi.
     * Setelah ini, hasil cetak PDF menampilkan keterangan "SUDAH DIVALIDASI".
     */
    public function validasiGuru(Request $request, $id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        $data = $this->simpanBuktiObservasi($request, $observasi);

        $data['status']               = 'tervalidasi';
        $data['diajukan_at']          = $observasi->diajukan_at ?: now();
        $data['validated_by_guru_id'] = Auth::id();
        $data['validated_at']         = now();

        $observasi->update($data);

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Lembar observasi berhasil divalidasi. Hasil cetak kini menampilkan keterangan "SUDAH DIVALIDASI".');
    }

    /**
     * AJUKAN VALIDASI oleh Guru Pembimbing (alur baru).
     *
     * Guru mengunggah BUKTI FOTO OBSERVASI + membubuhkan PARAF DIGITAL langsung
     * di layar (paraf guru pembimbing, lalu paraf instruktur di bawahnya),
     * kemudian MENGAJUKAN (seperti siswa pada jurnal & catatan).
     *   - Guru biasa  : status -> diajukan (menunggu divalidasi Wakasek).
     *   - Guru Wakasek: boleh langsung validasi sendiri -> status tervalidasi.
     */
    public function ajukanGuru(Request $request, $id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        $isWakasek = (bool) (Auth::user()->is_wakasek ?? false);

        $data = $this->simpanBuktiObservasi($request, $observasi);
        $data['diajukan_at'] = now();

        if ($isWakasek) {
            // Wakasek boleh memvalidasi lembar observasinya sendiri secara langsung.
            $data['status']               = 'tervalidasi';
            $data['validated_by_guru_id'] = Auth::id();
            $data['validated_at']         = now();
            $pesan = 'Lembar observasi berhasil divalidasi (Anda berstatus Wakasek). Hasil cetak kini menampilkan keterangan "SUDAH DIVALIDASI".';
        } else {
            // Guru biasa: menunggu divalidasi oleh Wakasek.
            $data['status']               = 'diajukan';
            $data['validated_by_guru_id'] = null;
            $data['validated_at']         = null;
            $pesan = 'Lembar observasi berhasil diajukan. Status: menunggu divalidasi oleh Wakasek.';
        }

        $observasi->update($data);

        return redirect()->route('guru.observasi.index')->with('success', $pesan);
    }

    public function destroyGuru($id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        if ($observasi->foto_dokumentasi && Storage::disk('public')->exists($observasi->foto_dokumentasi)) {
            Storage::disk('public')->delete($observasi->foto_dokumentasi);
        }
        if ($observasi->foto_lembar_observasi && Storage::disk('public')->exists($observasi->foto_lembar_observasi)) {
            Storage::disk('public')->delete($observasi->foto_lembar_observasi);
        }

        // Berkas paraf digital ikut dibersihkan.
        TandaTangan::hapus($observasi->ttd_guru);
        TandaTangan::hapus($observasi->ttd_instruktur);

        $observasi->delete();

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Data observasi berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: SISWA PKL (melihat observasi)
    |--------------------------------------------------------------------------
    */

    public function indexSiswa(Request $request)
    {
        $observasi = Observasi::where('user_id', Auth::id())
            ->with(['guru', 'items'])
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('hari_tanggal', $request->tanggal))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('siswa.observasi.index', compact('observasi'));
    }
}