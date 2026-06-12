<?php

namespace App\Models;

use App\Models\Concerns\PunyaStatusPersetujuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observasi extends Model
{
    use PunyaStatusPersetujuan;

    protected $fillable = [
        'siswa_id', 'guru_id', 'hari_tanggal', 'permasalahan', 'solusi',
        'status_persetujuan', 'disetujui_oleh',
    ];

    protected $casts = ['hari_tanggal' => 'date'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
