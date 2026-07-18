<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'status',            // Hadir | Izin | Sakit | Alpha
        'jam_masuk',
        'jam_pulang',
        'status_validasi',   // draft | diajukan | disetujui
        'foto_bukti',
        'catatan_instruktur',
        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'validated_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

   

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id');
    }
}