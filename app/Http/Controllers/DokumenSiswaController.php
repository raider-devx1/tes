<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;
use App\Models\Nilai;
use Illuminate\Support\Facades\Auth;

class DokumenSiswaController extends Controller
{
    public function index()
    {
        $dokumen = Dokumen::where('siswa_id', Auth::id())->first();
        $nilai   = Nilai::where('user_id', Auth::id())->first(); // ✅ FIX: siswa_id -> user_id
        return view('siswa.dokumen.index', compact('dokumen', 'nilai'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'laporan_akhir'    => 'nullable|mimes:pdf|max:5120',
            'surat_tugas'      => 'nullable|mimes:pdf|max:2048',
            'surat_penerimaan' => 'nullable|mimes:pdf|max:2048',
        ]);

        $dokumen = Dokumen::firstOrNew(['siswa_id' => Auth::id()]);

        if ($request->hasFile('laporan_akhir')) {
            $dokumen->laporan_akhir = $request->file('laporan_akhir')->store('dokumen_pkl', 'public');
        }
        if ($request->hasFile('surat_tugas')) {
            $dokumen->surat_tugas = $request->file('surat_tugas')->store('dokumen_pkl', 'public');
        }
        if ($request->hasFile('surat_penerimaan')) {
            $dokumen->surat_penerimaan = $request->file('surat_penerimaan')->store('dokumen_pkl', 'public');
        }

        $dokumen->save();
        return redirect()->back()->with('success', 'Dokumen berhasil diunggah!');
    }
}