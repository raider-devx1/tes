<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    protected $fillable = [
        'siswa_id', 'instruktur_id', 'soft_skill', 'hard_skill',
        'pengembangan_hard_skill', 'kewirausahaan', 'rata_rata', 'catatan_rekomendasi',
    ];

    protected $casts = ['rata_rata' => 'decimal:2'];

    /** Hitung & isi rata_rata otomatis dari 4 komponen. */
    public function hitungRataRata(): float
    {
        $total = $this->soft_skill + $this->hard_skill
               + $this->pengembangan_hard_skill + $this->kewirausahaan;
        return round($total / 4, 2);
    }

    public function predikat(): string
    {
        return match (true) {
            $this->rata_rata >= 4.5 => 'Sangat Baik',
            $this->rata_rata >= 3.5 => 'Baik',
            $this->rata_rata >= 2.5 => 'Cukup',
            default                 => 'Kurang',
        };
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
    public function instruktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instruktur_id');
    }
}
