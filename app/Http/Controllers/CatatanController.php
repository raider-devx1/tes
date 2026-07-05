<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatatanController extends Controller
{
    // ====== ROLE: SISWA PKL (mengisi catatan) ======
  public function indexSiswa(Request $request)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())
        ->when($request->filled('status'), fn ($q) => $q->where('is_approved', $request->status === 'disetujui'))
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
            'nama_pekerjaan' => 'required|string|max:255',
            'perencanaan_kegiatan' => 'required|string',
            'pelaksanaan_kegiatan' => 'required|string',
        ]);

        CatatanKegiatan::create([
            'user_id' => Auth::id(),
            'nama_pekerjaan' => $request->nama_pekerjaan,
            'perencanaan_kegiatan' => $request->perencanaan_kegiatan,
            'pelaksanaan_kegiatan' => $request->pelaksanaan_kegiatan,
        ]);

        return redirect()->route('siswa.catatan.index')->with('success', 'Catatan Kegiatan berhasil ditambahkan.');
    }

    /** Form edit catatan milik siswa yang login. */
public function editSiswa($id)
{
    // Pastikan hanya bisa mengedit catatan miliknya sendiri
    $catatan = CatatanKegiatan::where('user_id', Auth::id())->findOrFail($id);

    // Catatan yang sudah disetujui instruktur tidak boleh diubah lagi
    if ($catatan->is_approved) {
        return redirect()->route('siswa.catatan.index')
            ->with('error', 'Catatan yang sudah disetujui tidak dapat diubah.');
    }

    return view('siswa.catatan.edit', compact('catatan'));
}

/** Simpan perubahan catatan milik siswa yang login. */
public function updateSiswa(Request $request, $id)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())->findOrFail($id);

    if ($catatan->is_approved) {
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
    ]);

    return redirect()->route('siswa.catatan.index')
        ->with('success', 'Catatan Kegiatan berhasil diperbarui.');
}

/** Hapus catatan milik siswa yang login. */
public function destroySiswa($id)
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())->findOrFail($id);

    if ($catatan->is_approved) {
        return redirect()->route('siswa.catatan.index')
            ->with('error', 'Catatan yang sudah disetujui tidak dapat dihapus.');
    }

    $catatan->delete();

    return redirect()->route('siswa.catatan.index')
        ->with('success', 'Catatan Kegiatan berhasil dihapus.');
}

    // ====== ROLE: GURU PEMBIMBING (memantau catatan) ======
public function indexGuru(Request $request)
{
    $guru_id = Auth::id();

    // Query dasar: semua catatan milik siswa bimbingan guru ini (untuk rekap)
    $rekapQuery = CatatanKegiatan::whereHas('user', function ($u) use ($guru_id) {
        $u->where('guru_id', $guru_id)->where('status_pkl', 'aktif');
    });

    $rekap = [
        'total'     => (clone $rekapQuery)->count(),
        'disetujui' => (clone $rekapQuery)->where('is_approved', true)->count(),
        'menunggu'  => (clone $rekapQuery)->where('is_approved', false)->count(),
    ];

    $catatan = CatatanKegiatan::with('user')
        ->whereHas('user', function ($u) use ($guru_id, $request) {
            $u->where('guru_id', $guru_id)
                ->where('status_pkl', 'aktif');

            if ($request->filled('q')) {
                $q = $request->q;
                $u->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            }
        })
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('is_approved', $request->status === 'disetujui');
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('guru.catatan.index', compact('catatan', 'rekap'));
}

  
// ====== ROLE: INSTRUKTUR INDUSTRI (menyetujui catatan) ======
public function indexInstruktur(Request $request)
{
    $instruktur_id = Auth::id();

    // Rekap seluruh catatan siswa bimbingan aktif (tidak terpengaruh filter)
    $rekapQuery = CatatanKegiatan::whereHas('user', function ($u) use ($instruktur_id) {
        $u->where('instruktur_id', $instruktur_id)->where('status_pkl', 'aktif');
    });

    $rekap = [
        'total'     => (clone $rekapQuery)->count(),
        'disetujui' => (clone $rekapQuery)->where('is_approved', true)->count(),
        'menunggu'  => (clone $rekapQuery)->where('is_approved', false)->count(),
    ];

    $catatan = CatatanKegiatan::with('user')
        ->whereHas('user', function ($u) use ($instruktur_id, $request) {
            $u->where('instruktur_id', $instruktur_id)
                ->where('status_pkl', 'aktif');

            if ($request->filled('q')) {
                $q = $request->q;
                $u->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            }
        })
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('is_approved', $request->status === 'disetujui');
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('instruktur.catatan.index', compact('catatan', 'rekap'));
}

        /** Setujui catatan (tanpa perlu mengisi catatan instruktur). */
    public function approveInstruktur($id)
    {
        $catatan = CatatanKegiatan::findOrFail($id);

        abort_unless($catatan->user->instruktur_id === Auth::id(), 403, 'Akses ditolak.');

        $catatan->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Catatan berhasil disetujui.');
    }

    /** Batalkan persetujuan catatan (kembali ke status menunggu). */
    public function batalApproveInstruktur($id)
    {
        $catatan = CatatanKegiatan::findOrFail($id);

        abort_unless($catatan->user->instruktur_id === Auth::id(), 403, 'Akses ditolak.');

        $catatan->update(['is_approved' => false]);

        return redirect()->back()->with('success', 'Persetujuan catatan berhasil dibatalkan.');
    }

    /** Simpan / perbarui catatan instruktur (via pop-up form). */
    public function komentarInstruktur(Request $request, $id)
    {
        $catatan = CatatanKegiatan::findOrFail($id);

        abort_unless($catatan->user->instruktur_id === Auth::id(), 403, 'Akses ditolak.');

        $validated = $request->validate([
            'catatan_instruktur' => 'required|string',
        ], [
            'catatan_instruktur.required' => 'Catatan instruktur wajib diisi.',
        ]);

        $catatan->update(['catatan_instruktur' => $validated['catatan_instruktur']]);

        return redirect()->back()->with('success', 'Catatan instruktur berhasil disimpan.');
    }

}