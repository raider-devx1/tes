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