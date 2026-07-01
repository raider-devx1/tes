<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservasiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROLE: GURU PEMBIMBING (mengisi lembar observasi)
    |--------------------------------------------------------------------------
    */

    /** Daftar seluruh observasi yang dibuat guru ini. */
   public function indexGuru()
{
    $observasi = Observasi::where('guru_id', Auth::id())
        ->with('user')->latest()->paginate(15)->withQueryString();
    return view('guru.observasi.index', compact('observasi'));
}

    /** Form tambah observasi (hanya siswa bimbingan guru ini yang bisa dipilih). */
    public function createGuru()
    {
        $siswas = User::where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('guru.observasi.create', compact('siswas'));
    }

    /** Simpan observasi baru. */
    public function storeGuru(Request $request)
    {
        $validated = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'hari_tanggal'     => 'required|date',
            'pekerjaan_projek' => 'nullable|string|max:255',
            'permasalahan'     => 'required|string',
            'solusi'           => 'required|string',
        ]);

        // Pastikan siswa yang dipilih benar-benar bimbingan guru ini
        $siswa = User::where('id', $validated['user_id'])
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        Observasi::create([
            'user_id'          => $siswa->id,
            'guru_id'          => Auth::id(),
            'hari_tanggal'     => $validated['hari_tanggal'],
            'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
            'permasalahan'     => $validated['permasalahan'],
            'solusi'           => $validated['solusi'],
            'is_approved'      => false,
        ]);

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Data observasi berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: SISWA PKL (melihat observasi)
    |--------------------------------------------------------------------------
    */

   public function indexSiswa()
{
    $observasi = Observasi::where('user_id', Auth::id())
        ->with('guru')->latest()->paginate(15)->withQueryString();
    return view('siswa.observasi.index', compact('observasi'));
}


    /*
    |--------------------------------------------------------------------------
    | ROLE: INSTRUKTUR INDUSTRI (menyetujui observasi)
    |--------------------------------------------------------------------------
    */

   public function indexInstruktur()
{
    $instruktur_id = Auth::id();
    $observasi = Observasi::whereHas('user', fn ($q) => $q->where('instruktur_id', $instruktur_id))
        ->with(['user', 'guru'])->latest()->paginate(15)->withQueryString();
    return view('instruktur.observasi.index', compact('observasi'));
}

    public function approveInstruktur($id)
    {
        $observasi = Observasi::findOrFail($id);

        // Hanya boleh menyetujui observasi siswa binaannya
        abort_unless($observasi->user->instruktur_id === Auth::id(), 403, 'Akses ditolak.');

        $observasi->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Observasi berhasil disetujui.');
    }
}