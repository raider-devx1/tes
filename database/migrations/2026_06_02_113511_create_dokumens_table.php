<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');

            // --- Periode PKL tempat data ini dibuat ---
            // Data transaksi WAJIB merekam periodenya sendiri. Kalau hanya
            // mengandalkan users.periode_id, maka saat tahun ajaran berganti
            // dan periode siswa diperbarui, seluruh riwayat lamanya ikut
            // 'berpindah' ke periode baru dan arsip angkatan jadi rusak.
            //
            // TANPA ->constrained() di sini karena tabel 'periode_pkls' dibuat
            // SETELAH tabel ini (urutan timestamp) - pola yang sama dipakai
            // users.periode_id. Foreign key-nya dipasang belakangan pada
            // migrasi 2026_07_24_060000. Diisi otomatis oleh trait MilikPeriodePkl.
            $table->foreignId('periode_id')->nullable()->index();
            $table->string('laporan_akhir')->nullable(); // Path PDF laporan
            $table->string('surat_tugas')->nullable();
            $table->string('surat_penerimaan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dokumens');
    }
};