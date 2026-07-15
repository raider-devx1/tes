<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'instruktur_id',
        'guru_id',

        // Kolom lama instruktur (skala 1-5) — dibiarkan untuk kompatibilitas data lama
        'soft_skill',
        'hard_skill',
        'pengembangan_hard_skill',
        'kewirausahaan',
        'rata_rata',
        'catatan_rekomendasi',

        // --- Komponen Penilaian Guru (skala 0-100) ---
        'skor_soft_skill', 'deskripsi_soft_skill',
        'skor_hard_skill', 'deskripsi_hard_skill',
        'skor_pengembangan', 'deskripsi_pengembangan',
        'skor_kewirausahaan', 'deskripsi_kewirausahaan',
        'skor_laporan', 'deskripsi_laporan',
        'skor_presentasi', 'deskripsi_presentasi',
        'catatan_guru',

        // Foto lembar penilaian instruktur (diunggah guru)
        'foto_lembar_instruktur',

        'nilai_guru',
        'nilai_laporan',
        'nilai_akhir',
    ];

    // Relasi ke Siswa
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Instruktur Industri
    public function instruktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }

    // Relasi ke Guru Pembimbing
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /** Daftar 6 komponen skor penilaian guru. */
    public function getDaftarSkorAttribute(): array
    {
        return [
            $this->skor_soft_skill,
            $this->skor_hard_skill,
            $this->skor_pengembangan,
            $this->skor_kewirausahaan,
            $this->skor_laporan,
            $this->skor_presentasi,
        ];
    }

    /** True bila 6 komponen sudah terisi semua. */
    public function getSemuaNilaiTerisiAttribute(): bool
    {
        return ! in_array(null, $this->daftar_skor, true);
    }

    /** Rata-rata akhir (0-100). Null bila belum lengkap. */
    public function getRataRataAkhirAttribute(): ?float
    {
        if (! $this->semua_nilai_terisi) {
            return null;
        }

        return round(array_sum($this->daftar_skor) / count($this->daftar_skor), 2);
    }
}