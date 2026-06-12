<?php

namespace App\Models;

use App\Models\Concerns\PunyaStatusPersetujuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanKegiatan extends Model
{
    use PunyaStatusPersetujuan;

    protected $table = 'catatan_kegiatans';

    protected $fillable = [
        'siswa_id', 'nama_pekerjaan', 'perencanaan', 'pelaksanaan',
        'catatan_instruktur', 'status_persetujuan', 'disetujui_oleh',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
