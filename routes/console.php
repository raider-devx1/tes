<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Penjadwalan Pemangkasan Log Aktivitas -- SUDAH DIHAPUS
|--------------------------------------------------------------------------
|
| Dahulu berkas ini menjalankan tugas harian pukul 01.00 untuk menghapus isi
| tabel activity_logs yang lebih tua dari 30 hari.
|
| Fitur "Riwayat Aktivitas" beserta tabel activity_logs kini DIHAPUS total
| supaya tidak membebani shared hosting:
|
|   1. Tidak ada lagi INSERT tambahan setiap kali menyimpan/mengubah/menghapus
|      data, sehingga setiap aksi pengguna sedikit lebih cepat.
|   2. Tabel activity_logs yang terus membengkak sudah dihapus, sehingga kuota
|      database dan proses backup jauh lebih ringan.
|   3. Tidak perlu lagi cron job "php artisan schedule:run" hanya untuk
|      memangkas log. (Silakan tetap dipakai bila Anda menambah jadwal lain
|      di kemudian hari.)
|
| Bila suatu saat fitur ini ingin dihidupkan kembali, buat ulang tabelnya,
| daftarkan kembali middleware pencatat log di bootstrap/app.php, lalu
| tambahkan lagi Schedule::call(...) di bawah ini.
|
*/
