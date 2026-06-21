<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guru_id',
        'hari_tanggal',
        'pekerjaan_projek',
        'permasalahan',
        'solusi',
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
}