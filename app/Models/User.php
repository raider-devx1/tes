<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Konstanta peran -> dipakai di seluruh aplikasi agar tidak salah ketik string
    public const ROLE_ADMIN      = 'admin';
    public const ROLE_GURU       = 'guru_pembimbing';
    public const ROLE_SISWA      = 'siswa_pkl';
    public const ROLE_INSTRUKTUR = 'instruktur_industri';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'nis', 'telepon',
        'kelas', 'jurusan', 'perusahaan_id', 'instruktur_id', 'guru_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ---------- Helper peran ---------- */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
    public function isAdmin(): bool      { return $this->role === self::ROLE_ADMIN; }
    public function isGuru(): bool       { return $this->role === self::ROLE_GURU; }
    public function isSiswa(): bool      { return $this->role === self::ROLE_SISWA; }
    public function isInstruktur(): bool { return $this->role === self::ROLE_INSTRUKTUR; }

    /* ---------- Relasi pemetaan ---------- */
    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }
    public function instruktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /* Siswa yang dibimbing (untuk guru / instruktur) */
    public function siswaBimbinganGuru(): HasMany
    {
        return $this->hasMany(User::class, 'guru_id');
    }
    public function siswaBimbinganInstruktur(): HasMany
    {
        return $this->hasMany(User::class, 'instruktur_id');
    }

    /* ---------- Relasi data PKL milik siswa ---------- */
    public function jurnals(): HasMany   { return $this->hasMany(Jurnal::class, 'siswa_id'); }
    public function catatans(): HasMany  { return $this->hasMany(CatatanKegiatan::class, 'siswa_id'); }
    public function observasis(): HasMany { return $this->hasMany(Observasi::class, 'siswa_id'); }
    public function absensis(): HasMany  { return $this->hasMany(Absensi::class, 'siswa_id'); }
    public function dokumens(): HasMany  { return $this->hasMany(Dokumen::class, 'siswa_id'); }
    public function nilai(): BelongsTo
    {
        // didefinisikan sebagai hasOne lewat siswa_id
        return $this->hasOne(Nilai::class, 'siswa_id');
    }
    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class)->latest();
    }
}
