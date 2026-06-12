<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $table = 'pengaturans';

    protected $fillable = ['kunci', 'nilai'];

    public $timestamps = true;

    /** Ambil semua pengaturan sebagai array kunci => nilai (dengan cache). */
    public static function semua(): array
    {
        return Cache::rememberForever('pengaturan_all', function () {
            return static::pluck('nilai', 'kunci')->toArray();
        });
    }

    public static function ambil(string $kunci, $default = null)
    {
        return static::semua()[$kunci] ?? $default;
    }

    public static function simpan(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        Cache::forget('pengaturan_all');
    }
}
