<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * TANGGAL MERAH / HARI LIBUR
 *
 * Satu baris = satu hari libur ATAU satu rentang hari libur
 * (tanggal_selesai kosong berarti liburnya hanya satu hari).
 *
 * Dipakai di tiga tempat:
 *   1. Absensi::sinkronkanAlpa()      -> tanggal merah tidak pernah jadi Alpha
 *   2. AbsensiController::jendelaAbsensi() -> absensi tidak perlu diisi
 *   3. partials/notifikasi.blade.php   -> tidak ada notifikasi pengingat
 *
 * Karena ketiganya berjalan pada hampir SETIAP request, pembacaan tanggal
 * libur TIDAK boleh menembak database terus-menerus. Sebab itu seluruh
 * tanggal disusun sekali menjadi peta [Y-m-d => nama] lalu disimpan di cache,
 * dan cache dibuang otomatis setiap kali data berubah.
 */
class HariLibur extends Model
{
    protected $table = 'hari_liburs';

    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'aktif',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'aktif'           => 'boolean',
    ];

    /** Kunci cache peta tanggal libur. */
    public const CACHE_KUNCI = 'hari_libur:peta';

    /** Umur cache peta tanggal libur, dalam detik (15 menit). */
    public const CACHE_DETIK = 900;

    /**
     * Batas aman panjang satu rentang libur, dalam hari.
     * Melindungi memori bila ada satu baris yang tanggalnya salah ketik
     * (mis. tahun 2026 vs 2062).
     */
    public const MAKS_HARI_RENTANG = 366;

    protected static function booted(): void
    {
        // Setiap perubahan data langsung membuang cache, sehingga aturan libur
        // berlaku SEKETIKA tanpa menunggu masa cache habis.
        static::saved(fn () => static::lupakanCache());
        static::deleted(fn () => static::lupakanCache());
    }

    /** Buang cache peta tanggal libur. */
    public static function lupakanCache(): void
    {
        Cache::forget(self::CACHE_KUNCI);
    }

    /*
    |--------------------------------------------------------------------------
    | PEMBACAAN CEPAT: PETA TANGGAL LIBUR
    |--------------------------------------------------------------------------
    */

    /**
     * Peta seluruh tanggal libur yang AKTIF: ['2026-08-17' => 'Kemerdekaan RI'].
     *
     * Dibungkus try/catch supaya aplikasi tetap jalan bila migrasi tabel
     * hari_liburs belum dijalankan di server (dianggap belum ada tanggal merah)
     * ketimbang melempar "Base table not found" ke wajah pengguna.
     */
    public static function peta(): array
    {
        return Cache::remember(self::CACHE_KUNCI, self::CACHE_DETIK, function () {
            $peta = [];

            try {
                $daftar = static::query()->where('aktif', true)->get();
            } catch (Throwable $e) {
                return [];
            }

            foreach ($daftar as $libur) {
                foreach ($libur->rentangTanggal() as $tanggal) {
                    $peta[$tanggal] = $libur->nama;
                }
            }

            return $peta;
        });
    }

    /** Nama libur pada satu tanggal, atau null bila hari itu bukan tanggal merah. */
    public static function namaLibur($tanggal): ?string
    {
        $kunci = static::kunciTanggal($tanggal);

        if ($kunci === null) {
            return null;
        }

        return static::peta()[$kunci] ?? null;
    }

    /** Apakah satu tanggal termasuk tanggal merah yang aktif? */
    public static function adalahLibur($tanggal): bool
    {
        return static::namaLibur($tanggal) !== null;
    }

    /** Ubah Carbon / string / DateTime menjadi 'Y-m-d'. Null bila tidak terbaca. */
    private static function kunciTanggal($tanggal): ?string
    {
        if (blank($tanggal)) {
            return null;
        }

        if ($tanggal instanceof Carbon) {
            return $tanggal->format('Y-m-d');
        }

        if ($tanggal instanceof \DateTimeInterface) {
            return $tanggal->format('Y-m-d');
        }

        try {
            return Carbon::parse($tanggal)->format('Y-m-d');
        } catch (Throwable $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RENTANG TANGGAL SATU BARIS
    |--------------------------------------------------------------------------
    */

    /** Tanggal terakhir libur ini (sama dengan tanggal mulai bila hanya sehari). */
    public function tanggalAkhir(): Carbon
    {
        $mulai = $this->tanggal_mulai instanceof Carbon
            ? $this->tanggal_mulai->copy()
            : Carbon::parse((string) $this->tanggal_mulai);

        if (blank($this->tanggal_selesai)) {
            return $mulai;
        }

        $selesai = $this->tanggal_selesai instanceof Carbon
            ? $this->tanggal_selesai->copy()
            : Carbon::parse((string) $this->tanggal_selesai);

        return $selesai->lt($mulai) ? $mulai : $selesai;
    }

    /** Semua tanggal 'Y-m-d' yang dicakup baris ini. */
    public function rentangTanggal(): array
    {
        $mulai = $this->tanggal_mulai instanceof Carbon
            ? $this->tanggal_mulai->copy()
            : Carbon::parse((string) $this->tanggal_mulai);

        $selesai = $this->tanggalAkhir();

        // Jaga-jaga bila tanggal salah ketik sehingga rentangnya bertahun-tahun.
        $batas = $mulai->copy()->addDays(self::MAKS_HARI_RENTANG);
        if ($selesai->gt($batas)) {
            $selesai = $batas;
        }

        $hasil = [];

        for ($hari = $mulai->copy(); $hari->lte($selesai); $hari->addDay()) {
            $hasil[] = $hari->format('Y-m-d');
        }

        return $hasil;
    }

    /** Jumlah hari yang dicakup baris ini. */
    public function getJumlahHariAttribute(): int
    {
        return count($this->rentangTanggal());
    }

    /** Libur ini hanya satu hari? */
    public function getSatuHariAttribute(): bool
    {
        return $this->jumlah_hari <= 1;
    }

    /**
     * Label tanggal siap tampil:
     *   satu hari  -> "17 Agustus 2026"
     *   rentang    -> "20 - 25 Maret 2026"
     */
    public function getLabelTanggalAttribute(): string
    {
        $mulai = $this->tanggal_mulai instanceof Carbon
            ? $this->tanggal_mulai->copy()
            : Carbon::parse((string) $this->tanggal_mulai);

        $mulai->locale('id');

        if ($this->satu_hari) {
            return $mulai->translatedFormat('d F Y');
        }

        $akhir = $this->tanggalAkhir()->locale('id');

        // Bulan & tahun sama -> cukup tulis sekali di belakang.
        if ($mulai->format('Y-m') === $akhir->format('Y-m')) {
            return $mulai->format('d') . ' - ' . $akhir->translatedFormat('d F Y');
        }

        if ($mulai->format('Y') === $akhir->format('Y')) {
            return $mulai->translatedFormat('d F') . ' - ' . $akhir->translatedFormat('d F Y');
        }

        return $mulai->translatedFormat('d F Y') . ' - ' . $akhir->translatedFormat('d F Y');
    }

    /** Libur ini sudah terlewat seluruhnya? */
    public function getSudahLewatAttribute(): bool
    {
        return $this->tanggalAkhir()->lt(
            Carbon::today(config('app.timezone', 'Asia/Makassar'))
        );
    }

    /** Libur ini sedang berlangsung hari ini? */
    public function getSedangBerlangsungAttribute(): bool
    {
        $hariIni = Carbon::today(config('app.timezone', 'Asia/Makassar'))->format('Y-m-d');

        return in_array($hariIni, $this->rentangTanggal(), true);
    }

    /*
    |--------------------------------------------------------------------------
    | PENYARING
    |--------------------------------------------------------------------------
    */

    /** Libur yang bersinggungan dengan satu tahun kalender. */
    public function scopeTahun($query, $tahun)
    {
        $tahun = (int) $tahun;
        $awal  = sprintf('%04d-01-01', $tahun);
        $akhir = sprintf('%04d-12-31', $tahun);

        return $query->where(function ($q) use ($awal, $akhir) {
            $q->whereBetween('tanggal_mulai', [$awal, $akhir])
                ->orWhereBetween('tanggal_selesai', [$awal, $akhir]);
        });
    }

    /** Daftar tahun yang sudah memiliki data, untuk pilihan penyaring. */
    public static function tahunTersedia(): array
    {
        try {
            $baris = static::query()->get(['tanggal_mulai', 'tanggal_selesai']);
        } catch (Throwable $e) {
            return [];
        }

        $tahun = [];

        foreach ($baris as $satu) {
            foreach ([$satu->tanggal_mulai, $satu->tanggal_selesai] as $tanggal) {
                $kunci = static::kunciTanggal($tanggal);

                if ($kunci !== null) {
                    $tahun[] = (int) substr($kunci, 0, 4);
                }
            }
        }

        $tahun = array_values(array_unique($tahun));
        sort($tahun);

        return $tahun;
    }

    /*
    |--------------------------------------------------------------------------
    | PEMBERSIHAN ALPHA YANG SUDAH TERBENTUK
    |--------------------------------------------------------------------------
    | Admin sering menetapkan tanggal merah SETELAH tanggalnya terlewat,
    | sehingga sistem sudah menandai siswa Alpha pada hari itu. Supaya janji
    | "tanggal merah tidak dihitung Alpha" benar-benar terpenuhi, baris Alpha
    | tersebut dibersihkan.
    |
    | Yang dihapus HANYA baris yang jelas dibuat otomatis oleh sistem, yaitu
    | status Alpha TANPA jam masuk, TANPA jam pulang, dan TANPA foto bukti.
    | Absensi yang benar-benar diisi siswa (Hadir/Izin/Sakit, atau Alpha yang
    | masih menyimpan jam/foto sebagai jejak penolakan) TIDAK pernah disentuh.
    */

    /**
     * @param  array<int, string>  $tanggal  daftar tanggal 'Y-m-d'
     * @return int  jumlah baris Alpha otomatis yang dibersihkan
     */
    public static function bersihkanAlpaOtomatis(array $tanggal): int
    {
        $tanggal = array_values(array_unique(array_filter($tanggal)));

        if (empty($tanggal)) {
            return 0;
        }

        try {
            return (int) Absensi::whereIn('tanggal', $tanggal)
                ->where('status', 'Alpha')
                ->whereNull('jam_masuk')
                ->whereNull('jam_pulang')
                ->whereNull('foto_bukti')
                ->delete();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
