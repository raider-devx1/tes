<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use App\Support\ImageCompressor;
use App\Support\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CatatanController extends Controller
{
    // ====== ROLE: SISWA PKL (mengisi catatan) ======
 public function indexSiswa(Request $request)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())
        ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
        ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('created_at', $request->tanggal))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('siswa.catatan.index', compact('catatan'));
}

    public function createSiswa()
    {
        return view('siswa.catatan.create');
    }

    public function storeSiswa(Request $request)
{
    $request->validate([
        'nama_pekerjaan'       => 'required|string|max:255',
        'perencanaan_kegiatan' => 'required|string',
        'pelaksanaan_kegiatan' => 'required|string',
    ]);

    CatatanKegiatan::create([
        'user_id'              => Auth::id(),
        'nama_pekerjaan'       => $request->nama_pekerjaan,
        'perencanaan_kegiatan' => $request->perencanaan_kegiatan,
        'pelaksanaan_kegiatan' => $request->pelaksanaan_kegiatan,
        'status'               => 'draft',
    ]);

    return redirect()->route('siswa.catatan.index')
        ->with('success', 'Catatan Kegiatan berhasil dibuat (draft). Cetak draf, minta paraf instruktur, lalu ajukan.');
}

   public function editSiswa($id)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())->findOrFail($id);

    if ($catatan->status === 'disetujui') {
        return redirect()->route('siswa.catatan.index')
            ->with('error', 'Catatan yang sudah disetujui tidak dapat diubah.');
    }

    return view('siswa.catatan.edit', compact('catatan'));
}

public function updateSiswa(Request $request, $id)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())->findOrFail($id);

    if ($catatan->status === 'disetujui') {
        return redirect()->route('siswa.catatan.index')
            ->with('error', 'Catatan yang sudah disetujui tidak dapat diubah.');
    }

    $request->validate([
        'nama_pekerjaan'       => 'required|string|max:255',
        'perencanaan_kegiatan' => 'required|string',
        'pelaksanaan_kegiatan' => 'required|string',
    ]);

    $catatan->update([
        'nama_pekerjaan'       => $request->nama_pekerjaan,
        'perencanaan_kegiatan' => $request->perencanaan_kegiatan,
        'pelaksanaan_kegiatan' => $request->pelaksanaan_kegiatan,
        'status'               => 'draft', // edit -> kembali draft
        'validated_by_guru_id' => null,
        'validated_at'         => null,
    ]);

    return redirect()->route('siswa.catatan.index')
        ->with('success', 'Catatan Kegiatan berhasil diperbarui (status kembali draft).');
}

public function destroySiswa($id)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())->findOrFail($id);

    if ($catatan->status === 'disetujui') {
        return redirect()->route('siswa.catatan.index')
            ->with('error', 'Catatan yang sudah disetujui tidak dapat dihapus.');
    }

    // Bersihkan berkas: foto bukti versi lama + tanda tangan digital
    if ($catatan->foto_bukti) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($catatan->foto_bukti);
    }

    TandaTangan::hapus($catatan->ttd_instruktur);

    $catatan->delete();

    return redirect()->route('siswa.catatan.index')
        ->with('success', 'Catatan Kegiatan berhasil dihapus.');
}

    // ====== ROLE: GURU PEMBIMBING (memantau catatan) ======
public function indexGuru(Request $request)
{
    $guru_id = Auth::id();

    // Rekap seluruh catatan siswa bimbingan aktif (tidak terpengaruh filter)
    $rekapQuery = CatatanKegiatan::whereHas('user', function ($u) use ($guru_id) {
        $u->siswaBerjalan()->where('guru_id', $guru_id)->where('status_pkl', 'aktif');
    });

    $rekap = [
        'total'     => (clone $rekapQuery)->count(),
        'disetujui' => (clone $rekapQuery)->where('status', 'disetujui')->count(),
        'diajukan'  => (clone $rekapQuery)->where('status', 'diajukan')->count(),
        'draft'     => (clone $rekapQuery)->where('status', 'draft')->count(),
    ];

    $catatan = CatatanKegiatan::with('user')
        ->whereHas('user', function ($u) use ($guru_id, $request) {
            $u->siswaBerjalan()
                ->where('guru_id', $guru_id)
                ->where('status_pkl', 'aktif');

            if ($request->filled('q')) {
                $q = $request->q;
                $u->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            }
        })
        ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('guru.catatan.index', compact('catatan', 'rekap'));
}

  
// ===================== AJUKAN (SISWA) =====================
/**
 * ALUR BARU: tanpa unggah foto lembar berparaf.
 * Instruktur menandatangani langsung di web/HP (kanvas tanda tangan digital).
 */
public function ajukanSiswa(Request $request, $id)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())->findOrFail($id);

    $validated = $request->validate([
        'catatan_instruktur'  => 'required|string',
        'ttd_instruktur'      => ['required', 'string', function ($atribut, $nilai, $gagal) {
            if (! TandaTangan::valid($nilai)) {
                $gagal('Tanda tangan digital tidak terbaca. Mohon minta instruktur menandatangani ulang.');
            }
        }],
    ], [
        'catatan_instruktur.required' => 'Catatan/nilai dari instruktur wajib diketik ulang.',
        'ttd_instruktur.required'     => 'Tanda tangan digital instruktur wajib diisi.',
    ]);

    $path = TandaTangan::simpan($validated['ttd_instruktur'], 'ttd/catatan');

    if (! $path) {
        return back()->with('error', 'Tanda tangan digital gagal disimpan. Silakan coba lagi.');
    }

    // Pengajuan ulang: buang tanda tangan lama
    TandaTangan::hapus($catatan->ttd_instruktur);

    // Nama instruktur TIDAK diketik lagi: diambil otomatis dari data perusahaan siswa.
    $namaTtd = Auth::user()->instruktur->name ?? null;
    $namaTtd = $namaTtd === 'Belum Diatur' ? null : $namaTtd;

    $catatan->update([
        'catatan_instruktur'  => $validated['catatan_instruktur'],
        'ttd_instruktur'      => $path,
        'ttd_instruktur_nama' => $namaTtd,
        'ttd_signed_at'       => now(),
        'status'              => 'diajukan',
    ]);

    return redirect()->route('siswa.catatan.index')
        ->with('success', 'Catatan berhasil diajukan. Tanda tangan instruktur otomatis tercetak pada bagian paraf.');
}

// ===================== VALIDASI (GURU) =====================
public function validasiByGuru(Request $request, $id)
{
    $catatan = CatatanKegiatan::with('user')->findOrFail($id);

    abort_unless(
        $catatan->user && (int) $catatan->user->guru_id === (int) Auth::id(),
        403,
        'Akses ditolak: catatan ini bukan milik siswa bimbingan Anda.'
    );

    $aksi = $request->input('aksi', 'valid');

    if ($aksi === 'tolak') {
        $catatan->update([
            'status'               => 'draft',
            'validated_by_guru_id' => null,
            'validated_at'         => null,
        ]);

        return redirect()->back()->with('success', 'Pengajuan ditolak. Catatan dikembalikan ke siswa (draft).');
    }

    $catatan->update([
        'status'               => 'disetujui',
        'is_approved'          => true, // sinkron kolom lama
        'validated_by_guru_id' => Auth::id(),
        'validated_at'         => now(),
    ]);

    return redirect()->back()->with('success', 'Catatan Kegiatan berhasil divalidasi (disetujui).');
}

}