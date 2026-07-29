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
| Pemangkasan Log Aktivitas
|--------------------------------------------------------------------------
|
| Tabel activity_logs bertambah terus setiap hari. Tanpa pemangkasan, dalam
| beberapa tahun ukurannya bisa jauh melebihi data PKL itu sendiri dan
| membuat proses backup menjadi lambat.
|
| Ubah angka di bawah bila ingin masa simpan lebih panjang. Untuk satu
| siklus PKL penuh, 180 hari adalah pilihan yang aman.
|
| CATATAN PENTING -- JANGAN DIUBAH MENJADI const:
| Angka ini sengaja ditulis sebagai variabel DI DALAM closure, bukan sebagai
| "const" di tingkat file. Berkas ini dibaca ulang setiap kali aplikasi
| dinyalakan. Dalam sekali menjalankan "php artisan test", aplikasi
| dinyalakan ratusan kali di dalam SATU proses PHP yang sama. Sebuah const
| hanya boleh didefinisikan sekali per proses, sehingga penyalaan kedua akan
| gagal dengan pesan "Constant ... already defined". Variabel biasa tidak
| memiliki batasan itu.
|
*/

Schedule::call(function () {
    $masaSimpanLogHari = 30;

    DB::table('activity_logs')
        ->where('created_at', '<', now()->subDays($masaSimpanLogHari))
        ->delete();
})->dailyAt('01:00')->name('pangkas-log-aktivitas');
