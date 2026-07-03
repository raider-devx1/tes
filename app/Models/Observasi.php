<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Observasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guru_id',
        'hari_tanggal',
        'pekerjaan_projek',
        'is_approved',
    ];

    protected $casts = [
        'hari_tanggal' => 'date',
        'is_approved'  => 'boolean',
    ];

    // Siswa yang diobservasi
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Guru pembimbing yang mengisi
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    // Banyak poin permasalahan & solusi
    public function items()
    {
        return $this->hasMany(ObservasiItem::class, 'observasi_id');
    }

    /** Daftar poin observasi (semua data berasal dari observasi_items). */
    public function getPoinAttribute(): Collection
    {
        return $this->items;
    }
}