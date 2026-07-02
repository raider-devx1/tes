<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informasi extends Model
{
    use HasFactory;

    // Penting: Laravel salah menebak bentuk jamak "Informasi",
    // jadi nama tabel kita set manual.
    protected $table = 'informasis';

    protected $fillable = [
        'judul',
        'konten',
        'urutan',
        'file',
    ];
}