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
    'jabatan',
    'kelas',
    'jurusan',
    'perusahaan_id',
    'instruktur_id',
    'guru_id',
    'periode_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi Pemetaan: Siswa magang di Perusahaan apa
     */
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    /**
     * Relasi Pemetaan: Siswa dibimbing oleh Instruktur siapa
     */
    public function instruktur()
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }

    /**
     * Relasi Pemetaan: Siswa dipantau oleh Guru siapa
     */
    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
    /**
     * Relasi ke model Nilai (Siswa memiliki 1 data nilai dari instruktur)
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