<?php

namespace Database\Seeders;

use App\Models\PeriodePkl;
use Illuminate\Database\Seeder;

class PeriodePklSeeder extends Seeder
{
    public function run(): void
    {
        PeriodePkl::firstOrCreate(
            ['nama' => 'PKL Gelombang 1'],
            [
                'tahun_ajaran'    => '2025/2026',
                'tanggal_mulai'   => '2026-01-06',
                'tanggal_selesai' => '2026-06-30',
                'is_active'       => true,
                'keterangan'      => 'Periode PKL default',
            ]
        );
    }
}