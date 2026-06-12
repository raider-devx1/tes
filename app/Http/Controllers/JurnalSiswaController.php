<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurnal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JurnalSiswaController extends Controller
{
    // Menampilkan daftar jurnal milik siswa yang sedang login
    public function index()
    {
        $jurnals = Jurnal::where('siswa_id', Auth::id())
                         ->orderBy('hari_tanggal', 'desc')
                         ->get();
        return view('siswa.jurnal.index', compact('jurnals'));
    }

    // Menampilkan form tambah jurnal
    public function create()
    {
        return view('siswa.jurnal.create');
    }

    // Menyimpan data jurnal beserta foto dokumentasi
    public function store(Request $request)
    {
        $request->validate([
            'hari_tanggal' => 'required|date',
            'unit_kerja' => 'required|string|max:255',
            'deskripsi_pekerjaan' => 'required|string',
            'dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        $path = null;
        // Proses upload foto jika siswa melampirkan foto
        if ($request->hasFile('dokumentasi')) {
            $path = $request->file('dokumentasi')->store('dokumentasi_jurnal', 'public');
        }

        Jurnal::create([
            'siswa_id' => Auth::id(),
            'hari_tanggal' => $request->hari_tanggal,
            'unit_kerja' => $request->unit_kerja,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
            'dokumentasi' => $path,
            'status_persetujuan' => 'pending', // Status default menunggu instruktur
        ]);

        return redirect()->route('siswa.jurnal.index')->with('success', 'Jurnal harian berhasil ditambahkan!');
    }

    // Menghapus jurnal (hanya bisa jika belum disetujui)
    public function destroy($id)
    {
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();
        
        if ($jurnal->status_persetujuan !== 'pending') {
            return redirect()->back()->with('error', 'Jurnal yang sudah disetujui/direvisi tidak bisa dihapus.');
        }

        if ($jurnal->dokumentasi) {
            Storage::disk('public')->delete($jurnal->dokumentasi);
        }
        
        $jurnal->delete();
        return redirect()->route('siswa.jurnal.index')->with('success', 'Jurnal harian berhasil dihapus!');
    }
}