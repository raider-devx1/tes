<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodePkl extends Model
{
    use HasFactory;

    protected $table = 'periode_pkls';

    protected $fillable = [
        'nama', 'tahun_ajaran', 'tanggal_mulai',
        'tanggal_selesai', 'is_active', 'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
    ];

    /**
     * Aturan: hanya boleh ada SATU periode aktif.
     * Saat sebuah periode di-set aktif, periode lain otomatis dinonaktifkan.
     */
    protected static function booted(): void
    {
        static::saving(function (PeriodePkl $periode) {
            if ($periode->is_active) {
                static::where('id', '!=', $periode->id ?? 0)
                    ->update(['is_active' => false]);
            }
        });

        // ---------------------------------------------------------------
        // Begitu daftar periode berubah, cache "periode mana yang sedang
        // berjalan" WAJIB dibuang. Kalau tidak, sisa request ini masih
        // memakai ID periode yang lama -- dan itu tidak memunculkan error
        // apa pun, hanya angka yang diam-diam salah.
        //
        // Dipasang di model, bukan di controller, supaya berlaku untuk
        // SEMUA jalur: tombol admin, perintah artisan, seeder, import
        // massal, bahkan kode yang belum ditulis.
        //
        // saving() di atas memakai query builder (->update) yang TIDAK
        // memicu event model, jadi penonaktifan periode lain tidak akan
        // memanggil hook ini berulang kali.
        // ---------------------------------------------------------------
        static::saved(function () {
            \App\Support\KonteksPeriode::lupakan();
        });

        // Periode aktif yang dihapus juga membuat cache basi.
        static::deleted(function () {
            \App\Support\KonteksPeriode::lupakan();
        });
    }

    public function siswa()
    {
        return $this->hasMany(User::class, 'periode_id');
    }

    /** Ambil periode yang sedang aktif (atau null). */
    public static function aktif(): ?self
    {
        return static::where('is_active', true)->first();
    }
}