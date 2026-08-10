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
    |
    | SARINGAN KEDUA: STATUS PKL
    | --------------------------
    | Menyaring per periode saja ternyata belum cukup. Di dalam satu periode
    | yang sama masih bercampur tiga macam siswa: yang sedang berjalan
    | ('aktif'), yang sudah menuntaskan PKL ('selesai'), dan yang belum
    | berangkat ('belum'). Akibatnya grafik kehadiran ikut menjumlahkan
    | absensi milik siswa yang sudah lulus, dan "Belum Dinilai" ikut
    | menghitung siswa yang memang belum mulai PKL.
    |
    | Karena itu SELURUH grafik di bawah kini hanya menghitung siswa dengan
    | status_pkl = 'aktif'. Kartu ringkasan "Siswa Aktif PKL" sudah memakai
    | dasar yang sama, sehingga angka kartu dan angka grafik konsisten.
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

        // Dihitung supaya dashboard bisa menyatakan secara terbuka BERAPA siswa
        // yang sengaja tidak dimasukkan ke grafik. Tanpa angka ini, admin hanya
        // melihat jumlah mengecil tanpa tahu penyebabnya dan mengira data hilang.
        $siswaSelesai      = User::siswa()->periode($periodeId)->where('status_pkl', 'selesai')->count();
        $siswaBelum        = User::siswa()->periode($periodeId)->where('status_pkl', 'belum')->count();
        $siswaDikecualikan = $siswaSelesai + $siswaBelum;
        $totalGuru       = User::where('role', 'guru_pembimbing')->count();
        $totalInstruktur = Perusahaan::whereNotNull('pembimbing_industri')->where('pembimbing_industri', '!=', '')->count();
        $totalIndustri   = Perusahaan::count();

        // ====== KUERI DASAR TIAP GRAFIK (sudah tersaring siswa aktif) ======
        // Ditulis sebagai closure, bukan variabel builder biasa, supaya setiap
        // pemanggilan menghasilkan kueri BARU. Bila satu builder dipakai ulang
        // lalu ditambah ->where() berkali-kali, syaratnya akan menumpuk dan
        // hitungan kedua dan seterusnya menjadi salah.
        //
        // Perhatikan nama kolomnya BERBEDA antar tabel: absensis & jurnals
        // memakai siswa_id, sedangkan catatan_kegiatans, observasis, dan nilais
        // memakai user_id. Jangan disamakan.
        $absensiAktif   = fn () => Absensi::query()->periode($periodeId)
            ->whereIn('siswa_id', $this->idSiswaAktif($periodeId));

        $jurnalAktif    = fn () => Jurnal::query()->periode($periodeId)
            ->whereIn('siswa_id', $this->idSiswaAktif($periodeId));

        $catatanAktif   = fn () => CatatanKegiatan::query()->periode($periodeId)
            ->whereIn('user_id', $this->idSiswaAktif($periodeId));

        $observasiAktif = fn () => Observasi::query()->periode($periodeId)
            ->whereIn('user_id', $this->idSiswaAktif($periodeId));

        $nilaiAktif     = fn () => Nilai::query()->periode($periodeId)
            ->whereIn('user_id', $this->idSiswaAktif($periodeId));

        // ====== GRAFIK 1: KEHADIRAN SISWA ======
        // Setiap baris transaksi menyimpan periode_id-nya sendiri (trait
        // MilikPeriodePkl). Penyaringan periode tetap dipertahankan sebagai
        // lapis kedua: bila ada baris warisan yang periode_id-nya berbeda dari
        // periode siswanya, baris itu tetap tidak ikut terhitung.
        $kehadiran = [
            'Hadir' => $absensiAktif()->where('status', 'Hadir')->count(),
            'Izin'  => $absensiAktif()->where('status', 'Izin')->count(),
            'Sakit' => $absensiAktif()->where('status', 'Sakit')->count(),
            'Alpha' => $absensiAktif()->where('status', 'Alpha')->count(),
        ];

        // ====== GRAFIK 2: PROGRES JURNAL ======
        $jurnalStatus = [
            'Disetujui' => $jurnalAktif()->where('status_persetujuan', 'disetujui')->count(),
            'Menunggu'  => $jurnalAktif()->where('status_persetujuan', 'pending')->count(),
            'Revisi'    => $jurnalAktif()->where('status_persetujuan', 'revisi')->count(),
        ];

        // ====== GRAFIK 3: CATATAN KEGIATAN ======
        $catatanStatus = [
            'Disetujui' => $catatanAktif()->where('is_approved', true)->count(),
            'Belum'     => $catatanAktif()->where('is_approved', false)->count(),
        ];

        // ====== GRAFIK 4: OBSERVASI ======
        $observasiStatus = [
            'Disetujui' => $observasiAktif()->where('status', 'tervalidasi')->count(),
            'Belum'     => $observasiAktif()->where('status', 'draft')->count(),
        ];

        // ====== GRAFIK 5: SISWA PER JURUSAN ======
        // Grafik ini menghitung tabel users langsung, jadi cukup ditambah
        // syarat status_pkl tanpa perlu sub-kueri.
        $perJurusan = User::siswa()
            ->periode($periodeId)
            ->where('status_pkl', 'aktif')
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->select('jurusan', DB::raw('COUNT(*) as total'))
            ->groupBy('jurusan')
            ->pluck('total', 'jurusan');

        // ====== GRAFIK 6: STATUS PENILAIAN (jumlah, bukan rata-rata) ======
        $nilaiLaporan    = $nilaiAktif()->whereNotNull('nilai_laporan')->count();
        $nilaiGuru       = $nilaiAktif()->whereNotNull('nilai_guru')->count();
        $nilaiInstruktur = $nilaiAktif()->whereNotNull('rata_rata')->count();

        // Belum dinilai = siswa tanpa baris nilai ATAU nilai_akhir masih kosong.
        // Disaring per periode DAN hanya siswa aktif, supaya lulusan angkatan
        // lama serta siswa yang belum berangkat tidak muncul sebagai pekerjaan
        // yang belum selesai.
        $belumDinilai = User::siswa()
            ->periode($periodeId)
            ->where('status_pkl', 'aktif')
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
            'totalSiswa', 'siswaAktif', 'siswaSelesai', 'siswaBelum', 'siswaDikecualikan',
            'totalGuru', 'totalInstruktur', 'totalIndustri',
            'kehadiran', 'jurnalStatus', 'catatanStatus', 'observasiStatus',
            'perJurusan', 'statusNilai',
            'periodeList', 'periodeTerpakai', 'periodeId', 'peringatanPeriode'
        ));
    }

    /**
     * Sub-kueri berisi ID siswa yang BERSTATUS AKTIF pada periode terpilih.
     *
     * Dikembalikan sebagai builder (bukan array hasil ->pluck()), sehingga
     * MySQL mengerjakannya sebagai satu sub-kueri. Tidak ada ribuan ID yang
     * perlu diangkut ke PHP lalu dikirim balik sebagai daftar IN yang panjang.
     *
     * MENGAPA TIDAK MEMAKAI whereHas('siswa') / whereHas('user')?
     * Relasi siswa() dan user() di model transaksi sengaja memakai
     * withTrashed(), supaya nama siswa yang sudah diarsipkan tetap bisa
     * tercetak di PDF lama. Sifat itu ikut terbawa ke dalam whereHas(),
     * sehingga siswa terarsip akan kembali terhitung di grafik -- persis
     * masalah yang ingin kita hilangkan. Sub-kueri User::siswa() berdiri
     * sendiri, jadi global scope SoftDeletes tetap berlaku dan siswa yang
     * sudah diarsipkan otomatis tersingkir.
     *
     * Kolom id ditulis lengkap sebagai 'users.id' karena scope periode()
     * juga menyebut nama tabelnya secara eksplisit.
     */
    private function idSiswaAktif($periodeId)
    {
        return User::siswa()
            ->periode($periodeId)
            ->where('status_pkl', 'aktif')
            ->select('users.id');
    }
}
