<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Batas waktu pembukaan absensi manual (per-siswa)
|--------------------------------------------------------------------------
| Guru/admin dapat MEMBUKA absensi di luar jadwal jam lewat kolom
| absensi_dibuka / absensi_dibuka_masuk / absensi_dibuka_pulang.
|
| Masalahnya, flag itu menyala terus sampai ada yang menekan tombol
| "Tutup Absensi". Kalau guru lupa menutup, absensi terbuka sepanjang hari
| dan siswa bisa absen kapan saja.
|
| Kolom ini menyimpan TENGGAT pembukaan tersebut:
|
|   null                -> terbuka tanpa batas waktu (perilaku lama,
|                          harus ditutup manual)
|   waktu di masa depan -> masih terbuka, sisa waktu berjalan
|   waktu sudah lewat   -> dianggap TERTUTUP walau flag masih true,
|                          dan flag-nya dibersihkan otomatis saat halaman
|                          monitoring dimuat (tanpa perlu scheduler/cron)
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'absensi_dibuka_sampai')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('absensi_dibuka_sampai')
                  ->nullable()
                  ->after('absensi_dibuka_pulang')
                  ->comment('null = terbuka tanpa batas; selain itu absensi menutup sendiri setelah waktu ini');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'absensi_dibuka_sampai')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('absensi_dibuka_sampai');
        });
    }
};
