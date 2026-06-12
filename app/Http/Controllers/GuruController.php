<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Jurnal;
use App\Models\Absensi;
use App\Models\Observasi;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    // Menampilkan daftar siswa yang dibimbing oleh guru ini
    public function index()
    {
        $siswas = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->get();
        return view('guru.siswa.index', compact('siswas'));
    }

    // Menampilkan Jurnal dan Absensi dari satu siswa spesifik
    public function detailSiswa($id)
    {
        $siswa = User::findOrFail($id);
        
        // Pastikan guru hanya bisa melihat siswa bimbingannya
        if ($siswa->guru_id !== Auth::id()) {
            abort(403, 'Akses Ditolak: Bukan siswa bimbingan Anda.');
        }

        $jurnals = Jurnal::where('siswa_id', $id)->orderBy('hari_tanggal', 'desc')->get();
        $absensis = Absensi::where('siswa_id', $id)->orderBy('tanggal', 'desc')->get();

        return view('guru.siswa.detail', compact('siswa', 'jurnals', 'absensis'));
    }

    // Menampilkan halaman Lembar Observasi
    public function observasiIndex($id)
    {
        $siswa = User::findOrFail($id);
        
        if ($siswa->guru_id !== Auth::id()) {
            abort(403, 'Akses Ditolak: Bukan siswa bimbingan Anda.');
        }

        $observasis = Observasi::where('siswa_id', $id)->orderBy('tanggal', 'desc')->get();

        return view('guru.observasi.index', compact('siswa', 'observasis'));
    }

    // Menyimpan data Lembar Observasi baru
    public function observasiStore(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'permasalahan' => 'required|string',
            'solusi' => 'required|string',
        ]);

        Observasi::create([
            'siswa_id' => $id,
            'guru_id' => Auth::id(),
            'tanggal' => $request->tanggal,
            'permasalahan' => $request->permasalahan,
            'solusi' => $request->solusi,
            'status_persetujuan' => 'pending' // Menunggu disetujui instruktur industri
        ]);

        return redirect()->back()->with('success', 'Lembar observasi berhasil ditambahkan!');
    }
}