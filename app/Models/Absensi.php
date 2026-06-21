<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id', 'instruktur_id', 'tanggal', 'status', 'jam_masuk', 'jam_pulang',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function instruktur()
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }
}