<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Pembersihan Otomatis Tabel activity_logs
|--------------------------------------------------------------------------
| Menghapus data log yang lebih tua dari 7 hari, dijalankan setiap hari
| pukul 01:00 (saat traffic sepi). Karena kolom `created_at` sudah di-index
| di migrasi, proses ini sangat ringan untuk database shared hosting.
|
| Ganti subDays(7) menjadi subDays(15) bila ingin menyimpan riwayat 15 hari.
*/
Schedule::call(function () {
    DB::table('activity_logs')
        ->where('created_at', '<', now()->subDays(7))
        ->delete();
})
    ->dailyAt('01:00')          // setiap hari jam 1 malam
    ->name('bersihkan-activity-logs')
    ->withoutOverlapping();     // cegah tumpang tindih jika proses sebelumnya belum selesai
