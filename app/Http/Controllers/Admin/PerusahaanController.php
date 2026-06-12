<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    public function index()
    {
        $perusahaans = Perusahaan::withCount('siswas')->latest()->paginate(15);
        return view('admin.perusahaan.index', compact('perusahaans'));
    }

    public function store(Request $request)
    {
        Perusahaan::create($this->validasi($request));
        return back()->with('success', 'Data industri berhasil ditambahkan.');
    }

    public function update(Request $request, Perusahaan $perusahaan)
    {
        $perusahaan->update($this->validasi($request));
        return back()->with('success', 'Data industri berhasil diperbarui.');
    }

    public function destroy(Perusahaan $perusahaan)
    {
        $perusahaan->delete();
        return back()->with('success', 'Data industri dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama'                => ['required', 'string', 'max:255'],
            'bidang'              => ['nullable', 'string', 'max:255'],
            'alamat'              => ['nullable', 'string'],
            'telepon'             => ['nullable', 'string', 'max:50'],
            'pembimbing_industri' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
