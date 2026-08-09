<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'no_hp',
    'foto',
    'nisn',
    'jenis_kelamin',
    'status_pkl',
    'nip',
    'is_wakasek',
    'is_admin',
    'jabatan',
    'kelas',
    'jurusan',
    // --- Jam kerja industri (per-siswa) ---
    'jam_masuk_industri',
    'jam_pulang_industri',
    'jam_masuk_usulan',
    'jam_pulang_usulan',
    'status_jam_usulan',
    'catatan_jam_usulan',
    // --- Pembukaan absensi manual (per-siswa) ---
    'absensi_dibuka',
    'absensi_dibuka_masuk',
    'absensi_dibuka_pulang',
    // Tenggat pembukaan manual; null = tanpa batas waktu (harus ditutup manual).
    'absensi_dibuka_sampai',
    // --- Jadwal hari kerja absensi (per-siswa; null = ikut jadwal global) ---
    'hari_kerja',
    // --- Tanda tangan tersimpan milik guru pembimbing ---
    // Diunggah SEKALI dari halaman Monitoring Absensi, lalu tinggal dipilih
    // saat memberi nilai supaya guru tidak mengunggah berkas yang sama terus.
    'ttd_tersimpan',
    'ttd_tersimpan_at',
    // --- Relasi ---
    'perusahaan_id',
    'guru_id',
    'periode_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    /*
    |--------------------------------------------------------------------------
    | SoftDeletes
    |--------------------------------------------------------------------------
    | Semua tabel transaksi PKL memakai onDelete('cascade') ke tabel ini.
    | Tanpa soft delete, satu klik hapus siswa akan memusnahkan seluruh
    | jurnal, absensi, nilai, dan dokumennya secara permanen.
    |
    | Dengan SoftDeletes, penghapusan hanya mengisi kolom deleted_at.
    | Tidak ada perintah DELETE sungguhan, sehingga cascade TIDAK berjalan
    | dan seluruh riwayat PKL tetap utuh serta bisa dipulihkan:
    |
    |     User::onlyTrashed()->findOrFail($id)->restore();
    |
    | Penghapusan permanen tetap mungkin lewat forceDelete(), tapi sengaja
    | tidak disediakan tombolnya di antarmuka admin.
    */
    use HasFactory, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
            'is_wakasek' => 'boolean',
            'is_admin' => 'boolean',
            'absensi_dibuka' => 'boolean',
            'absensi_dibuka_masuk' => 'boolean',
            'absensi_dibuka_pulang' => 'boolean',
            'absensi_dibuka_sampai' => 'datetime',
            'ttd_tersimpan_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PEMBUKAAN ABSENSI MANUAL + BATAS WAKTUNYA
    |--------------------------------------------------------------------------
    | Guru/admin boleh membuka absensi di luar jadwal jam. Supaya tidak
    | terbuka terus karena lupa ditutup, pembukaan dapat diberi BATAS WAKTU
    | pada kolom absensi_dibuka_sampai:
    |
    |   null                -> tanpa batas waktu (perilaku lama, tutup manual)
    |   waktu di masa depan -> masih terbuka, sisa waktu berjalan
    |   waktu sudah lewat   -> dianggap TERTUTUP walau flag masih true
    |
    | Pengecekan dilakukan saat dibaca (lazy), jadi tidak butuh cron/scheduler.
    */

    /** Lama pembukaan absensi manual bawaan, dalam menit. */
    public const BUKA_MENIT_DEFAULT = 30;

    /** Lama pembukaan absensi manual maksimum, dalam menit (24 jam). */
    public const BUKA_MENIT_MAKS = 1440;

    /** Ada flag pembukaan manual yang menyala? (belum memperhitungkan batas waktu) */
    public function adaFlagBukaManual(): bool
    {
        return (bool) $this->absensi_dibuka
            || (bool) $this->absensi_dibuka_masuk
            || (bool) $this->absensi_dibuka_pulang;
    }

    /** Batas waktu pembukaan sudah lewat? (null = tanpa batas, tidak pernah lewat) */
    public function bukaManualKedaluwarsa(): bool
    {
        if (! $this->absensi_dibuka_sampai) {
            return false;
        }

        return $this->absensi_dibuka_sampai->isPast();
    }

    /** Pembukaan manual masih berlaku saat ini? */
    public function bukaManualAktif(): bool
    {
        return $this->adaFlagBukaManual() && ! $this->bukaManualKedaluwarsa();
    }

    /** Sisa waktu pembukaan dalam detik. null = tanpa batas / tidak sedang dibuka. */
    public function sisaDetikBuka(): ?int
    {
        if (! $this->bukaManualAktif() || ! $this->absensi_dibuka_sampai) {
            return null;
        }

        return max(0, (int) round(now()->diffInSeconds($this->absensi_dibuka_sampai, false)));
    }

    /** Label ramah untuk sisa waktu, mis. "24 menit lagi". */
    public function labelSisaBuka(): string
    {
        if (! $this->bukaManualAktif()) {
            return 'tertutup';
        }

        $detik = $this->sisaDetikBuka();

        if ($detik === null) {
            return 'tanpa batas waktu';
        }

        if ($detik < 60) {
            return 'kurang dari 1 menit lagi';
        }

        $menit = intdiv($detik, 60);

        if ($menit < 60) {
            return $menit . ' menit lagi';
        }

        $jam  = intdiv($menit, 60);
        $sisa = $menit % 60;

        return $sisa > 0 ? "{$jam} jam {$sisa} menit lagi" : "{$jam} jam lagi";
    }

    /** Jam berakhirnya pembukaan, mis. "08:30". Kosong bila tanpa batas. */
    public function jamBerakhirBuka(): string
    {
        return $this->bukaManualAktif() && $this->absensi_dibuka_sampai
            ? $this->absensi_dibuka_sampai->format('H:i')
            : '';
    }

    /**
     * Tutup pembukaan manual yang batas waktunya sudah lewat untuk sekumpulan
     * siswa sekaligus. Dipanggil saat halaman monitoring dimuat sehingga
     * absensi menutup sendiri tanpa perlu scheduler maupun tombol "Tutup".
     *
     * @param  array<int>|null  $siswaIds  null = seluruh pengguna
     * @return int jumlah baris yang baru saja ditutup otomatis
     */
    public static function tutupBukaKedaluwarsa(?array $siswaIds = null): int
    {
        if ($siswaIds !== null && $siswaIds === []) {
            return 0;
        }

        $query = static::query()
            ->whereNotNull('absensi_dibuka_sampai')
            ->where('absensi_dibuka_sampai', '<=', now());

        if ($siswaIds !== null) {
            $query->whereIn('id', $siswaIds);
        }

        return $query->update([
            'absensi_dibuka'        => false,
            'absensi_dibuka_masuk'  => false,
            'absensi_dibuka_pulang' => false,
            'absensi_dibuka_sampai' => null,
        ]);
    }

    /**
     * Apakah guru ini ditetapkan sebagai Wakasek oleh admin.
     * Wakasek berhak memvalidasi lembar observasi guru lain
     * dan boleh memvalidasi lembar observasinya sendiri.
     */
    public function isWakasek(): bool
    {
        return (bool) $this->is_wakasek;
    }

    /** Scope: hanya guru pembimbing yang berstatus Wakasek. */
    public function scopeWakasek($query)
    {
        return $query->where('role', 'guru_pembimbing')->where('is_wakasek', true);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE PENYARINGAN (sumber kebenaran tunggal)
    |--------------------------------------------------------------------------
    | Sebelumnya penyaringan role siswa dan periode ditulis ulang manual di
    | banyak controller. Setiap kali ada fitur baru, semua titik itu harus
    | diingat satu per satu -- dan satu saja terlewat, data antar-angkatan
    | bisa tercampur tanpa disadari.
    |
    | Scope di bawah memindahkan aturan itu ke SATU tempat:
    |
    |     User::siswa()->periode($idPeriode)->get();
    |     User::siswa()->periodeAktif()->count();
    |
    | Nama kolom sengaja diawali nama tabel supaya tetap aman ketika query
    | digabung (join) dengan tabel lain yang juga punya kolom periode_id.
    */

    /** Scope: hanya akun yang berperan sebagai siswa PKL. */
    public function scopeSiswa($query)
    {
        return $query->where($this->getTable() . '.role', 'siswa_pkl');
    }

    /**
     * Pembatas standar untuk SELURUH daftar kerja harian.
     *
     * Menggabungkan dua hal yang selama ini tercecer:
     *   1. withoutTrashed() -- siswa yang sudah diarsipkan tidak boleh muncul
     *      di daftar kerja guru. Ini WAJIB ditulis eksplisit, karena relasi
     *      di model transaksi sengaja memakai withTrashed() agar nama siswa
     *      lama tetap tercetak di PDF. Tanpa baris ini, withTrashed() pada
     *      relasi ikut terbawa ke dalam whereHas() dan siswa terarsip muncul
     *      kembali di daftar guru.
     *   2. periode() -- hanya angkatan yang sedang berjalan.
     *
     * Bila belum ada periode aktif, penyaringan periode diabaikan (lihat
     * App\Support\KonteksPeriode) sehingga halaman tidak pernah kosong total.
     */
    public function scopeBerjalan($query)
    {
        return $query->withoutTrashed()
                     ->periode(\App\Support\KonteksPeriode::id());
    }

    /** Gabungan yang paling sering dipakai: siswa PKL angkatan berjalan. */
    public function scopeSiswaBerjalan($query)
    {
        return $query->siswa()->berjalan();
    }

    /**
     * Scope: saring menurut Periode PKL.
     *
     * Nilai kosong (null, string kosong, atau '0') sengaja DIABAIKAN, bukan
     * dianggap "cari yang periode_id-nya kosong". Dengan begitu scope ini
     * aman dipakai langsung pada nilai filter dropdown tanpa perlu dibungkus
     * when() berulang kali di controller.
     */
    public function scopePeriode($query, $periodeId = null)
    {
        if (blank($periodeId)) {
            return $query;
        }

        return $query->where($this->getTable() . '.periode_id', $periodeId);
    }

    /**
     * Scope: hanya baris milik Periode PKL yang sedang aktif.
     *
     * Bila admin belum menandai satu pun periode sebagai aktif, scope ini
     * sengaja mengembalikan HASIL KOSONG, bukan seluruh data. Menampilkan
     * semua angkatan sekaligus jauh lebih berbahaya daripada menampilkan
     * tabel kosong yang jelas terlihat salah oleh admin.
     */
    public function scopePeriodeAktif($query)
    {
        $aktif = PeriodePkl::aktif();

        if (! $aktif) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable() . '.periode_id', $aktif->id);
    }

    /*
    |--------------------------------------------------------------------------
    | JAM KERJA INDUSTRI (per-siswa) — dipakai untuk jendela absensi
    |--------------------------------------------------------------------------
    | - Jika siswa memiliki jam khusus yang SUDAH disetujui guru
    |   (jam_masuk_industri / jam_pulang_industri), gunakan itu.
    | - Jika tidak, gunakan jam GLOBAL yang diatur admin (tabel pengaturans).
    */
    public function jamMasukEfektif(): string
    {
        if (! empty($this->jam_masuk_industri)) {
            return substr((string) $this->jam_masuk_industri, 0, 5);
        }

        return (string) Pengaturan::ambil('absensi_jam_masuk', '08:00');
    }

    public function jamPulangEfektif(): string
    {
        if (! empty($this->jam_pulang_industri)) {
            return substr((string) $this->jam_pulang_industri, 0, 5);
        }

        return (string) Pengaturan::ambil('absensi_jam_pulang', '16:00');
    }

    /** True bila siswa memakai jam khusus (bukan jam global admin). */
    public function pakaiJamKhusus(): bool
    {
        return $this->status_jam_usulan === 'disetujui'
            && (! empty($this->jam_masuk_industri) || ! empty($this->jam_pulang_industri));
    }

    /*
    |--------------------------------------------------------------------------
    | JADWAL HARI KERJA ABSENSI
    |--------------------------------------------------------------------------
    | Default seluruh sekolah: SENIN - JUMAT. Admin dapat mengubahnya menjadi
    | SENIN - SABTU atau SENIN - MINGGU lewat tombol "Jadwal Absensi"
    | (kunci: absensi_hari_kerja).
    |
    | Kolom users.hari_kerja adalah PENGECUALIAN per siswa: siswa yang tetap
    | masuk sampai Sabtu/Minggu dapat diberi jadwalnya sendiri walau jadwal
    | sekolah hanya sampai Jumat. Bernilai null berarti ikut jadwal global.
    |
    | Hari yang BUKAN hari kerja tidak boleh diisi absensi dan TIDAK pernah
    | ditandai Alpha otomatis -- barisnya sengaja dibiarkan kosong.
    */
    public const HARI_KERJA_SENIN_JUMAT  = 'senin_jumat';
    public const HARI_KERJA_SENIN_SABTU  = 'senin_sabtu';
    public const HARI_KERJA_SENIN_MINGGU = 'senin_minggu';

    /** Semua pilihan jadwal yang sah (dipakai validasi & dropdown admin). */
    public static function daftarHariKerja(): array
    {
        return [
            self::HARI_KERJA_SENIN_JUMAT,
            self::HARI_KERJA_SENIN_SABTU,
            self::HARI_KERJA_SENIN_MINGGU,
        ];
    }

    /** Label layar untuk sebuah nilai jadwal. */
    public static function labelJadwal(?string $nilai): string
    {
        return match ((string) $nilai) {
            self::HARI_KERJA_SENIN_MINGGU => 'Senin - Minggu',
            self::HARI_KERJA_SENIN_SABTU  => 'Senin - Sabtu',
            default                       => 'Senin - Jumat',
        };
    }

    /** Keterangan singkat: hari mana saja yang dilewati oleh sebuah jadwal. */
    public static function keteranganJadwal(?string $nilai): string
    {
        return match ((string) $nilai) {
            self::HARI_KERJA_SENIN_MINGGU => 'tanpa hari libur, absensi diisi setiap hari',
            self::HARI_KERJA_SENIN_SABTU  => 'hanya Minggu yang dilewati',
            default                       => 'Sabtu & Minggu dilewati',
        };
    }

    /** Jadwal hari kerja GLOBAL yang berlaku di seluruh sekolah. */
    public static function hariKerjaGlobal(): string
    {
        $nilai = (string) Pengaturan::ambil('absensi_hari_kerja', self::HARI_KERJA_SENIN_JUMAT);

        return in_array($nilai, self::daftarHariKerja(), true)
            ? $nilai
            : self::HARI_KERJA_SENIN_JUMAT;
    }

    /** Jadwal hari kerja yang benar-benar berlaku untuk siswa ini. */
    public function hariKerjaEfektif(): string
    {
        $khusus = (string) ($this->hari_kerja ?? '');

        if (in_array($khusus, self::daftarHariKerja(), true)) {
            return $khusus;
        }

        return self::hariKerjaGlobal();
    }

    /** True bila siswa ini memakai jadwal khusus (bukan jadwal global admin). */
    public function pakaiHariKerjaKhusus(): bool
    {
        return in_array((string) ($this->hari_kerja ?? ''), self::daftarHariKerja(), true);
    }

    /** True bila jadwal siswa ini menjadikan Sabtu sebagai hari kerja. */
    public function masukSampaiSabtu(): bool
    {
        return in_array($this->hariKerjaEfektif(), [
            self::HARI_KERJA_SENIN_SABTU,
            self::HARI_KERJA_SENIN_MINGGU,
        ], true);
    }

    /** True bila jadwal siswa ini menjadikan Minggu sebagai hari kerja. */
    public function masukSampaiMinggu(): bool
    {
        return $this->hariKerjaEfektif() === self::HARI_KERJA_SENIN_MINGGU;
    }

    /**
     * Apakah tanggal tertentu termasuk hari kerja bagi siswa ini?
     *
     *  - 'senin_jumat'  : Sabtu & Minggu BUKAN hari kerja
     *  - 'senin_sabtu'  : hanya Minggu yang bukan hari kerja
     *  - 'senin_minggu' : setiap hari adalah hari kerja
     */
    public function adalahHariKerja($tanggal): bool
    {
        $c = $tanggal instanceof \Carbon\Carbon
            ? $tanggal
            : \Carbon\Carbon::parse($tanggal);

        $hari = (int) $c->dayOfWeek; // 0 = Minggu ... 6 = Sabtu

        if ($hari === 0) {
            return $this->masukSampaiMinggu();
        }

        if ($hari === 6) {
            return $this->masukSampaiSabtu();
        }

        return true;                // Senin - Jumat
    }

    /** Label jadwal hari kerja untuk ditampilkan di layar. */
    public function labelHariKerja(): string
    {
        return self::labelJadwal($this->hariKerjaEfektif());
    }

    /*
    |--------------------------------------------------------------------------
    | TANDA TANGAN TERSIMPAN (GURU PEMBIMBING)
    |--------------------------------------------------------------------------
    | Guru mengunggah tanda tangannya sekali saja, lalu memakainya berulang
    | kali saat memberi nilai. Saat dipakai, berkasnya DISALIN ke penilaian
    | yang bersangkutan sehingga arsip cetakan lama tidak ikut berubah bila
    | guru mengganti tanda tangan tersimpannya di kemudian hari.
    */

    /** Guru sudah pernah menyimpan tanda tangannya? */
    public function punyaTtdTersimpan(): bool
    {
        return is_string($this->ttd_tersimpan) && trim($this->ttd_tersimpan) !== '';
    }

    /** URL publik tanda tangan tersimpan, untuk pratinjau di layar. */
    public function ttdTersimpanUrl(): ?string
    {
        return $this->punyaTtdTersimpan()
            ? asset('storage/' . $this->ttd_tersimpan)
            : null;
    }

    /** Keterangan singkat kapan tanda tangan terakhir diperbarui. */
    public function labelTtdTersimpan(): string
    {
        if (! $this->punyaTtdTersimpan()) {
            return 'Belum ada tanda tangan tersimpan.';
        }

        return $this->ttd_tersimpan_at
            ? 'Disimpan ' . $this->ttd_tersimpan_at->translatedFormat('d M Y, H:i') . ' WITA'
            : 'Tanda tangan sudah tersimpan.';
    }

    /**
     * Relasi Pemetaan: Siswa magang di Perusahaan apa
     */
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    /**
     * Instruktur industri kini berupa DATA (nama pembimbing pada Perusahaan),
     * bukan akun user. Accessor ini menjaga tampilan lama yang masih memanggil
     * $siswa->instruktur->name atau $siswa->instruktur->nip tetap berfungsi.
     */
    public function getInstrukturAttribute(): object
    {
        $namaPembimbing = $this->perusahaan?->pembimbing_industri;

        return (object) [
            'name' => $namaPembimbing ?: 'Belum Diatur',
            'nip'  => '-',
        ];
    }

    /**
     * Relasi Pemetaan: Siswa dipantau oleh Guru siapa
     */
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relasi ke model Nilai (Siswa memiliki 1 data nilai)
     */
    public function nilai()
    {
        return $this->hasOne(Nilai::class, 'user_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function dokumen()
    {
        return $this->hasOne(\App\Models\Dokumen::class, 'siswa_id');
    }
}
