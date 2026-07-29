<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perusahaan;
use App\Models\PeriodePkl;
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
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ADMIN
    |--------------------------------------------------------------------------
    | Seluruh angka di halaman ini DISARING menurut Periode PKL.
    |
    | Sebelumnya semua kartu dan grafik menghitung isi tabel apa adanya.
    | Di tahun pertama hasilnya benar, tetapi mulai angkatan kedua angkanya
    | menumpuk: "Total Siswa" menjadi gabungan seluruh angkatan, dan
    | "Belum Dinilai" ikut menghitung siswa yang sudah lulus dua tahun lalu.
    | Datanya tidak salah -- penyajiannyalah yang tercampur.
    |
    | Admin dapat memilih periode lewat parameter ?periode=<id>. Bila tidak
    | dipilih, dashboard mengikuti periode yang sedang aktif.
    |
    | Bila belum ada periode aktif DAN admin belum memilih periode, scope
    | periode() mengabaikan penyaringan sehingga seluruh data ditampilkan.
    | Kondisi itu ditandai lewat $peringatanPeriode agar admin sadar bahwa
    | angka yang terlihat adalah gabungan semua angkatan, bukan salah hitung.
    */
    public function dashboard(Request $request)
    {
        $periodeList = PeriodePkl::orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->get();

        $periodeAktif = PeriodePkl::aktif();

        // Periode pilihan admin; jatuh kembali ke periode aktif bila kosong.
        $periodeDipilih = (string) $request->get('periode', '');
        $periodeId = $periodeDipilih !== ''
            ? $periodeDipilih
            : optional($periodeAktif)->id;

        $periodeTerpakai = $periodeId
            ? $periodeList->firstWhere('id', (int) $periodeId)
            : null;

        $peringatanPeriode = $periodeId
            ? null
            : 'Belum ada Periode PKL yang ditandai aktif. Angka di bawah merupakan gabungan seluruh angkatan.';

        // ====== KARTU RINGKASAN ======
        // Siswa disaring per periode. Guru, instruktur, dan industri adalah
        // data induk yang dipakai lintas angkatan, jadi tetap dihitung utuh.
        $totalSiswa      = User::siswa()->periode($periodeId)->count();
        $siswaAktif      = User::siswa()->periode($periodeId)->where('status_pkl', 'aktif')->count();
        $totalGuru       = User::where('role', 'guru_pembimbing')->count();
        $totalInstruktur = Perusahaan::whereNotNull('pembimbing_industri')->where('pembimbing_industri', '!=', '')->count();
        $totalIndustri   = Perusahaan::count();

        // ====== GRAFIK 1: KEHADIRAN SISWA ======
        // Setiap baris transaksi menyimpan periode_id-nya sendiri (trait
        // MilikPeriodePkl), sehingga grafik bisa disaring tanpa menempuh
        // tabel users terlebih dahulu.
        $kehadiran = [
            'Hadir' => Absensi::query()->periode($periodeId)->where('status', 'Hadir')->count(),
            'Izin'  => Absensi::query()->periode($periodeId)->where('status', 'Izin')->count(),
            'Sakit' => Absensi::query()->periode($periodeId)->where('status', 'Sakit')->count(),
            'Alpha' => Absensi::query()->periode($periodeId)->where('status', 'Alpha')->count(),
        ];

        // ====== GRAFIK 2: PROGRES JURNAL ======
        $jurnalStatus = [
            'Disetujui' => Jurnal::query()->periode($periodeId)->where('status_persetujuan', 'disetujui')->count(),
            'Menunggu'  => Jurnal::query()->periode($periodeId)->where('status_persetujuan', 'pending')->count(),
            'Revisi'    => Jurnal::query()->periode($periodeId)->where('status_persetujuan', 'revisi')->count(),
        ];

        // ====== GRAFIK 3: CATATAN KEGIATAN ======
        $catatanStatus = [
            'Disetujui' => CatatanKegiatan::query()->periode($periodeId)->where('is_approved', true)->count(),
            'Belum'     => CatatanKegiatan::query()->periode($periodeId)->where('is_approved', false)->count(),
        ];

        // ====== GRAFIK 4: OBSERVASI ======
        $observasiStatus = [
            'Disetujui' => Observasi::query()->periode($periodeId)->where('status', 'tervalidasi')->count(),
            'Belum'     => Observasi::query()->periode($periodeId)->where('status', 'draft')->count(),
        ];

        // ====== GRAFIK 5: SISWA PER JURUSAN ======
        $perJurusan = User::siswa()
            ->periode($periodeId)
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->select('jurusan', DB::raw('COUNT(*) as total'))
            ->groupBy('jurusan')
            ->pluck('total', 'jurusan');

        // ====== GRAFIK 6: STATUS PENILAIAN (jumlah, bukan rata-rata) ======
        $nilaiLaporan    = Nilai::query()->periode($periodeId)->whereNotNull('nilai_laporan')->count();
        $nilaiGuru       = Nilai::query()->periode($periodeId)->whereNotNull('nilai_guru')->count();
        $nilaiInstruktur = Nilai::query()->periode($periodeId)->whereNotNull('rata_rata')->count();

        // Belum dinilai = siswa tanpa baris nilai ATAU nilai_akhir masih kosong.
        // Disaring per periode supaya lulusan angkatan lama tidak ikut terhitung
        // sebagai pekerjaan yang belum selesai.
        $belumDinilai = User::siswa()
            ->periode($periodeId)
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
            'perJurusan', 'statusNilai',
            'periodeList', 'periodeTerpakai', 'periodeId', 'peringatanPeriode'
        ));
    }
}
