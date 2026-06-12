<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use Illuminate\Support\Facades\Auth;

class ObservasiSiswaController extends Controller
{
    public function index()
    {
        $observasi = Observasi::where('user_id', Auth::id())->with('guru')->latest()->get();
        return view('siswa.observasi.index', compact('observasi'));
    }
}