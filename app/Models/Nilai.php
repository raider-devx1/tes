<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'instruktur_id',
        'soft_skill',
        'hard_skill',
        'pengembangan_hard_skill',
        'kewirausahaan',
        'rata_rata',
        'catatan_rekomendasi',
    ];

    // Relasi ke Siswa
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Instruktur Industri
    public function instruktur()
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }
    
}