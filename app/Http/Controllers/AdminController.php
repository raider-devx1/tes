<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Jurnal;
use App\Models\Absensi;
use App\Models\Observasi;
use App\Models\Dokumen;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'siswa'         => User::where('role', 'siswa_pkl')->count(),
            'guru'          => User::where('role', 'guru_pembimbing')->count(),
            'instruktur'    => User::where('role', 'instruktur_industri')->count(),
            'industri'      => Perusahaan::count(),
            'jurnal'        => Jurnal::count(),
            'jurnalPending' => Jurnal::where('status_persetujuan', 'pending')->count(),
            'dokumen'       => Dokumen::count(),
            'observasi'     => Observasi::count(),
        ];

        $kehadiran = [
            'Hadir' => Absensi::where('status', 'Hadir')->count(),
            'Izin'  => Absensi::where('status', 'Izin')->count(),
            'Sakit' => Absensi::where('status', 'Sakit')->count(),
            'Alpha' => Absensi::where('status', 'Alpha')->count(),
        ];

        $jurnalStatus = [
            'Disetujui' => Jurnal::where('status_persetujuan', 'disetujui')->count(),
            'Menunggu'  => Jurnal::where('status_persetujuan', 'pending')->count(),
            'Revisi'    => Jurnal::where('status_persetujuan', 'revisi')->count(),
        ];

        $perJurusan = User::where('role', 'siswa_pkl')
            ->whereNotNull('jurusan')
            ->select('jurusan', DB::raw('COUNT(*) as total'))
            ->groupBy('jurusan')
            ->pluck('total', 'jurusan');

        $nilaiRata = [
            'Soft Skill'   => round((float) Nilai::avg('soft_skill'), 2),
            'Hard Skill'   => round((float) Nilai::avg('hard_skill'), 2),
            'Pengembangan' => round((float) Nilai::avg('pengembangan_hard_skill'), 2),
            'Kemandirian'  => round((float) Nilai::avg('kewirausahaan'), 2),
        ];

        // Notifikasi langsung dibangun di controller
        $notifikasi = $this->buildNotifikasi();

        return view('admin.dashboard', compact(
            'stats', 'kehadiran', 'jurnalStatus', 'perJurusan', 'nilaiRata', 'notifikasi'
        ));
    }

    /**
     * Bangun daftar notifikasi otomatis sistem.
     * Bisa dipanggil dari controller admin lain yang butuh badge notifikasi.
     */
    public function buildNotifikasi(): array
    {
        $notif = [];
        $batas = now()->subDays(3)->toDateString();

        // 1. Siswa belum isi jurnal >= 3 hari
        $siswas = User::where('role', 'siswa_pkl')->get();
        foreach ($siswas as $s) {
            $last = Jurnal::where('siswa_id', $s->id)->max('hari_tanggal');
            if (is_null($last) || $last < $batas) {
                $notif[] = ['type' => 'danger', 'icon' => '📓', 'text' => "{$s->name} belum mengisi jurnal ≥ 3 hari."];
            }
        }

        // 2. Instruktur belum menyetujui jurnal
        $pending = Jurnal::where('status_persetujuan', 'pending')->count();
        if ($pending > 0) {
            $notif[] = ['type' => 'warning', 'icon' => '⏳', 'text' => "$pending jurnal menunggu persetujuan instruktur."];
        }

        // 3. Guru belum melakukan observasi
        $gurus = User::where('role', 'guru_pembimbing')->get();
        foreach ($gurus as $g) {
            if (Observasi::where('guru_id', $g->id)->count() === 0) {
                $notif[] = ['type' => 'warning', 'icon' => '👁️', 'text' => "{$g->name} belum melakukan observasi."];
            }
        }

        // 4. Dokumen wajib belum diunggah
        $totalSiswa    = $siswas->count();
        $siswaPunyaDok = Dokumen::distinct('siswa_id')->count('siswa_id');
        $belumDok      = max(0, $totalSiswa - $siswaPunyaDok);
        if ($belumDok > 0) {
            $notif[] = ['type' => 'danger', 'icon' => '📁', 'text' => "$belumDok siswa belum mengunggah dokumen wajib."];
        }

        return array_slice($notif, 0, 15);
    }

    /* ===== Mapping siswa (dipertahankan) ===== */
    public function indexSiswa()
    {
        $siswas      = User::where('role', 'siswa_pkl')->get();
        $gurus       = User::where('role', 'guru_pembimbing')->get();
        $instrukturs = User::where('role', 'instruktur_industri')->get();
        $perusahaans = Perusahaan::all();

        return view('admin.siswa.index', compact('siswas', 'gurus', 'instrukturs', 'perusahaans'));
    }

    public function updateMapping(Request $request, $id)
    {
        $siswa = User::findOrFail($id);
        $siswa->update([
            'kelas'         => $request->kelas,
            'jurusan'       => $request->jurusan,
            'perusahaan_id' => $request->perusahaan_id,
            'instruktur_id' => $request->instruktur_id,
            'guru_id'       => $request->guru_id,
        ]);

        return redirect()->back()->with('success', 'Data Pemetaan Siswa berhasil disimpan!');
    }
}