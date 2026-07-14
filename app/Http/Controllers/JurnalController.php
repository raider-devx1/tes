<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JurnalController extends Controller
{
    // ============================ SISWA ============================

    public function indexSiswa(Request $request)
    {
        $jurnals = Jurnal::where('siswa_id', Auth::id())
            ->with('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status_persetujuan', $request->status))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('hari_tanggal', $request->tanggal))
            ->orderBy('hari_tanggal', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('siswa.jurnal.index', compact('jurnals'));
    }

    public function createSiswa()
    {
        return view('siswa.jurnal.create');
    }

    public function storeSiswa(Request $request)
{
    $validated = $request->validate([
        'hari_tanggal'        => 'required|date',
        'items'               => 'required|array|min:1',
        'items.*.unit_kerja'  => 'required|string',
        'items.*.dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'items.required'              => 'Minimal harus ada 1 pekerjaan / unit kerja.',
        'items.min'                   => 'Minimal harus ada 1 pekerjaan / unit kerja.',
        'items.*.unit_kerja.required' => 'Unit kerja / pekerjaan wajib diisi pada setiap poin.',
    ]);

    DB::transaction(function () use ($request, $validated) {
        $jurnal = Jurnal::create([
            'siswa_id'     => Auth::id(),
            'hari_tanggal' => $validated['hari_tanggal'],
            'status'       => 'draft', // <-- alur baru: draft dulu, boleh langsung cetak draf PDF
        ]);

        foreach ($request->input('items', []) as $i => $item) {
            $path = null;
            if ($request->hasFile("items.$i.dokumentasi")) {
                $path = $request->file("items.$i.dokumentasi")->store('dokumentasi_jurnal', 'public');
            }

            $jurnal->items()->create([
                'unit_kerja'  => $item['unit_kerja'],
                'dokumentasi' => $path,
            ]);
        }
    });

    return redirect()->route('siswa.jurnal.index')
        ->with('success', 'Jurnal harian berhasil dibuat (status: draft). Silakan cetak draf, minta paraf instruktur, lalu ajukan.');
}

    public function editSiswa($id)
    {
        // Edit selalu diizinkan (apa pun statusnya)
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())
            ->with('items')
            ->firstOrFail();

        return view('siswa.jurnal.edit', compact('jurnal'));
    }

    public function updateSiswa(Request $request, $id)
    {
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'hari_tanggal'        => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.unit_kerja'  => 'required|string',
            'items.*.dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'items.required'              => 'Minimal harus ada 1 pekerjaan / unit kerja.',
            'items.min'                   => 'Minimal harus ada 1 pekerjaan / unit kerja.',
            'items.*.unit_kerja.required' => 'Unit kerja / pekerjaan wajib diisi pada setiap poin.',
        ]);

        DB::transaction(function () use ($request, $validated, $jurnal) {
           // Setelah diedit, kembali ke draft (harus diajukan & divalidasi ulang)
$jurnal->update([
    'hari_tanggal'         => $validated['hari_tanggal'],
    'status'               => 'draft',
    'validated_by_guru_id' => null,
    'validated_at'         => null,
]);

            $keptIds = [];

            foreach ($request->input('items', []) as $i => $item) {
                $existingId  = $item['id'] ?? null;
                $existingDoc = $item['existing_dokumentasi'] ?? null;

                // Tentukan path foto: pakai yang lama, kecuali ada upload baru
                $path = $existingDoc;
                if ($request->hasFile("items.$i.dokumentasi")) {
                    if ($existingDoc) {
                        Storage::disk('public')->delete($existingDoc);
                    }
                    $path = $request->file("items.$i.dokumentasi")->store('dokumentasi_jurnal', 'public');
                }

                if ($existingId && ($jItem = $jurnal->items()->find($existingId))) {
                    $jItem->update([
                        'unit_kerja'  => $item['unit_kerja'],
                        'dokumentasi' => $path,
                    ]);
                    $keptIds[] = $jItem->id;
                } else {
                    $new = $jurnal->items()->create([
                        'unit_kerja'  => $item['unit_kerja'],
                        'dokumentasi' => $path,
                    ]);
                    $keptIds[] = $new->id;
                }
            }

            // Hapus pekerjaan yang dibuang dari form (beserta fotonya)
            $toDelete = $jurnal->items()->whereNotIn('id', $keptIds)->get();
            foreach ($toDelete as $del) {
                if ($del->dokumentasi) {
                    Storage::disk('public')->delete($del->dokumentasi);
                }
                $del->delete();
            }
        });

        return redirect()->route('siswa.jurnal.index')
            ->with('success', 'Jurnal berhasil diperbarui. Status kembali menunggu persetujuan instruktur.');
    }

    public function destroySiswa($id)
    {
        // Hapus selalu diizinkan (apa pun statusnya)
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        foreach ($jurnal->items as $item) {
            if ($item->dokumentasi) {
                Storage::disk('public')->delete($item->dokumentasi);
            }
        }

        $jurnal->delete(); // jurnal_items ikut terhapus (cascade)

        return redirect()->route('siswa.jurnal.index')
            ->with('success', 'Jurnal harian berhasil dihapus!');
    }

    // ========================== INSTRUKTUR ==========================

   public function indexInstruktur(Request $request)
{
    $siswaIds = User::where('role', 'siswa_pkl')
        ->where('instruktur_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->pluck('id');

    $jurnals = Jurnal::whereIn('siswa_id', $siswaIds)
        ->with(['siswa', 'items'])
        ->when($request->filled('q'), function ($query) use ($request) {
            $q = $request->q;
            $query->whereHas('siswa', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        })
        ->when($request->filled('status'), fn ($query) =>
            $query->where('status_persetujuan', $request->status))
        ->when($request->filled('tanggal'), fn ($query) =>
            $query->whereDate('hari_tanggal', $request->tanggal))
        ->orderBy('hari_tanggal', 'desc')
        ->paginate(15)
        ->withQueryString();

    // ---- Kartu informasi jurnal siswa bimbingan aktif (tidak terpengaruh filter) ----
    $rekapQuery = Jurnal::whereIn('siswa_id', $siswaIds);

    $rekap = [
        'total'     => (clone $rekapQuery)->count(),
        'disetujui' => (clone $rekapQuery)->where('status_persetujuan', 'disetujui')->count(),
        'pending'   => (clone $rekapQuery)->where('status_persetujuan', 'pending')->count(),
        'revisi'    => (clone $rekapQuery)->where('status_persetujuan', 'revisi')->count(),
    ];

    return view('instruktur.jurnal.index', compact('jurnals', 'rekap'));
}

    // ===================== AJUKAN (SISWA) =====================

/**
 * Siswa mengunggah foto bukti fisik + mengetik ulang catatan instruktur.
 * Validasi strict: foto_bukti & catatan_instruktur WAJIB. Status -> diajukan.
 */
public function ajukanSiswa(Request $request, $id)
{
    $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

    $validated = $request->validate([
        'catatan_instruktur' => 'required|string',
        'foto_bukti'         => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'catatan_instruktur.required' => 'Catatan/nilai dari instruktur wajib diketik ulang.',
        'foto_bukti.required'         => 'Foto bukti fisik lembar berparaf wajib diunggah.',
        'foto_bukti.image'            => 'File harus berupa gambar.',
        'foto_bukti.mimes'            => 'Format foto harus jpeg, png, atau jpg.',
        'foto_bukti.max'              => 'Ukuran foto maksimal 2MB.',
    ]);

    // Ganti foto lama bila ada
    if ($jurnal->foto_bukti) {
        Storage::disk('public')->delete($jurnal->foto_bukti);
    }
    $path = $request->file('foto_bukti')->store('bukti_fisik/jurnal', 'public');

    $jurnal->update([
        'catatan_instruktur' => $validated['catatan_instruktur'],
        'foto_bukti'         => $path,
        'status'             => 'diajukan',
    ]);

    return redirect()->route('siswa.jurnal.index')
        ->with('success', 'Jurnal berhasil diajukan ke Guru Pembimbing untuk divalidasi.');
}

// ===================== VALIDASI (GURU) =====================

/**
 * Guru Pembimbing memvalidasi. aksi = 'valid' -> disetujui, 'tolak' -> kembali draft.
 */
public function validasiByGuru(Request $request, $id)
{
    $jurnal = Jurnal::with('siswa')->findOrFail($id);

    // Pastikan jurnal milik siswa bimbingan guru yang login
    abort_unless(
        $jurnal->siswa && (int) $jurnal->siswa->guru_id === (int) Auth::id(),
        403,
        'Akses ditolak: jurnal ini bukan milik siswa bimbingan Anda.'
    );

    $aksi = $request->input('aksi', 'valid');

    if ($aksi === 'tolak') {
        $jurnal->update([
            'status'               => 'draft',
            'validated_by_guru_id' => null,
            'validated_at'         => null,
        ]);

        return redirect()->back()->with('success', 'Pengajuan ditolak. Jurnal dikembalikan ke siswa (status draft).');
    }

    $jurnal->update([
        'status'               => 'disetujui',
        'validated_by_guru_id' => Auth::id(),
        'validated_at'         => now(),
    ]);

    return redirect()->back()->with('success', 'Jurnal berhasil divalidasi (disetujui).');
}

}