<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InformasiController extends Controller
{
    /* ===========================================================
     |  SEMUA ROLE: melihat informasi & panduan PKL
     * =========================================================== */
    public function index()
    {
        $informasi = Informasi::orderBy('urutan')->orderByDesc('created_at')->get();

        return view('informasi.index', [
            'informasi' => $informasi,
        ]);
    }

    /* ===========================================================
     |  ADMIN: kelola (CRUD) informasi
     * =========================================================== */
    public function adminIndex()
    {
        $informasi = Informasi::orderBy('urutan')->orderByDesc('created_at')->get();

        return view('admin.informasi.index', [
            'informasi' => $informasi,
        ]);
    }

    public function create()
    {
        return view('admin.informasi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'  => 'required|string|max:255',
            'konten' => 'required|string',
            'urutan' => 'nullable|integer|min:0',
            'file'   => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png',
        ]);

        $informasi = new Informasi();
        $informasi->judul  = $validated['judul'];
        $informasi->konten = $validated['konten'];
        $informasi->urutan = $validated['urutan'] ?? 0;

        if ($request->hasFile('file')) {
            $informasi->file = $request->file('file')->store('informasi', 'public');
        }

        $informasi->save();

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil ditambahkan.');
    }

    public function edit(Informasi $informasi)
    {
        return view('admin.informasi.edit', [
            'informasi' => $informasi,
        ]);
    }

    public function update(Request $request, Informasi $informasi)
    {
        $validated = $request->validate([
            'judul'  => 'required|string|max:255',
            'konten' => 'required|string',
            'urutan' => 'nullable|integer|min:0',
            'file'   => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png',
        ]);

        $informasi->judul  = $validated['judul'];
        $informasi->konten = $validated['konten'];
        $informasi->urutan = $validated['urutan'] ?? 0;

        if ($request->hasFile('file')) {
            if ($informasi->file) {
                Storage::disk('public')->delete($informasi->file);
            }
            $informasi->file = $request->file('file')->store('informasi', 'public');
        }

        $informasi->save();

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil diperbarui.');
    }

    public function destroy(Informasi $informasi)
    {
        if ($informasi->file) {
            Storage::disk('public')->delete($informasi->file);
        }

        $informasi->delete();

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil dihapus.');
    }
}