<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatatanInstrukturController extends Controller
{
    public function index()
    {
        $instruktur_id = Auth::id();
        // Mengambil catatan siswa di perusahaan/instruktur yang sama
        $catatan = CatatanKegiatan::whereHas('user', function($q) use ($instruktur_id) {
            $q->where('instruktur_id', $instruktur_id);
        })->with('user')->latest()->get();

        return view('instruktur.catatan.index', compact('catatan'));
    }

    public function approve(Request $request, $id)
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