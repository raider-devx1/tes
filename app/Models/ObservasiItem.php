<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservasiItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'observasi_id',
        'permasalahan',
        'solusi',
    ];

    public function observasi()
    {
        return $this->belongsTo(Observasi::class, 'observasi_id');
    }
}