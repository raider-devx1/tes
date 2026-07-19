<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perusahaan;
use App\Models\Jurnal;
use App\Models\Absensi;
use App\Models\Observasi;
use App\Models\Dokumen;
use App\Models\Nilai;
use App\Models\CatatanKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // ====== KARTU RINGKASAN ======
        $totalSiswa      = User::where('role', 'siswa_pkl')->count();
        $siswaAktif      = User::where('role', 'siswa_pkl')->where('status_pkl', 'aktif')->count();
        $totalGuru       = User::where('role', 'guru_pembimbing')->count();
        $totalInstruktur = Perusahaan::whereNotNull('pembimbing_industri')->where('pembimbing_industri', '!=', '')->count();
        $totalIndustri   = Perusahaan::count();

        // ====== GRAFIK 1: KEHADIRAN SISWA ======
        $kehadiran = [
            'Hadir' => Absensi::where('status', 'Hadir')->count(),
            'Izin'  => Absensi::where('status', 'Izin')->count(),
            'Sakit' => Absensi::where('status', 'Sakit')->count(),
            'Alpha' => Absensi::where('status', 'Alpha')->count(),
        ];

        // ====== GRAFIK 2: PROGRES JURNAL ======
        $jurnalStatus = [
            'Disetujui' => Jurnal::where('status_persetujuan', 'disetujui')->count(),
            'Menunggu'  => Jurnal::where('status_persetujuan', 'pending')->count(),
            'Revisi'    => Jurnal::where('status_persetujuan', 'revisi')->count(),
        ];

        // ====== GRAFIK 3: CATATAN KEGIATAN ======
        $catatanStatus = [
            'Disetujui' => CatatanKegiatan::where('is_approved', true)->count(),
            'Belum'     => CatatanKegiatan::where('is_approved', false)->count(),
        ];

        // ====== GRAFIK 4: OBSERVASI ======
        $observasiStatus = [
            'Disetujui' => Observasi::where('status', 'tervalidasi')->count(),
            'Belum'     => Observasi::where('status', 'draft')->count(),
        ];

        // ====== GRAFIK 5: SISWA PER JURUSAN ======
        $perJurusan = User::where('role', 'siswa_pkl')
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->select('jurusan', DB::raw('COUNT(*) as total'))
            ->groupBy('jurusan')
            ->pluck('total', 'jurusan');

        // ====== GRAFIK 6: STATUS PENILAIAN (jumlah, bukan rata-rata) ======
        $nilaiLaporan    = Nilai::whereNotNull('nilai_laporan')->count();
        $nilaiGuru       = Nilai::whereNotNull('nilai_guru')->count();
        $nilaiInstruktur = Nilai::whereNotNull('rata_rata')->count();

        // Belum dinilai = siswa tanpa baris nilai ATAU nilai_akhir masih kosong
        $belumDinilai = User::where('role', 'siswa_pkl')
            ->where(function ($u) {
                $u->whereDoesntHave('nilai')
                  ->orWhereHas('nilai', fn ($n) => $n->whereNull('nilai_akhir'));
            })
            ->count();

        $statusNilai = [
            'Laporan'    => $nilaiLaporan,
            'Nilai Guru' => $nilaiGuru,
            'Instruktur' => $nilaiInstruktur,
            'Belum'      => $belumDinilai,
        ];

        return view('admin.dashboard', compact(
            'totalSiswa', 'siswaAktif', 'totalGuru', 'totalInstruktur', 'totalIndustri',
            'kehadiran', 'jurnalStatus', 'catatanStatus', 'observasiStatus',
            'perJurusan', 'statusNilai'
        ));
    }
}
