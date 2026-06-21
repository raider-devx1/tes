<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}