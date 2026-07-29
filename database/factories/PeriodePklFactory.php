<?php

namespace Database\Factories;

use App\Models\PeriodePkl;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodePkl>
 *
 * Perhatian: model PeriodePkl otomatis MENONAKTIFKAN periode lain begitu
 * sebuah periode disimpan dengan is_active = true. Jadi jangan berasumsi
 * dua periode bisa aktif bersamaan di dalam tes.
 */
class PeriodePklFactory extends Factory
{
    protected $model = PeriodePkl::class;

    public function definition(): array
    {
        $mulai = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'nama'            => 'PKL ' . fake()->unique()->numerify('Gelombang ##'),
            'tahun_ajaran'    => '2025/2026',
            'tanggal_mulai'   => $mulai,
            'tanggal_selesai' => (clone $mulai)->modify('+6 months'),
            'is_active'       => false,
            'keterangan'      => null,
        ];
    }

    /** Periode yang sedang berjalan. */
    public function aktif(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }
}
