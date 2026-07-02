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

        return view('admin.dashboard', compact(
            'stats', 'kehadiran', 'jurnalStatus', 'perJurusan', 'nilaiRata'
        ));
    }

    /**
     * Halaman Notifikasi Sistem (tabel).
     * Kolom: nama, nisn, nip, email + keterangan notifikasi.
     */
    public function notifikasi()
    {
        $rows  = [];
        $batas = now()->subDays(3)->toDateString();

        // 1) Siswa belum mengisi jurnal (>= 3 hari)
        $siswas = User::where('role', 'siswa_pkl')->orderBy('name')->get();
        foreach ($siswas as $s) {
            $last = Jurnal::where('siswa_id', $s->id)->max('hari_tanggal');
            if (is_null($last) || $last < $batas) {
                $rows[] = [
                    'nama'       => $s->name,
                    'nisn'       => $s->nisn ?? '-',
                    'nip'        => '-',
                    'email'      => $s->email,
                    'keterangan' => 'Siswa belum mengisi jurnal (≥ 3 hari).',
                    'kategori'   => 'danger',
                ];
            }
        }

        // 2) Jurnal siswa belum disetujui instruktur (status pending)
        $pendingPerSiswa = Jurnal::where('status_persetujuan', 'pending')
            ->select('siswa_id', DB::raw('COUNT(*) as total'))
            ->groupBy('siswa_id')
            ->pluck('total', 'siswa_id');

        if ($pendingPerSiswa->isNotEmpty()) {
            $siswaPending = User::whereIn('id', $pendingPerSiswa->keys())->orderBy('name')->get();
            foreach ($siswaPending as $s) {
                $rows[] = [
                    'nama'       => $s->name,
                    'nisn'       => $s->nisn ?? '-',
                    'nip'        => '-',
                    'email'      => $s->email,
                    'keterangan' => 'Jurnal belum disetujui instruktur (' . $pendingPerSiswa[$s->id] . ' jurnal).',
                    'kategori'   => 'warning',
                ];
            }
        }

        // 3) Guru belum melakukan observasi
        $gurus = User::where('role', 'guru_pembimbing')->orderBy('name')->get();
        foreach ($gurus as $g) {
            if (Observasi::where('guru_id', $g->id)->count() === 0) {
                $rows[] = [
                    'nama'       => $g->name,
                    'nisn'       => '-',
                    'nip'        => $g->nip ?? '-',
                    'email'      => $g->email,
                    'keterangan' => 'Guru belum melakukan observasi.',
                    'kategori'   => 'warning',
                ];
            }
        }

        return view('admin.notifikasi.index', ['notifikasi' => $rows]);
    }

    /**
     * (Opsional) Ringkasan notifikasi untuk badge — masih dipertahankan
     * bila ingin dipakai di tempat lain.
     */
    public function buildNotifikasi(): array
    {
        $notif = [];
        $batas = now()->subDays(3)->toDateString();

        $siswas = User::where('role', 'siswa_pkl')->get();
        foreach ($siswas as $s) {
            $last = Jurnal::where('siswa_id', $s->id)->max('hari_tanggal');
            if (is_null($last) || $last < $batas) {
                $notif[] = ['type' => 'danger', 'icon' => '📓', 'text' => "{$s->name} belum mengisi jurnal ≥ 3 hari."];
            }
        }

        $pending = Jurnal::where('status_persetujuan', 'pending')->count();
        if ($pending > 0) {
            $notif[] = ['type' => 'warning', 'icon' => '⏳', 'text' => "$pending jurnal menunggu persetujuan instruktur."];
        }

        $gurus = User::where('role', 'guru_pembimbing')->get();
        foreach ($gurus as $g) {
            if (Observasi::where('guru_id', $g->id)->count() === 0) {
                $notif[] = ['type' => 'warning', 'icon' => '👁️', 'text' => "{$g->name} belum melakukan observasi."];
            }
        }

        return array_slice($notif, 0, 15);
    }
}