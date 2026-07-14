<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanKegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        return $this->belongsTo(User::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id');
    }
}