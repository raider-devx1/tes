<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\Pengaturan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

/**
 * Semua cetak PDF dalam satu controller.
 * Resolusi siswa terpusat di resolveSiswa() agar tidak ada pengulangan & aman dari akses lintas peran.
 */
class CetakController extends Controller
{
    public function jurnal(?int $siswaId = null)
    {
        $siswa = $this->resolveSiswa($siswaId);
        $jurnals = Jurnal::where('siswa_id', $siswa->id)->orderBy('hari_tanggal')->get();
        return $this->render('pdf.jurnal', compact('siswa', 'jurnals'), 'Jurnal_PKL_' . $siswa->name);
    }

    public function catatan(?int $siswaId = null)
    {
        $siswa = $this->resolveSiswa($siswaId);
        $catatans = CatatanKegiatan::where('siswa_id', $siswa->id)->disetujui()->get();
        return $this->render('pdf.catatan', compact('siswa', 'catatans'), 'Catatan_PKL_' . $siswa->name);
    }

    public function observasi(?int $siswaId = null)
    {
        $siswa = $this->resolveSiswa($siswaId);
        $observasis = Observasi::where('siswa_id', $siswa->id)->orderBy('hari_tanggal')->get();
        return $this->render('pdf.observasi', compact('siswa', 'observasis'), 'Observasi_PKL_' . $siswa->name);
    }

    public function nilai(?int $siswaId = null)
    {
        $siswa = $this->resolveSiswa($siswaId);
        $nilai = Nilai::where('siswa_id', $siswa->id)->first();
        abort_if(! $nilai, 404, 'Nilai belum diinput instruktur.');
        return $this->render('pdf.nilai', compact('siswa', 'nilai'), 'Nilai_PKL_' . $siswa->name);
    }

    /* ---------- helper ---------- */
    private function render(string $view, array $data, string $namaFile)
    {
        $data['pengaturan'] = Pengaturan::semua();
        return Pdf::loadView($view, $data)->setPaper('a4', 'portrait')->stream($namaFile . '.pdf');
    }

    /**
     * Tentukan siswa target & validasi hak akses:
     *  - siswa: hanya dirinya (abaikan param)
     *  - guru/instruktur: hanya siswa bimbingannya
     */
    private function resolveSiswa(?int $siswaId): User
    {
        $user = Auth::user();
        if ($user->isSiswa()) {
            return $user;
        }

        abort_if(! $siswaId, 400, 'Siswa tidak ditentukan.');
        $siswa = User::findOrFail($siswaId);

        $boleh = ($user->isGuru() && $siswa->guru_id === $user->id)
              || ($user->isInstruktur() && $siswa->instruktur_id === $user->id)
              || $user->isAdmin();
        abort_unless($boleh, 403);

        return $siswa;
    }
}
