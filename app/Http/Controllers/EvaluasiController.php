<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EvaluasiController extends Controller
{
    /** Opsi filter kelas & jurusan dari seluruh siswa PKL. */
    private function opsiFilter(): array
    {
        $kelasList = User::where('role', 'siswa_pkl')
            ->whereNotNull('kelas')->where('kelas', '!=', '')
            ->distinct()->orderBy('kelas')->pluck('kelas');

        $jurusanList = User::where('role', 'siswa_pkl')
            ->whereNotNull('jurusan')->where('jurusan', '!=', '')
            ->distinct()->orderBy('jurusan')->pluck('jurusan');

        return [$kelasList, $jurusanList];
    }

    /** Daftar siswa PKL untuk pencocokan NISN pada modal tambah/edit. */
    private function siswaList()
    {
        return User::where('role', 'siswa_pkl')
            ->where('status_pkl', '!=', 'selesai')
            ->orderBy('name')
            ->get(['id', 'name', 'nisn']);
    }

    /*
    |--------------------------------------------------------------------------
    | OBSERVASI — Evaluasi Lembar Observasi Guru
    |--------------------------------------------------------------------------
    */
   /*
|--------------------------------------------------------------------------
| EVALUASI — LEMBAR OBSERVASI (admin: full akses, sama seperti guru)
|--------------------------------------------------------------------------
*/

public function observasi(Request $request)
{
    [$kelasList, $jurusanList] = $this->opsiFilter();

    $query = Observasi::with(['user', 'guru', 'items'])
        ->whereHas('user', fn ($u) => $u->where('role', 'siswa_pkl')->where('status_pkl', '!=', 'selesai'))
        ->when($request->filled('q'), function ($q) use ($request) {
            $cari = trim($request->q);
            $q->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$cari}%")
                ->orWhere('nisn', 'like', "%{$cari}%"));
        })
        ->when($request->filled('kelas'), fn ($q) => $q
            ->whereHas('user', fn ($u) => $u->where('kelas', $request->kelas)))
        ->when($request->filled('jurusan'), fn ($q) => $q
            ->whereHas('user', fn ($u) => $u->where('jurusan', $request->jurusan)))
        ->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status === '1') {
                $q->where('status', 'tervalidasi');
            } elseif ($request->status === '0') {
                $q->where('status', '!=', 'tervalidasi');
            }
        })
        ->latest('hari_tanggal');

    $observasi = (clone $query)->paginate(15)->withQueryString();

    $baseRekap = Observasi::whereHas('user', fn ($u) => $u->where('role', 'siswa_pkl')->where('status_pkl', '!=', 'selesai'));
    $rekap = [
        'total'     => (clone $baseRekap)->count(),
        'disetujui' => (clone $baseRekap)->where('status', 'tervalidasi')->count(),
        'menunggu'  => (clone $baseRekap)->where('status', '!=', 'tervalidasi')->count(),
    ];

    $jumlahGuru = User::where('role', 'guru')->count();
    $siswaList  = $this->siswaList();   // <-- INI yang kurang

    return view('admin.evaluasi.observasi', compact(
        'observasi', 'rekap', 'jumlahGuru', 'kelasList', 'jurusanList', 'siswaList'
    ));
}

