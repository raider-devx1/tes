<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guru_id',
        'hari_tanggal',
        'pekerjaan_projek',
        'permasalahan',
        'solusi',
        'is_approved',
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function guru() { return $this->belongsTo(User::class, 'guru_id'); }
}