<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use Illuminate\Http\Request;

class InformasiController extends Controller
{
    // Daftar kategori + label tampilan (dipakai form & view)
    public static array $kategoriLabels = [
        'umum'               => 'Informasi Umum',
        'panduan_laporan'    => 'Panduan Penyusunan Laporan',
        'panduan_presentasi' => 'Panduan Presentasi',
    ];

    /* ===========================================================
     |  SEMUA ROLE: melihat informasi & panduan PKL
     * =========================================================== */
    public function index()
    {
        $informasiGroup = Informasi::orderBy('urutan')->get()->groupBy('kategori');

        return view('informasi.index', [
            'informasiGroup' => $informasiGroup,
            'kategoriLabels' => self::$kategoriLabels,
        ]);
    }

    /* ===========================================================
     |  ADMIN: kelola (CRUD) informasi
     * =========================================================== */
    public function adminIndex()
    {
        $informasi = Informasi::orderBy('kategori')->orderBy('urutan')->get();

        return view('admin.informasi.index', [
            'informasi'      => $informasi,
            'kategoriLabels' => self::$kategoriLabels,
        ]);
    }

    public function create()
    {
        return view('admin.informasi.create', [
            'kategoriLabels' => self::$kategoriLabels,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'    => 'required|string|max:255',
            'kategori' => 'required|in:' . implode(',', array_keys(self::$kategoriLabels)),
            'konten'   => 'required|string',
            'urutan'   => 'nullable|integer|min:0',
        ]);
        $data['urutan'] = $data['urutan'] ?? 0;

        Informasi::create($data);

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil ditambahkan.');
    }

    public function edit(Informasi $informasi)
    {
        return view('admin.informasi.edit', [
            'informasi'      => $informasi,
            'kategoriLabels' => self::$kategoriLabels,
        ]);
    }

    public function update(Request $request, Informasi $informasi)
    {
        $data = $request->validate([
            'judul'    => 'required|string|max:255',
            'kategori' => 'required|in:' . implode(',', array_keys(self::$kategoriLabels)),
            'konten'   => 'required|string',
            'urutan'   => 'nullable|integer|min:0',
        ]);
        $data['urutan'] = $data['urutan'] ?? 0;

        $informasi->update($data);

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil diperbarui.');
    }

    public function destroy(Informasi $informasi)
    {
        $informasi->delete();

        return redirect()->route('admin.informasi.index')
            ->with('success', 'Informasi berhasil dihapus.');
    }
}