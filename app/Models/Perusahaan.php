<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_perusahaan',
        'alamat',
        'telepon',
        'pembimbing_industri',
       
    ];

    /**
     * Siswa yang ditempatkan di perusahaan ini
     */
    public function siswa()
    {
        return $this->hasMany(User::class, 'perusahaan_id')
                    ->where('role', 'siswa_pkl');
    }
}