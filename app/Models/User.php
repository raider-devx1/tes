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
