<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservasiController extends Controller
{
    // ====== ROLE: GURU PEMBIMBING (mengisi observasi) ======
    public function indexGuru()
    {
        $observasis = Observasi::where('guru_id', Auth::id())->with('user')->latest()->get();
        return view('guru.observasi.index', compact('observasis'));
    }

    public function createGuru()
    {
        $siswa = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->get();
        return view('guru.observasi.create', compact('siswa'));
    }

    public function storeGuru(Request $request)
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

    // ====== ROLE: SISWA PKL (melihat observasi) ======
    public function indexSiswa()
    {
        $observasi = Observasi::where('user_id', Auth::id())->with('guru')->latest()->get();
        return view('siswa.observasi.index', compact('observasi'));
    }

    // ====== ROLE: INSTRUKTUR INDUSTRI (menyetujui observasi) ======
    public function indexInstruktur()
    {
        $instruktur_id = Auth::id();
        $observasi = Observasi::whereHas('user', function ($q) use ($instruktur_id) {
            $q->where('instruktur_id', $instruktur_id);
        })->with(['user', 'guru'])->latest()->get();

        return view('instruktur.observasi.index', compact('observasi'));
    }

    public function approveInstruktur($id)
    {
        $observasi = Observasi::findOrFail($id);
        $observasi->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Observasi berhasil disetujui.');
    }
}