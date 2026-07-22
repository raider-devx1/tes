<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'no_hp',
    'foto',
    'nisn',
    'jenis_kelamin',
    'status_pkl',
    'nip',
    'is_wakasek',
    'is_admin',
    'jabatan',
    'kelas',
    'jurusan',
    // --- Jam kerja industri (per-siswa) ---
    'jam_masuk_industri',
    'jam_pulang_industri',
    'jam_masuk_usulan',
    'jam_pulang_usulan',
    'status_jam_usulan',
    'catatan_jam_usulan',
    // --- Pembukaan absensi manual (per-siswa) ---
    'absensi_dibuka',
    // --- Relasi ---
    'perusahaan_id',
    'guru_id',
    'periode_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_wakasek' => 'boolean',
            'is_admin' => 'boolean',
            'absensi_dibuka' => 'boolean',
        ];
    }

    /**
     * Apakah guru ini ditetapkan sebagai Wakasek oleh admin.
     * Wakasek berhak memvalidasi lembar observasi guru lain
     * dan boleh memvalidasi lembar observasinya sendiri.
     */
    public function isWakasek(): bool
    {
        return (bool) $this->is_wakasek;
    }

    /** Scope: hanya guru pembimbing yang berstatus Wakasek. */
    public function scopeWakasek($query)
    {
        return $query->where('role', 'guru_pembimbing')->where('is_wakasek', true);
    }

    /*
    |--------------------------------------------------------------------------
    | JAM KERJA INDUSTRI (per-siswa) — dipakai untuk jendela absensi
    |--------------------------------------------------------------------------
    | - Jika siswa memiliki jam khusus yang SUDAH disetujui guru
    |   (jam_masuk_industri / jam_pulang_industri), gunakan itu.
    | - Jika tidak, gunakan jam GLOBAL yang diatur admin (tabel pengaturans).
    */
    public function jamMasukEfektif(): string
    {
        if (! empty($this->jam_masuk_industri)) {
            return substr((string) $this->jam_masuk_industri, 0, 5);
        }

        return (string) Pengaturan::ambil('absensi_jam_masuk', '08:00');
    }

    public function jamPulangEfektif(): string
    {
        if (! empty($this->jam_pulang_industri)) {
            return substr((string) $this->jam_pulang_industri, 0, 5);
        }

        return (string) Pengaturan::ambil('absensi_jam_pulang', '16:00');
    }

    /** True bila siswa memakai jam khusus (bukan jam global admin). */
    public function pakaiJamKhusus(): bool
    {
        return $this->status_jam_usulan === 'disetujui'
            && (! empty($this->jam_masuk_industri) || ! empty($this->jam_pulang_industri));
    }

    /**
     * Relasi Pemetaan: Siswa magang di Perusahaan apa
     */
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    /**
     * Instruktur industri kini berupa DATA (nama pembimbing pada Perusahaan),
     * bukan akun user. Accessor ini menjaga tampilan lama yang masih memanggil
     * $siswa->instruktur->name atau $siswa->instruktur->nip tetap berfungsi.
     */
    public function getInstrukturAttribute(): object
    {
        $namaPembimbing = $this->perusahaan?->pembimbing_industri;

        return (object) [
            'name' => $namaPembimbing ?: 'Belum Diatur',
            'nip'  => '-',
        ];
    }

    /**
     * Relasi Pemetaan: Siswa dipantau oleh Guru siapa
     */
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relasi ke model Nilai (Siswa memiliki 1 data nilai)
     */
    public function nilai()
    {
        return $this->hasOne(Nilai::class, 'user_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePkl::class, 'periode_id');
    }

    public function dokumen()
    {
        return $this->hasOne(\App\Models\Dokumen::class, 'siswa_id');
    }
}
