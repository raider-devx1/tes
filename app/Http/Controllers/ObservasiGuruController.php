<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservasiGuruController extends Controller
{
    public function index()
    {
        // Mengubah nama variabel menjadi jamak ($observasis) agar konsisten dengan view
        $observasis = Observasi::where('guru_id', Auth::id())->with('user')->latest()->get();
        return view('guru.observasi.index', compact('observasis'));
    }

    public function create()
    {
        // Role siswa_pkl (sebelumnya tertulis 'siswa' yang membuat daftar menjadi kosong)
        $siswa = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->get();
        return view('guru.observasi.create', compact('siswa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'hari_tanggal' => 'required|date',
            'pekerjaan_projek' => 'nullable|string|max:255',
            'permasalahan' => 'required|string',
            'solusi' => 'required|string',
        ]);

        Observasi::create([
            'user_id' => $request->user_id,
            'guru_id' => Auth::id(),
            'hari_tanggal' => $request->hari_tanggal,
            'pekerjaan_projek' => $request->pekerjaan_projek,
            'permasalahan' => $request->permasalahan,
            'solusi' => $request->solusi,
            'is_approved' => false,
        ]);

        return redirect()->route('guru.observasi.index')->with('success', 'Data observasi berhasil disimpan.');
    }
}