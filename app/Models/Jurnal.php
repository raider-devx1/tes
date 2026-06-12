<?php

namespace App\Models;

use App\Models\Concerns\PunyaStatusPersetujuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jurnal extends Model
{
    use PunyaStatusPersetujuan;

    protected $fillable = [
        'siswa_id', 'hari_tanggal', 'unit_kerja', 'deskripsi_pekerjaan',
        'dokumentasi', 'catatan_instruktur', 'status_persetujuan', 'disetujui_oleh',
    ];

    protected $casts = ['hari_tanggal' => 'date'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
