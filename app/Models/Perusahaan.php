<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perusahaan extends Model
{
    protected $fillable = ['nama', 'bidang', 'alamat', 'telepon', 'pembimbing_industri'];

    public function siswas(): HasMany
    {
        return $this->hasMany(User::class, 'perusahaan_id')->where('role', User::ROLE_SISWA);
    }
}
