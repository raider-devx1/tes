<?php

namespace App\Models;

use App\Models\Concerns\MilikPeriodePkl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
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

        // Identitas siswa yang dipakai pada hasil CETAK penilaian guru
        'label_identitas',   // 'nisn' (bawaan) atau 'nis'
        'nomor_identitas',   // nomor yang diketik bebas oleh guru

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
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    // Instruktur industri kini data (nama pembimbing pada Perusahaan siswa), bukan akun.
    public function getInstrukturAttribute(): object
    {
        $namaPembimbing = $this->user?->perusahaan?->pembimbing_industri;

        return (object) [
            'name' => $namaPembimbing ?: 'Belum Diatur',
            'nip'  => '-',
        ];
    }

    // Relasi ke Guru Pembimbing
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id')->withTrashed();
    }

    /* ============ IDENTITAS UNTUK HASIL CETAK ============ */

    public const LABEL_NISN = 'nisn';
    public const LABEL_NIS  = 'nis';

    /**
     * Kata yang dicetak pada baris identitas: "NISN" atau "NIS".
     * Bawaan tetap "NISN" agar cetakan lama tidak berubah.
     */
    public function getLabelIdentitasCetakAttribute(): string
    {
        return $this->label_identitas === self::LABEL_NIS ? 'NIS' : 'NISN';
    }

    /**
     * Nomor yang dicetak pada baris identitas.
     * Bila guru tidak mengisi nomor, dipakai NISN bawaan milik siswa.
     */
    public function getNomorIdentitasCetakAttribute(): string
    {
        $nomor = trim((string) ($this->nomor_identitas ?? ''));

        if ($nomor !== '') {
            return $nomor;
        }

        // Nomor kosong: hanya masuk akal sebagai fallback untuk NISN siswa.
        return trim((string) ($this->user->nisn ?? '')) ?: '-';
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
