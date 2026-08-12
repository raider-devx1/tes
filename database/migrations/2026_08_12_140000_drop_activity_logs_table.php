<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Menghapus tabel activity_logs (fitur "Riwayat Aktivitas")
|--------------------------------------------------------------------------
|
| Alasan: pada shared hosting, setiap aksi simpan/ubah/hapus menulis satu
| baris ke tabel ini. Tabelnya tumbuh paling cepat di antara semua tabel,
| memakan kuota database, dan memperlambat backup. Karena data ini tidak
| dipakai untuk laporan PKL, tabelnya dihapus sekaligus isinya.
|
| Aman dijalankan berulang kali: memakai dropIfExists.
|
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('activity_logs');
    }

    /**
     * Dibuat ulang bila migrasi ini di-rollback, agar struktur database
     * tetap konsisten dengan riwayat migrasi lama.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description');
            $table->string('method', 10)->nullable();
            $table->string('route_name')->nullable();
            $table->string('url')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }
};
