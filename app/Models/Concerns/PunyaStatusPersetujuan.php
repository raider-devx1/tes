<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait reusable untuk semua model yang punya alur persetujuan instruktur.
 * Menghindari pengulangan scope & helper di Jurnal, CatatanKegiatan, Observasi.
 */
trait PunyaStatusPersetujuan
{
    public function scopePending(Builder $q): Builder
    {
        return $q->where('status_persetujuan', 'pending');
    }

    public function scopeDisetujui(Builder $q): Builder
    {
        return $q->where('status_persetujuan', 'disetujui');
    }

    public function disetujuiOleh()
    {
        return $this->belongsTo(\App\Models\User::class, 'disetujui_oleh');
    }

    public function getBadgeWarnaAttribute(): string
    {
        return match ($this->status_persetujuan) {
            'disetujui' => 'green',
            'revisi'    => 'red',
            default     => 'yellow',
        };
    }
}
