<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dokumen extends Model
{
    protected $fillable = ['siswa_id', 'jenis', 'judul', 'path'];

    public const JENIS = [
        'surat_tugas'      => 'Surat Tugas PKL',
        'surat_penerimaan' => 'Surat Penerimaan Industri',
        'laporan_final'    => 'Laporan PKL Final',
        'lainnya'          => 'Lainnya',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }
}
