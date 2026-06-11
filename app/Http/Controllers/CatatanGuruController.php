<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use Illuminate\Support\Facades\Auth;

class CatatanGuruController extends Controller
{
    public function index()
    {
        $guru_id = Auth::id();
        // Mengambil catatan siswa yang dibimbing oleh guru ini
        $catatan = CatatanKegiatan::whereHas('user', function($q) use ($guru_id) {
            $q->where('guru_id', $guru_id);
        })->with('user')->latest()->get();
        
        return view('guru.catatan.index', compact('catatan'));
    }
}