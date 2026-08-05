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
        'foto_bukti',             // kolom lama (alur foto bukti fisik) - dibiarkan untuk data lama
        'ttd_instruktur',         // path PNG tanda tangan digital instruktur
        'ttd_instruktur_nama',    // nama yang dicetak di bawah paraf
        'ttd_signed_at',          // waktu instruktur menandatangani
        'catatan_instruktur',
        'is_approved',         // kolom lama (dibiarkan)
        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'validated_at'  => 'datetime',
        'ttd_signed_at' => 'datetime',
    ];

    /** Sudah ditandatangani instruktur secara digital? */
    public function sudahDitandatangani(): bool
    {
        return ! empty($this->ttd_instruktur);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id')->withTrashed();
    }
}