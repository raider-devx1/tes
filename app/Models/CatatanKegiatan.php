<?php

namespace App\Models;

use App\Models\Concerns\MilikPeriodePkl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanKegiatan extends Model
{
    use HasFactory, MilikPeriodePkl;

    /**
     * Kolom yang menyimpan ID siswa pemilik data.
     * Dipakai trait MilikPeriodePkl untuk mengisi periode_id otomatis.
     */
    protected string $kolomPemilikPeriode = 'user_id';

    protected $fillable = [
        'user_id',
        'periode_id',   // periode PKL tempat data ini dibuat (diisi otomatis)
        'nama_pekerjaan',
        'perencanaan_kegiatan',
        'pelaksanaan_kegiatan',
        'status',              // draft | diajukan | disetujui
        'foto_bukti',
        'catatan_instruktur',
        'is_approved',         // kolom lama (dibiarkan)
        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id')->withTrashed();
    }
}