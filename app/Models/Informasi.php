<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Informasi extends Model
{
    protected $table = 'informasis';

    protected $fillable = ['judul', 'kategori', 'konten', 'urutan'];

    public const KATEGORI = [
        'umum'                => 'Informasi Umum',
        'panduan_laporan'     => 'Panduan Penyusunan Laporan',
        'panduan_presentasi'  => 'Panduan Penyusunan Presentasi',
    ];
}
