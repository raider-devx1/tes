<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    // ==========================================
    // ALUR AKSES PERAN: INSTRUKTUR INDUSTRI
    // ==========================================
    public function index()
    {
        $instrukturId = Auth::id();
        
        // Mengambil rekap siswa yang terdaftar di bawah perusahaan tempat instruktur bertugas
        $siswa = User::where('role', 'siswa_pkl')
                     ->where('instruktur_id', $instrukturId)
                     ->with('nilai')
                     ->get();

        return view('instruktur.nilai.index', compact('siswa'));
    }

    public function create(Request $request)
    {
        $siswaId = $request->query('siswa_id');
        
        // Memastikan ID yang dicari benar-benar user dengan role siswa_pkl
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($siswaId);

        return view('instruktur.nilai.create', compact('siswa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'soft_skill' => 'required|integer|between:1,5',
            'hard_skill' => 'required|integer|between:1,5',
            'pengembangan_hard_skill' => 'required|integer|between:1,5',
            'kewirausahaan' => 'required|integer|between:1,5',
            'catatan_rekomendasi' => 'nullable|string',
        ]);

        // Kalkulasi Rata-rata dari akumulasi skor inputan
        $rataRata = ($request->soft_skill + $request->hard_skill + $request->pengembangan_hard_skill + $request->kewirausahaan) / 4;

        Nilai::updateOrCreate(
            ['user_id' => $request->user_id],
            [
                'instruktur_id' => Auth::id(),
                'soft_skill' => $request->soft_skill,
                'hard_skill' => $request->hard_skill,
                'pengembangan_hard_skill' => $request->pengembangan_hard_skill,
                'kewirausahaan' => $request->kewirausahaan,
                'rata_rata' => $rataRata,
                'catatan_rekomendasi' => $request->catatan_rekomendasi,
            ]
        );

        return redirect()->route('instruktur.nilai.index')->with('success', 'Lembar evaluasi penilaian siswa sukses disimpan.');
    }

    // ==========================================
    // ALUR AKSES PERAN: SISWA PKL
    // ==========================================
    public function siswaIndex()
    {
        $nilai = Nilai::where('user_id', Auth::id())->with('instruktur')->first();
        return view('siswa.nilai.index', compact('nilai'));
    }

    // ==========================================
    // ALUR AKSES PERAN: GURU PEMBIMBING
    // ==========================================
    public function guruIndex()
    {
        // Menampilkan seluruh hasil capaian nilai perkembangan dari anak didik bimbingannya
        $nilaiSiswa = Nilai::whereHas('user', function ($query) {
            $query->where('guru_id', Auth::id())
                  ->where('role', 'siswa_pkl'); // Filter tambahan memastikan hanya role siswa_pkl
        })->with('user')->latest()->get();

        return view('guru.nilai.index', compact('nilaiSiswa'));
    }
}