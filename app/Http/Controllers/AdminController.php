<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Jurnal;
use App\Models\Absensi;
use App\Models\Observasi;
use App\Models\Dokumen;
use App\Models\Nilai;
use Illuminate\Pagination\LengthAwarePaginator;
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
   /**
/**
 * Halaman Notifikasi Sistem: kartu ringkasan realtime + tabel
 * (nama, nisn, nip, email, keterangan) dengan pencarian & pagination 15/hal.
 * NISN terisi untuk siswa (NIP kosong); NIP terisi untuk guru (NISN kosong).
 */
public function notifikasi(Request $request)
{
    $batas = now()->subDays(3)->toDateString();
    $rows  = [];

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
                'jenis'      => 'siswa_jurnal',
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
                'jenis'      => 'jurnal_pending',
            ];
        }
    }

    // 3) Guru belum melakukan observasi (tampil atas nama guru itu sendiri)
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
                'jenis'      => 'guru_observasi',
            ];
        }
    }

    // ---- Kartu ringkasan (dihitung ulang tiap load = realtime) ----
    $ringkasan = [
        'guru_observasi' => collect($rows)->where('jenis', 'guru_observasi')->count(),
        'siswa_jurnal'   => collect($rows)->where('jenis', 'siswa_jurnal')->count(),
        'jurnal_pending' => Jurnal::where('status_persetujuan', 'pending')->count(),
    ];

    // ---- Filter pencarian: nama / nisn / nip ----
    $q = trim($request->get('q', ''));
    if ($q !== '') {
        $rows = array_values(array_filter($rows, function ($r) use ($q) {
            return stripos($r['nama'], $q) !== false
                || stripos($r['nisn'], $q) !== false
                || stripos($r['nip'],  $q) !== false;
        }));
    }

    // ---- Pagination manual (15 per halaman) ----
    $perPage = 15;
    $page    = max(1, (int) $request->get('page', 1));
    $items   = array_slice($rows, ($page - 1) * $perPage, $perPage);

    $notifikasi = new LengthAwarePaginator(
        $items,
        count($rows),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('admin.notifikasi.index', compact('notifikasi', 'ringkasan', 'q'));
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