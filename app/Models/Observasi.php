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
        'status',                 // draft | diajukan | tervalidasi
        'diajukan_at',            // waktu guru mengajukan (menunggu divalidasi wakasek)
        'foto_dokumentasi',       // foto kegiatan/kunjungan (diunggah saat mengajukan)
        'foto_lembar_observasi',  // foto lembar fisik yang sudah diparaf instruktur & guru
        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'hari_tanggal' => 'date',
        'diajukan_at'  => 'datetime',
        'validated_at' => 'datetime',
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

    // Guru pembimbing yang memvalidasi
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id');
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

    /** True bila lembar observasi sudah divalidasi. */
    public function getIsTervalidasiAttribute(): bool
    {
        return $this->status === 'tervalidasi';
    }

    /** True bila lembar observasi sedang menunggu divalidasi wakasek. */
    public function getIsDiajukanAttribute(): bool
    {
        return $this->status === 'diajukan';
    }

    /** True bila lembar observasi masih draft (belum diajukan). */
    public function getIsDraftAttribute(): bool
    {
        return ($this->status ?? 'draft') === 'draft';
    }
}