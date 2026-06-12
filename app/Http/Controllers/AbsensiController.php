<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Daftar Hadir Siswa PKL.
 *  - Instruktur : input absensi harian (massal) siswa bimbingannya
 *  - Siswa & Guru : melihat rekap absensi (read-only)
 */
class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tanggal = $request->input('tanggal', today()->toDateString());

        if ($user->isInstruktur()) {
            $siswas = User::where('instruktur_id', $user->id)->where('role', User::ROLE_SISWA)->get();
            $absensis = Absensi::whereIn('siswa_id', $siswas->pluck('id'))
                ->whereDate('tanggal', $tanggal)->get()->keyBy('siswa_id');
            return view('absensi.input', compact('siswas', 'absensis', 'tanggal'));
        }

        // Siswa & guru: rekap
        $query = Absensi::with('siswa')->latest('tanggal');
        if ($user->isSiswa()) {
            $query->where('siswa_id', $user->id);
        } else { // guru
            $query->whereIn('siswa_id', User::where('guru_id', $user->id)->pluck('id'));
        }
        $absensis = $query->paginate(20);

        return view('absensi.rekap', compact('absensis'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isInstruktur(), 403);

        $request->validate([
            'tanggal'            => ['required', 'date'],
            'absensi'            => ['required', 'array'],
            'absensi.*.status'   => ['required', 'in:hadir,izin,sakit,alpha'],
        ]);

        foreach ($request->absensi as $siswaId => $row) {
            // pastikan siswa benar-benar bimbingan instruktur ini
            if (! User::where('id', $siswaId)->where('instruktur_id', Auth::id())->exists()) {
                continue;
            }
            Absensi::updateOrCreate(
                ['siswa_id' => $siswaId, 'tanggal' => $request->tanggal],
                [
                    'instruktur_id' => Auth::id(),
                    'status'        => $row['status'],
                    'jam_masuk'     => $row['jam_masuk'] ?? null,
                    'jam_pulang'    => $row['jam_pulang'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Absensi tanggal ' . $request->tanggal . ' berhasil disimpan.');
    }
}
