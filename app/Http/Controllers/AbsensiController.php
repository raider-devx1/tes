<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    // ====== ROLE: INSTRUKTUR INDUSTRI (mengisi absensi) ======
    public function indexInstruktur(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $siswas = User::where('role', 'siswa_pkl')->where('instruktur_id', Auth::id())->get();
        $absensis = Absensi::where('instruktur_id', Auth::id())->where('tanggal', $tanggal)->get()->keyBy('siswa_id');
        return view('instruktur.absensi.index', compact('siswas', 'tanggal', 'absensis'));
    }

    public function storeInstruktur(Request $request)
    {
        foreach ($request->absensi as $siswa_id => $data) {
            Absensi::updateOrCreate(
                ['siswa_id' => $siswa_id, 'tanggal' => $request->tanggal],
                ['instruktur_id' => Auth::id(), 'status' => $data['status'], 'jam_masuk' => $data['jam_masuk'], 'jam_pulang' => $data['jam_pulang']]
            );
        }
        return redirect()->back()->with('success', 'Absensi disimpan!');
    }
}