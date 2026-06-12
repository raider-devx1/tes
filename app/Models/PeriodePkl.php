<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodePkl extends Model
{
    protected $table = 'periode_pkls';

    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_selesai', 'aktif'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'aktif' => 'boolean',
    ];

    public static function aktif(): ?self
    {
        return static::where('aktif', true)->latest()->first();
    }
}
