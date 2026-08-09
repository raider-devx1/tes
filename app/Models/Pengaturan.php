<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Pengaturan extends Model
{
    protected $table = 'pengaturans';

    protected $fillable = ['kunci', 'nilai'];

    /** Ambil satu nilai pengaturan berdasarkan kunci. */
    public static function ambil(string $kunci, $default = null)
    {
        return static::where('kunci', $kunci)->value('nilai') ?? $default;
    }

    /** Simpan / perbarui satu pengaturan (buat bila belum ada). */
    public static function simpan(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    /** Semua pengaturan sebagai array [kunci => nilai]. */
    public static function semua(): array
    {
        return static::pluck('nilai', 'kunci')->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | BUKA-PAKSA ABSENSI GLOBAL + BATAS WAKTUNYA
    |--------------------------------------------------------------------------
    | Admin boleh membuka absensi untuk SEMUA siswa di luar jadwal jam. Supaya
    | tidak terbuka terus karena lupa ditutup, pembukaan dapat diberi BATAS
    | WAKTU yang disimpan pada kunci "<flag>_sampai":
    |
    |   kosong              -> tanpa batas waktu (perilaku lama, tutup manual)
    |   waktu di masa depan -> masih terbuka, sisa waktu berjalan
    |   waktu sudah lewat   -> dianggap TERTUTUP walau flag masih '1'
    |
    | Pengecekan dilakukan saat dibaca (lazy), persis seperti pembukaan
    | per-siswa di App\Models\User, jadi tidak butuh cron/scheduler.
    */

    /** Lama buka-paksa absensi bawaan, dalam menit. */
    public const PAKSA_MENIT_DEFAULT = 30;

    /** Lama buka-paksa absensi maksimum, dalam menit (24 jam). */
    public const PAKSA_MENIT_MAKS = 1440;

    /** Nama kunci flag buka-paksa untuk sebuah fase. */
    private static function kunciPaksa(string $fase): string
    {
        return $fase === 'pulang'
            ? 'absensi_paksa_buka_pulang'
            : 'absensi_paksa_buka_masuk';
    }

    /** Nama kunci penyimpan batas waktu buka-paksa untuk sebuah fase. */
    private static function kunciPaksaSampai(string $fase): string
    {
        return static::kunciPaksa($fase) . '_sampai';
    }

    /** Batas waktu buka-paksa. null = tanpa batas waktu. */
    public static function tenggatPaksa(string $fase): ?Carbon
    {
        $nilai = trim((string) static::ambil(static::kunciPaksaSampai($fase), ''));

        if ($nilai === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai, config('app.timezone', 'Asia/Makassar'));
        } catch (Throwable $e) {
            // Nilai rusak dianggap tanpa batas agar absensi tidak ikut terkunci.
            return null;
        }
    }

    /** Batas waktu sudah lewat? (null = tanpa batas, tidak pernah lewat) */
    public static function paksaKedaluwarsa(string $fase): bool
    {
        $tenggat = static::tenggatPaksa($fase);

        return $tenggat !== null && $tenggat->isPast();
    }

    /** Buka-paksa fase ini masih berlaku? (flag menyala DAN belum lewat tenggat) */
    public static function paksaBukaAktif(string $fase): bool
    {
        return static::ambil(static::kunciPaksa($fase), '0') === '1'
            && ! static::paksaKedaluwarsa($fase);
    }

    /** Sisa waktu buka-paksa dalam detik. null = tanpa batas / tidak dibuka. */
    public static function sisaDetikPaksa(string $fase): ?int
    {
        if (! static::paksaBukaAktif($fase)) {
            return null;
        }

        $tenggat = static::tenggatPaksa($fase);

        return $tenggat === null
            ? null
            : max(0, (int) round(now()->diffInSeconds($tenggat, false)));
    }

    /**
     * Label ramah sisa waktu buka-paksa, mis. "24 menit lagi".
     * Kosong bila fase tersebut memang tidak sedang dibuka-paksa.
     */
    public static function labelSisaPaksa(string $fase): string
    {
        if (! static::paksaBukaAktif($fase)) {
            return '';
        }

        $detik = static::sisaDetikPaksa($fase);

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

    /**
     * Buka / tutup satu fase absensi secara global sekaligus menyimpan batas
     * waktunya. Menutup fase juga membersihkan tenggat yang tersimpan.
     *
     * @param  string       $fase    "masuk" atau "pulang"
     * @param  bool         $buka    true = buka, false = tutup
     * @param  Carbon|null  $sampai  null = tanpa batas waktu
     */
    public static function aturPaksa(string $fase, bool $buka, ?Carbon $sampai = null): void
    {
        static::simpan(static::kunciPaksa($fase), $buka ? '1' : '0');
        static::simpan(
            static::kunciPaksaSampai($fase),
            $buka && $sampai ? $sampai->format('Y-m-d H:i:s') : ''
        );
    }

    /**
     * Matikan flag buka-paksa yang batas waktunya sudah lewat. Dipanggil saat
     * halaman monitoring admin dimuat sehingga status yang tampil selalu jujur
     * tanpa perlu scheduler. Aman dipanggil berkali-kali (idempoten).
     *
     * @return int jumlah fase yang baru saja ditutup otomatis
     */
    public static function bersihkanPaksaKedaluwarsa(): int
    {
        $jumlah = 0;

        foreach (['masuk', 'pulang'] as $fase) {
            if (! static::paksaKedaluwarsa($fase)) {
                continue;
            }

            static::aturPaksa($fase, false);
            $jumlah++;
        }

        return $jumlah;
    }
}
