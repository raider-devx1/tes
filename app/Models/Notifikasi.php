<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasis';

    protected $fillable = ['user_id', 'judul', 'pesan', 'link', 'dibaca_pada'];

    protected $casts = ['dibaca_pada' => 'datetime'];

    public function scopeBelumDibaca($q)
    {
        return $q->whereNull('dibaca_pada');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Helper pengiriman notifikasi yang dipakai controller (DRY). */
    public static function kirim(int $userId, string $judul, string $pesan, ?string $link = null): self
    {
        return static::create(compact('userId', 'judul', 'pesan', 'link') + [
            'user_id' => $userId,
        ]);
    }
}
