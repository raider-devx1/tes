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
        'catatan_instruktur',
        'is_approved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}