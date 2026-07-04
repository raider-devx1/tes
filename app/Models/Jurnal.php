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
        'catatan_instruktur',
        'status_persetujuan',
        'disetujui_oleh',
    ];

    protected $casts = [
        'hari_tanggal' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    // Banyak pekerjaan / unit kerja dalam 1 tanggal
    public function items()
    {
        return $this->hasMany(JurnalItem::class, 'jurnal_id');
    }
}