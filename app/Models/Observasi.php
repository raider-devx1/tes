<?php

namespace App\Models;

use App\Models\Concerns\MilikPeriodePkl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Observasi extends Model
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
        'guru_id',
        'hari_tanggal',
        'pekerjaan_projek',
        'status',                 // draft | diajukan | tervalidasi
        'diajukan_at',            // waktu guru mengajukan (menunggu divalidasi wakasek)
        'foto_dokumentasi',       // BUKTI FOTO OBSERVASI (diunggah saat mengajukan)
        'foto_lembar_observasi',  // LEGACY: foto lembar fisik berparaf (alur lama, hanya untuk data lama)

        // ===== Paraf digital (kanvas di web/HP), pengganti foto lembar berparaf =====
        'ttd_guru',                 // path PNG paraf guru pembimbing
        'ttd_guru_nama',            // nama guru saat memaraf
        'ttd_guru_signed_at',
        'ttd_instruktur',           // path PNG paraf instruktur (dibubuhkan di bawah paraf guru)
        'ttd_instruktur_nama',
        'ttd_instruktur_signed_at',

        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'hari_tanggal'             => 'date',
        'diajukan_at'              => 'datetime',
        'validated_at'             => 'datetime',
        'ttd_guru_signed_at'       => 'datetime',
        'ttd_instruktur_signed_at' => 'datetime',
    ];

    // Siswa yang diobservasi
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    // Guru pembimbing yang mengisi
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id')->withTrashed();
    }

    // Guru pembimbing yang memvalidasi
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id')->withTrashed();
    }

    // Banyak poin permasalahan & solusi
    public function items()
    {
        return $this->hasMany(ObservasiItem::class, 'observasi_id');
    }

    /** Daftar poin observasi (semua data berasal dari observasi_items). */
    public function getPoinAttribute(): Collection
    {
        return $this->items;
    }

    /** True bila lembar observasi sudah divalidasi. */
    public function getIsTervalidasiAttribute(): bool
    {
        return $this->status === 'tervalidasi';
    }

    /** True bila lembar observasi sedang menunggu divalidasi wakasek. */
    public function getIsDiajukanAttribute(): bool
    {
        return $this->status === 'diajukan';
    }

    /** True bila lembar observasi masih draft (belum diajukan). */
    public function getIsDraftAttribute(): bool
    {
        return ($this->status ?? 'draft') === 'draft';
    }

    /** True bila paraf digital guru pembimbing & instruktur sudah lengkap. */
    public function getSudahDiparafAttribute(): bool
    {
        return (bool) ($this->ttd_guru && $this->ttd_instruktur);
    }

    /** True bila data ini masih memakai bukti lama (foto lembar berparaf). */
    public function getPakaiBuktiLamaAttribute(): bool
    {
        return ! $this->ttd_guru
            && ! $this->ttd_instruktur
            && (bool) $this->foto_lembar_observasi;
    }
}