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
        $catatan = CatatanKegiatan::where('user_id', Auth::id())->latest()->get();
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
    public function indexGuru()
    {
        $guru_id = Auth::id();
        $catatan = CatatanKegiatan::whereHas('user', function ($q) use ($guru_id) {
            $q->where('guru_id', $guru_id);
        })->with('user')->latest()->get();

        return view('guru.catatan.index', compact('catatan'));
    }

    // ====== ROLE: INSTRUKTUR INDUSTRI (menyetujui catatan) ======
    public function indexInstruktur()
    {
        $instruktur_id = Auth::id();
        $catatan = CatatanKegiatan::whereHas('user', function ($q) use ($instruktur_id) {
            $q->where('instruktur_id', $instruktur_id);
        })->with('user')->latest()->get();

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