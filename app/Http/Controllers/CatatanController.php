<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatatanController extends Controller
{
    // ====== ROLE: SISWA PKL (mengisi catatan) ======
   public function indexSiswa()
{
    $catatan = CatatanKegiatan::where('user_id', Auth::id())
        ->latest()->paginate(15)->withQueryString();
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

    // ====== ROLE: GURU PEMBIMBING (memantau catatan) ======
public function indexGuru(Request $request)
{
    $guru_id = Auth::id();

    $catatan = CatatanKegiatan::with('user')
        ->whereHas('user', function ($u) use ($guru_id, $request) {
            $u->where('guru_id', $guru_id);

            // Filter pencarian: Nama / NISN siswa
            if ($request->filled('q')) {
                $q = $request->q;
                $u->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            }
        })
        // Filter dropdown: Status persetujuan
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('is_approved', $request->status === 'disetujui');
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('guru.catatan.index', compact('catatan'));
}

    // ====== ROLE: INSTRUKTUR INDUSTRI (menyetujui catatan) ======
   // ====== ROLE: INSTRUKTUR INDUSTRI (menyetujui catatan) ======
public function indexInstruktur(Request $request)
{
    $instruktur_id = Auth::id();

    $catatan = CatatanKegiatan::with('user')
        ->whereHas('user', function ($u) use ($instruktur_id, $request) {
            $u->where('instruktur_id', $instruktur_id);

            // Filter pencarian: Nama / NISN siswa
            if ($request->filled('q')) {
                $q = $request->q;
                $u->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            }
        })
        // Filter dropdown: status (disetujui | belum)
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('is_approved', $request->status === 'disetujui');
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('instruktur.catatan.index', compact('catatan'));
}

    public function approveInstruktur(Request $request, $id)
    {
        $catatan = CatatanKegiatan::findOrFail($id);

        $request->validate([
            'catatan_instruktur' => 'nullable|string',
        ]);

        $catatan->update([
            'catatan_instruktur' => $request->catatan_instruktur,
            'is_approved' => true,
        ]);

        return redirect()->back()->with('success', 'Catatan berhasil disetujui dan diberi tanggapan.');
    }
}