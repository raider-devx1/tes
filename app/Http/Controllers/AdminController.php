<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Perusahaan;

class AdminController extends Controller
{
    public function indexSiswa()
    {
        // Ambil semua data sesuai role masing-masing
        $siswas = User::where('role', 'siswa_pkl')->get();
        $gurus = User::where('role', 'guru_pembimbing')->get();
        $instrukturs = User::where('role', 'instruktur_industri')->get();
        $perusahaans = Perusahaan::all();

        return view('admin.siswa.index', compact('siswas', 'gurus', 'instrukturs', 'perusahaans'));
    }

    public function updateMapping(Request $request, $id)
    {
        $siswa = User::findOrFail($id);
        
        $siswa->update([
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'perusahaan_id' => $request->perusahaan_id,
            'instruktur_id' => $request->instruktur_id,
            'guru_id' => $request->guru_id,
        ]);

        return redirect()->back()->with('success', 'Data Pemetaan Siswa berhasil disimpan!');
    }
}