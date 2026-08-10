<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| TABEL TANGGAL MERAH (HARI LIBUR)
|--------------------------------------------------------------------------
| Menyimpan daftar tanggal merah yang ditetapkan admin untuk satu tahun:
| hari libur nasional, cuti bersama, libur sekolah, dan sebagainya.
|
| Satu baris bisa mewakili SATU hari (tanggal_selesai kosong) atau SATU
| RENTANG hari (mis. cuti bersama Lebaran 20-25 Maret). Menyimpannya sebagai
| rentang jauh lebih ringkas daripada satu baris per hari, dan pembacaannya
| tetap murah karena App\Models\HariLibur menyusun peta tanggal lalu
| menyimpannya di cache.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->id();

            // Nama yang tampil, mis. "Hari Kemerdekaan RI".
            $table->string('nama', 120);

            // Hari pertama libur (wajib).
            $table->date('tanggal_mulai');

            // Hari terakhir libur. NULL = libur hanya satu hari.
            $table->date('tanggal_selesai')->nullable();

            // Nonaktif = tersimpan sebagai catatan, tapi TIDAK berlaku.
            // Berguna bila sekolah ingin menyimpan daftar tahun lalu.
            $table->boolean('aktif')->default(true);

            // Catatan bebas untuk admin (opsional).
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Pencarian selalu berdasarkan tanggal, jadi keduanya diindeks.
            $table->index('tanggal_mulai');
            $table->index('tanggal_selesai');
            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hari_liburs');
    }
};
