<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Upload Dokumen PKL (surat tugas, surat penerimaan, laporan final).
 *  - Siswa : upload & hapus dokumen miliknya
 *  - Guru/Instruktur : melihat dokumen siswa bimbingannya
 */
class DokumenController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $dokumens = Dokumen::with('siswa')
            ->when($user->isSiswa(), fn ($q) => $q->where('siswa_id', $user->id))
            ->when($user->isGuru(), fn ($q) => $q->whereIn('siswa_id', User::where('guru_id', $user->id)->pluck('id')))
            ->when($user->isInstruktur(), fn ($q) => $q->whereIn('siswa_id', User::where('instruktur_id', $user->id)->pluck('id')))
            ->latest()
            ->get();

        return view('dokumen.index', compact('dokumens'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSiswa(), 403);

        $request->validate([
            'jenis' => ['required', 'in:surat_tugas,surat_penerimaan,laporan_final,lainnya'],
            'judul' => ['required', 'string', 'max:255'],
            'file'  => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        Dokumen::create([
            'siswa_id' => Auth::id(),
            'jenis'    => $request->jenis,
            'judul'    => $request->judul,
            'path'     => $request->file('file')->store('dokumen', 'public'),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function destroy(Dokumen $dokumen)
    {
        abort_unless($dokumen->siswa_id === Auth::id(), 403);
        Storage::disk('public')->delete($dokumen->path);
        $dokumen->delete();
        return back()->with('success', 'Dokumen dihapus.');
    }
}
