<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatatanSiswaController extends Controller
{
    public function index()
    {
        $catatan = CatatanKegiatan::where('user_id', Auth::id())->latest()->get();
        return view('siswa.catatan.index', compact('catatan'));
    }

    public function create()
    {
        return view('siswa.catatan.create');
    }

    public function store(Request $request)
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
}