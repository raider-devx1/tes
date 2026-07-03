<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'hari_tanggal',
        'unit_kerja',
        'dokumentasi',
        'catatan_instruktur',
        'status_persetujuan',
        'disetujui_oleh',
    ];

    // Relasi balik ke User (Siswa)
    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}