<?php

namespace App\Models;

use App\Models\Concerns\MilikPeriodePkl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory, MilikPeriodePkl;

    /**
     * Kolom yang menyimpan ID siswa pemilik data.
     * Dipakai trait MilikPeriodePkl untuk mengisi periode_id otomatis.
     */
    protected string $kolomPemilikPeriode = 'siswa_id';

    protected $fillable = [
        'siswa_id',
        'periode_id',   // periode PKL tempat data ini dibuat (diisi otomatis)
        'hari_tanggal',
        'status',                 // draft | diajukan | disetujui
        'foto_bukti',
        'catatan_instruktur',
        'status_persetujuan',     // kolom lama (dibiarkan agar tidak merusak data lama)
        'disetujui_oleh',
        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'hari_tanggal' => 'date',
        'validated_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id')->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(JurnalItem::class, 'jurnal_id');
    }

    // Guru pembimbing yang memvalidasi
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id')->withTrashed();
    }
}