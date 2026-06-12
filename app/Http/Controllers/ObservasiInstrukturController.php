<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservasiInstrukturController extends Controller
{
    public function index()
    {
        $instruktur_id = Auth::id();
        // Mengambil observasi untuk siswa yang ditugaskan di tempat instruktur ini
        $observasi = Observasi::whereHas('user', function($q) use ($instruktur_id) {
            $q->where('instruktur_id', $instruktur_id);
        })->with(['user', 'guru'])->latest()->get();

        return view('instruktur.observasi.index', compact('observasi'));
    }

    public function approve($id)
    {
        $observasi = Observasi::findOrFail($id);
        $observasi->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Observasi berhasil disetujui.');
    }
}