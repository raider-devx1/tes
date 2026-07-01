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
public function indexGuru(Request $request)
{
    $q      = trim($request->get('q', ''));
    $status = $request->get('status'); // '1' = disetujui, '0' = menunggu

    $observasi = Observasi::where('guru_id', Auth::id())
        ->with('user')
        ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
            $u->where('name', 'like', "%{$q}%")
              ->orWhere('nisn', 'like', "%{$q}%")))
        ->when($status !== null && $status !== '', fn ($query) =>
            $query->where('is_approved', $status === '1'))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('guru.observasi.index', compact('observasi', 'q', 'status'));
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

   public function indexInstruktur(Request $request)
{
    $instruktur_id = Auth::id();

    $observasi = Observasi::whereHas('user', function ($u) use ($instruktur_id, $request) {
            $u->where('instruktur_id', $instruktur_id);

            // Filter pencarian: Nama / NISN siswa
            if ($request->filled('q')) {
                $q = $request->q;
                $u->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            }
        })
        ->with(['user', 'guru'])
        // Filter dropdown: status (disetujui | belum)
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('is_approved', $request->status === 'disetujui');
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

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