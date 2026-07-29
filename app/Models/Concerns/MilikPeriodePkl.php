<?php

namespace App\Models\Concerns;

use App\Models\PeriodePkl;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mengikat sebuah baris data transaksi PKL ke Periode PKL tempat data itu dibuat.
 *
 * LATAR BELAKANG
 * --------------
 * Sebelumnya kolom `periode_id` HANYA ada di tabel `users`. Semua data transaksi
 * (jurnal, absensi, nilai, catatan, observasi, dokumen) hanya menempel ke siswa.
 *
 * Akibatnya, saat tahun ajaran berganti dan admin memperbarui `periode_id` siswa,
 * seluruh riwayat lama siswa itu ikut "berpindah" ke periode baru — karena data
 * riwayat tidak pernah menyimpan periodenya sendiri. Arsip angkatan lama jadi rusak.
 *
 * Trait ini membuat setiap baris data MEREKAM periodenya sendiri saat dibuat,
 * sehingga riwayat menjadi permanen dan tidak terpengaruh perubahan data siswa.
 *
 * CARA PAKAI
 * ----------
 *   class Jurnal extends Model
 *   {
 *       use HasFactory, MilikPeriodePkl;
 *
 *       protected string $kolomPemilikPeriode = 'siswa_id';
 *   }
 *
 * Pastikan 'periode_id' juga masuk ke $fillable.
 */
trait MilikPeriodePkl
{
    /**
     * CATATAN PERUBAHAN -- cache dipindah ke App\Support\KonteksPeriode.
     *
     * Dulu trait ini menyimpan cache-nya sendiri di properti static.
     * Masalahnya: properti static di dalam trait DISALIN ke setiap kelas
     * yang memakainya. Trait ini dipakai 6 model (Jurnal, Absensi, Nilai,
     * Dokumen, Observasi, CatatanKegiatan), sehingga ada 6 cache terpisah
     * yang harus dikosongkan satu per satu -- dan pasti ada yang terlewat.
     *
     * Sekarang seluruh aplikasi memakai SATU cache saja, di KonteksPeriode.
     * Satu pemanggilan KonteksPeriode::lupakan() membersihkan semuanya.
     */

    /**
     * Dipanggil otomatis oleh Eloquent (boot<NamaTrait>).
     * Mengisi periode_id sebelum baris disimpan pertama kali.
     */
    public static function bootMilikPeriodePkl(): void
    {
        static::creating(function (Model $model): void {
            // Hormati nilai yang sudah diisi manual (mis. saat import data lama).
            if (! empty($model->periode_id)) {
                return;
            }

            $model->periode_id = static::tentukanPeriodeId($model);
        });
    }

    /**
     * Tentukan periode untuk baris ini.
     *
     * Urutan prioritas:
     *   1. Periode milik siswa pemilik data  -> paling akurat, tahan terhadap
     *      kasus admin menginput data susulan saat periode sudah berganti.
     *   2. Periode yang sedang aktif         -> jaring pengaman.
     *   3. null                              -> dibiarkan kosong, tidak menggagalkan
     *      penyimpanan. Data tetap tersimpan; periode bisa dibackfill belakangan.
     */
    protected static function tentukanPeriodeId(Model $model): ?int
    {
        $kolom    = $model->kolomPemilikPeriode();
        $pemilikId = $model->{$kolom} ?? null;

        if (! empty($pemilikId)) {
            $periodeSiswa = User::withoutGlobalScopes()
                ->whereKey($pemilikId)
                ->value('periode_id');

            if (! empty($periodeSiswa)) {
                return (int) $periodeSiswa;
            }
        }

        return static::periodeAktifId();
    }

    /**
     * ID periode aktif, di-cache selama request berjalan.
     *
     * Kini hanya meneruskan ke KonteksPeriode supaya definisi "periode
     * berjalan" di model transaksi PERSIS sama dengan yang dipakai
     * User::siswaBerjalan() dan seluruh controller. Nama method
     * dipertahankan agar pemanggil lama tetap jalan
     * (mis. Absensi::sinkronkanAlpa()).
     */
    public static function periodeAktifId(): ?int
    {
        return \App\Support\KonteksPeriode::id();
    }

    /**
     * Kosongkan cache periode aktif.
     *
     * Dipertahankan demi kompatibilitas; cukup meneruskan ke satu-satunya
     * cache yang kini ada. Dalam pemakaian normal Anda tidak perlu
     * memanggil ini -- PeriodePkl sudah melakukannya otomatis lewat
     * event saved() dan deleted().
     */
    public static function lupakanPeriodeAktif(): void
    {
        \App\Support\KonteksPeriode::lupakan();
    }

    /**
     * Nama kolom yang menyimpan ID siswa pemilik data.
     * Berbeda antar tabel: 'siswa_id' (jurnal/absensi/dokumen)
     * dan 'user_id' (nilai/catatan/observasi).
     */
    public function kolomPemilikPeriode(): string
    {
        return property_exists($this, 'kolomPemilikPeriode')
            ? $this->kolomPemilikPeriode
            : 'siswa_id';
    }

    /** Periode PKL tempat baris data ini dibuat. */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    /**
     * Saring data pada satu periode tertentu.
     * Nama tabel disebut eksplisit agar aman dipakai bersama join.
     *
     * Contoh: Jurnal::periode($request->periode_id)->get();
     */
    public function scopePeriode(Builder $query, PeriodePkl|int|string|null $periode): Builder
    {
        $id = $periode instanceof PeriodePkl ? $periode->id : $periode;

        if (empty($id)) {
            return $query;
        }

        return $query->where($this->getTable() . '.periode_id', $id);
    }

    /**
     * Saring data pada periode yang sedang aktif.
     * Bila belum ada periode aktif, query dibiarkan apa adanya.
     */
    public function scopePeriodeAktif(Builder $query): Builder
    {
        return $this->scopePeriode($query, static::periodeAktifId());
    }

    /**
     * Padanan User::berjalan() untuk model transaksi.
     *
     * Memakai KonteksPeriode agar satu request hanya sekali menanyakan
     * periode aktif ke database, dan agar seluruh aplikasi memakai
     * definisi 'angkatan berjalan' yang sama persis.
     */
    public function scopePeriodeBerjalan(Builder $query): Builder
    {
        return $this->scopePeriode($query, \App\Support\KonteksPeriode::id());
    }

    /** Data dari periode-periode sebelumnya (arsip angkatan lama). */
    public function scopePeriodeArsip(Builder $query): Builder
    {
        $aktif = static::periodeAktifId();

        if (empty($aktif)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($aktif): void {
            $q->where($this->getTable() . '.periode_id', '!=', $aktif)
              ->orWhereNull($this->getTable() . '.periode_id');
        });
    }
}
