<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'instruktur_id',
        'guru_id',
        'soft_skill',
        'hard_skill',
        'pengembangan_hard_skill',
        'kewirausahaan',
        'rata_rata',
        'catatan_rekomendasi',
        'nilai_guru',
        'nilai_laporan',
        'catatan_guru',
        'nilai_akhir',
    ];

    // Relasi ke Siswa
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Instruktur Industri
    public function instruktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }

    // Relasi ke Guru Pembimbing
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}