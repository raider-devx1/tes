<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurnal;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Nilai;
use Illuminate\Support\Facades\Auth;

class InstrukturController extends Controller
{
    public function jurnalIndex() {
        $siswaIds = User::where('instruktur_id', Auth::id())->pluck('id');
        $jurnals = Jurnal::whereIn('siswa_id', $siswaIds)->orderBy('hari_tanggal', 'desc')->get();
        return view('instruktur.jurnal.index', compact('jurnals'));
    }

    public function jurnalUpdate(Request $request, $id) {
        $jurnal = Jurnal::findOrFail($id);
        $jurnal->update([
            'status_persetujuan' => $request->status_persetujuan,
            'catatan_instruktur' => $request->catatan_instruktur,
            'disetujui_oleh' => Auth::id()
        ]);
        return redirect()->back()->with('success', 'Status Jurnal diperbarui!');
    }

    public function absensiIndex(Request $request) {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $siswas = User::where('role', 'siswa_pkl')->where('instruktur_id', Auth::id())->get();
        $absensis = Absensi::where('instruktur_id', Auth::id())->where('tanggal', $tanggal)->get()->keyBy('siswa_id');
        return view('instruktur.absensi.index', compact('siswas', 'tanggal', 'absensis'));
    }

    public function absensiStore(Request $request) {
        foreach ($request->absensi as $siswa_id => $data) {
            Absensi::updateOrCreate(
                ['siswa_id' => $siswa_id, 'tanggal' => $request->tanggal],
                ['instruktur_id' => Auth::id(), 'status' => $data['status'], 'jam_masuk' => $data['jam_masuk'], 'jam_pulang' => $data['jam_pulang']]
            );
        }
        return redirect()->back()->with('success', 'Absensi disimpan!');
    }

    // MODUL BARU: PENILAIAN AKHIR PKL
    public function nilaiIndex() {
        $siswas = User::where('role', 'siswa_pkl')->where('instruktur_id', Auth::id())->get();
        $nilais = Nilai::whereIn('siswa_id', $siswas->pluck('id'))->get()->keyBy('siswa_id');
        return view('instruktur.nilai.index', compact('siswas', 'nilais'));
    }

    public function nilaiStore(Request $request) {
        $request->validate(['siswa_id' => 'required|exists:users,id', 'soft_skills' => 'required|numeric|min:1|max:5', 'hard_skills' => 'required|numeric|min:1|max:5', 'pengembangan' => 'required|numeric|min:1|max:5', 'kewirausahaan' => 'required|numeric|min:1|max:5']);
        
        Nilai::updateOrCreate(
            ['siswa_id' => $request->siswa_id, 'instruktur_id' => Auth::id()],
            $request->only(['soft_skills', 'hard_skills', 'pengembangan', 'kewirausahaan', 'catatan_tambahan'])
        );
        return redirect()->back()->with('success', 'Nilai PKL berhasil disimpan!');
    }
}