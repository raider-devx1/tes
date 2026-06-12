<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Informasi;
use Illuminate\Http\Request;

class InformasiController extends Controller
{
    public function index()
    {
        $informasis = Informasi::orderBy('kategori')->orderBy('urutan')->get();
        return view('admin.informasi.index', compact('informasis'));
    }

    public function create()
    {
        return view('admin.informasi.form', ['informasi' => new Informasi()]);
    }

    public function store(Request $request)
    {
        Informasi::create($this->validasi($request));
        return redirect()->route('admin.informasi.index')->with('success', 'Informasi/panduan ditambahkan.');
    }

    public function edit(Informasi $informasi)
    {
        return view('admin.informasi.form', compact('informasi'));
    }

    public function update(Request $request, Informasi $informasi)
    {
        $informasi->update($this->validasi($request));
        return redirect()->route('admin.informasi.index')->with('success', 'Informasi/panduan diperbarui.');
    }

    public function destroy(Informasi $informasi)
    {
        $informasi->delete();
        return back()->with('success', 'Informasi/panduan dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul'    => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:50'],
            'konten'   => ['required', 'string'],
            'urutan'   => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
