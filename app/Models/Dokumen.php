<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    use HasFactory;
    protected $fillable = ['siswa_id', 'laporan_akhir', 'surat_tugas', 'surat_penerimaan'];

    public function siswa() { return $this->belongsTo(User::class, 'siswa_id'); }
}