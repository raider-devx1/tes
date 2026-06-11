<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id', 'instruktur_id', 'tanggal', 'status', 'jam_masuk', 'jam_pulang'
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}