public function storeObservasi(Request $request)
{
    $validated = $request->validate([
        'user_id'              => 'required|exists:users,id',
        'hari_tanggal'         => 'required|date',
        'pekerjaan_projek'     => 'nullable|string|max:255',
        'items'                => 'required|array|min:1',
        'items.*.permasalahan' => 'required|string',
        'items.*.solusi'       => 'required|string',
    ], [
        'user_id.required'              => 'Siswa (NISN) wajib dipilih.',
        'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
        'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
        'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
    ]);

    $siswa = User::where('id', $validated['user_id'])
        ->where('role', 'siswa_pkl')
        ->firstOrFail();

    DB::transaction(function () use ($validated, $siswa) {
        $observasi = Observasi::create([
            'user_id'          => $siswa->id,
            'guru_id'          => $siswa->guru_id,
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

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Lembar observasi berhasil ditambahkan (status: menunggu). Lakukan validasi untuk mengesahkan.');
}

public function updateObservasi(Request $request, Observasi $observasi)
{
    $validated = $request->validate([
        'user_id'              => 'required|exists:users,id',
        'hari_tanggal'         => 'required|date',
        'pekerjaan_projek'     => 'nullable|string|max:255',
        'items'                => 'required|array|min:1',
        'items.*.permasalahan' => 'required|string',
        'items.*.solusi'       => 'required|string',
    ], [
        'user_id.required'              => 'Siswa (NISN) wajib dipilih.',
        'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
        'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
        'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
    ]);

    $siswa = User::where('id', $validated['user_id'])
        ->where('role', 'siswa_pkl')
        ->firstOrFail();

    DB::transaction(function () use ($observasi, $validated, $siswa) {
        // Isi diubah -> status kembali ke menunggu & validasi lama dibatalkan
        $observasi->update([
            'user_id'              => $siswa->id,
            'guru_id'              => $siswa->guru_id,
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

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Lembar observasi diperbarui. Status kembali ke menunggu dan perlu divalidasi ulang.');
}

/**
 * VALIDASI oleh Admin — unggah foto dokumentasi kegiatan + foto lembar
 * observasi yang sudah diparaf instruktur & guru. Status -> tervalidasi.
 */
public function validasiObservasi(Request $request, Observasi $observasi)
{
    $request->validate([
        'foto_dokumentasi'      => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'foto_lembar_observasi' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'foto_dokumentasi.required'      => 'Foto dokumentasi kegiatan/kunjungan wajib diunggah.',
        'foto_dokumentasi.image'         => 'Foto dokumentasi harus berupa gambar.',
        'foto_dokumentasi.mimes'         => 'Format foto dokumentasi harus JPG, JPEG, atau PNG.',
        'foto_dokumentasi.max'           => 'Ukuran foto dokumentasi maksimal 2 MB.',
        'foto_lembar_observasi.required' => 'Foto lembar observasi yang sudah diparaf wajib diunggah.',
        'foto_lembar_observasi.image'    => 'Foto lembar observasi harus berupa gambar.',
        'foto_lembar_observasi.mimes'    => 'Format foto lembar observasi harus JPG, JPEG, atau PNG.',
        'foto_lembar_observasi.max'      => 'Ukuran foto lembar observasi maksimal 2 MB.',
    ]);

    // Hapus foto lama bila validasi ulang
    if ($observasi->foto_dokumentasi && Storage::disk('public')->exists($observasi->foto_dokumentasi)) {
        Storage::disk('public')->delete($observasi->foto_dokumentasi);
    }
    if ($observasi->foto_lembar_observasi && Storage::disk('public')->exists($observasi->foto_lembar_observasi)) {
        Storage::disk('public')->delete($observasi->foto_lembar_observasi);
    }

    $observasi->update([
        'foto_dokumentasi'      => $request->file('foto_dokumentasi')->store('observasi/dokumentasi', 'public'),
        'foto_lembar_observasi' => $request->file('foto_lembar_observasi')->store('observasi/lembar', 'public'),
        'status'                => 'tervalidasi',
        'validated_by_guru_id'  => $observasi->guru_id ?? Auth::id(),
        'validated_at'          => now(),
    ]);

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Lembar observasi berhasil divalidasi. Hasil cetak kini menampilkan keterangan "SUDAH DIVALIDASI".');
}

/**
 * BATALKAN VALIDASI oleh Admin — status kembali ke menunggu.
 * Foto tetap disimpan agar mudah divalidasi ulang.
 */
public function batalValidasiObservasi(Observasi $observasi)
{
    $observasi->update([
        'status'               => 'draft',
        'validated_by_guru_id' => null,
        'validated_at'         => null,
    ]);

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Validasi lembar observasi dibatalkan. Status kembali ke menunggu.');
}

public function destroyObservasi(Observasi $observasi)
{
    if ($observasi->foto_dokumentasi && Storage::disk('public')->exists($observasi->foto_dokumentasi)) {
        Storage::disk('public')->delete($observasi->foto_dokumentasi);
    }
    if ($observasi->foto_lembar_observasi && Storage::disk('public')->exists($observasi->foto_lembar_observasi)) {
        Storage::disk('public')->delete($observasi->foto_lembar_observasi);
    }

    $observasi->delete();

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Data observasi berhasil dihapus.');
}

    /*
    |--------------------------------------------------------------------------
    | PENILAIAN — Rekap & Penilaian Siswa PKL (sistem guru 6 komponen 0–100)
    |--------------------------------------------------------------------------
    */
    public function penilaian(Request $request)
    {
        [$kelasList, $jurusanList] = $this->opsiFilter();

        $q       = trim($request->get('q', ''));
        $kelas   = $request->get('kelas');
        $jurusan = $request->get('jurusan');
        $status  = $request->get('status'); // 'sudah' | 'belum'

        $total = User::where('role', 'siswa_pkl')->where('status_pkl', '!=', 'selesai')->count();
        $sudah = User::where('role', 'siswa_pkl')->where('status_pkl', '!=', 'selesai')
            ->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir'))->count();

        $rekap = ['total' => $total, 'sudah' => $sudah, 'belum' => $total - $sudah];

        $siswa = User::where('role', 'siswa_pkl')
            ->where('status_pkl', '!=', 'selesai')
            ->with(['nilai', 'guru'])
            ->when($q !== '', fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas, fn ($query) => $query->where('kelas', $kelas))
            ->when($jurusan, fn ($query) => $query->where('jurusan', $jurusan))
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir')))
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('nilai_akhir'))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $siswaList = $this->siswaList();

        return view('admin.evaluasi.penilaian', compact(
            'siswa', 'rekap', 'kelasList', 'jurusanList', 'siswaList'
        ));
    }

    /** Nilai akhir = rata-rata 6 komponen (0–100). Null bila belum lengkap. */
    private function hitungRataRataAkhir(Nilai $nilai): ?float
    {
        $daftarSkor = [
            $nilai->skor_soft_skill,
            $nilai->skor_hard_skill,
            $nilai->skor_pengembangan,
            $nilai->skor_kewirausahaan,
            $nilai->skor_laporan,
            $nilai->skor_presentasi,
        ];

        if (in_array(null, $daftarSkor, true)) {
            return null;
        }

        return round(array_sum($daftarSkor) / count($daftarSkor), 2);
    }

    private function aturanPenilaian(): array
    {
        return [
            'user_id'                 => 'required|exists:users,id',
            'skor_soft_skill'         => 'required|numeric|between:0,100',
            'deskripsi_soft_skill'    => 'required|string',
            'skor_hard_skill'         => 'required|numeric|between:0,100',
            'deskripsi_hard_skill'    => 'required|string',
            'skor_pengembangan'       => 'required|numeric|between:0,100',
            'deskripsi_pengembangan'  => 'required|string',
            'skor_kewirausahaan'      => 'required|numeric|between:0,100',
            'deskripsi_kewirausahaan' => 'required|string',
            'skor_laporan'            => 'required|numeric|between:0,100',
            'deskripsi_laporan'       => 'required|string',
            'skor_presentasi'         => 'required|numeric|between:0,100',
            'deskripsi_presentasi'    => 'required|string',
            'catatan_guru'            => 'nullable|string',
            'foto_lembar_instruktur'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /** Isi seluruh komponen nilai (sistem guru) + hitung nilai akhir. */
    private function isiNilai(Nilai $nilai, Request $request, User $siswa): void
    {
        $nilai->user_id = $siswa->id;
        // Instruktur kini bukan akun; guru penilai diambil dari data siswa bila belum tercatat.
        $nilai->guru_id = $nilai->guru_id ?? $siswa->guru_id;

        $nilai->skor_soft_skill         = $request->skor_soft_skill;
        $nilai->deskripsi_soft_skill    = $request->deskripsi_soft_skill;
        $nilai->skor_hard_skill         = $request->skor_hard_skill;
        $nilai->deskripsi_hard_skill    = $request->deskripsi_hard_skill;
        $nilai->skor_pengembangan       = $request->skor_pengembangan;
        $nilai->deskripsi_pengembangan  = $request->deskripsi_pengembangan;
        $nilai->skor_kewirausahaan      = $request->skor_kewirausahaan;
        $nilai->deskripsi_kewirausahaan = $request->deskripsi_kewirausahaan;
        $nilai->skor_laporan            = $request->skor_laporan;
        $nilai->deskripsi_laporan       = $request->deskripsi_laporan;
        $nilai->skor_presentasi         = $request->skor_presentasi;
        $nilai->deskripsi_presentasi    = $request->deskripsi_presentasi;
        $nilai->catatan_guru            = $request->catatan_guru;

        if ($request->hasFile('foto_lembar_instruktur')) {
            if ($nilai->foto_lembar_instruktur && Storage::disk('public')->exists($nilai->foto_lembar_instruktur)) {
                Storage::disk('public')->delete($nilai->foto_lembar_instruktur);
            }
            $nilai->foto_lembar_instruktur = $request->file('foto_lembar_instruktur')
                ->store('nilai/lembar-instruktur', 'public');
        }

        // Nilai akhir = rata-rata 6 komponen (0–100)
        $nilai->nilai_akhir   = $this->hitungRataRataAkhir($nilai);
        $nilai->nilai_guru    = $nilai->nilai_akhir;    // kompatibilitas kolom lama
        $nilai->nilai_laporan = $request->skor_laporan; // kompatibilitas kolom lama
    }

    public function storePenilaian(Request $request)
    {
        $request->validate($this->aturanPenilaian());
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($request->user_id);

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);
        $this->isiNilai($nilai, $request, $siswa);
        $nilai->save();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Penilaian siswa berhasil disimpan.');
    }

    public function updatePenilaian(Request $request, Nilai $nilai)
    {
        $request->validate($this->aturanPenilaian());
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($request->user_id);

        $this->isiNilai($nilai, $request, $siswa);
        $nilai->save();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Penilaian siswa berhasil diperbarui.');
    }

    public function destroyPenilaian(Nilai $nilai)
    {
        if ($nilai->foto_lembar_instruktur && Storage::disk('public')->exists($nilai->foto_lembar_instruktur)) {
            Storage::disk('public')->delete($nilai->foto_lembar_instruktur);
        }
        $nilai->delete();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Data penilaian berhasil dihapus.');
    }
}