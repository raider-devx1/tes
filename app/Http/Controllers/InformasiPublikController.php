<?php

namespace App\Http\Controllers;

use App\Models\Informasi;

/** Menu "Panduan PKL" yang bisa dibaca semua peran. */
class InformasiPublikController extends Controller
{
    public function index()
    {
        $informasis = Informasi::orderBy('kategori')->orderBy('urutan')->get()->groupBy('kategori');
        return view('informasi.index', compact('informasis'));
    }

    public function show(Informasi $informasi)
    {
        return view('informasi.show', compact('informasi'));
    }
}
