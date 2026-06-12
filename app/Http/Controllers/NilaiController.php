<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lembar Penilaian PKL (skala 1-5, 4 komponen).
 *  - Instruktur : input / ubah nilai siswa bimbingannya
 *  - Siswa & Guru : melihat nilai (read-only)
 * Menggantikan NilaiController lama + InstrukturController::nilai* (versi ganda yang bentrok).
 */
class NilaiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isInstruktur()) {
            $siswas = User::where('instruktur_id', $user->id)->where('role', User::ROLE_SISWA)->with('nilai')->get();
            return view('nilai.kelola', compact('siswas'));
        }

        if ($user->isSiswa()) {
            $nilai = Nilai::with('instruktur')->where('siswa_id', $user->id)->first();
            return view('nilai.siswa', compact('nilai'));
        }

        // guru: rekap
        $nilais = Nilai::with('siswa')
            ->whereIn('siswa_id', User::where('guru_id', $user->id)->pluck('id'))
            ->get();
        return view('nilai.rekap', compact('nilais'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isInstruktur(), 403);

        $data = $request->validate([
            'siswa_id'                => ['required', 'exists:users,id'],
            'soft_skill'              => ['required', 'integer', 'between:1,5'],
            'hard_skill'              => ['required', 'integer', 'between:1,5'],
            'pengembangan_hard_skill' => ['required', 'integer', 'between:1,5'],
            'kewirausahaan'           => ['required', 'integer', 'between:1,5'],
            'catatan_rekomendasi'     => ['nullable', 'string'],
        ]);

        abort_unless(User::where('id', $data['siswa_id'])->where('instruktur_id', Auth::id())->exists(), 403);

        $nilai = new Nilai($data);
        $nilai->instruktur_id = Auth::id();
        $nilai->rata_rata = $nilai->hitungRataRata();

        Nilai::updateOrCreate(
            ['siswa_id' => $data['siswa_id']],
            $nilai->only(['instruktur_id', 'soft_skill', 'hard_skill', 'pengembangan_hard_skill', 'kewirausahaan', 'rata_rata', 'catatan_rekomendasi'])
        );

        Notifikasi::kirim($data['siswa_id'], 'Nilai PKL tersedia', 'Instruktur telah mengisi nilai PKL Anda.', route('nilai.index'));

        return back()->with('success', 'Nilai PKL berhasil disimpan.');
    }
}
