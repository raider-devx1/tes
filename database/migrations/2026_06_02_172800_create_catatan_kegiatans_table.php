<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke siswa

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
            $table->string('nama_pekerjaan');
            $table->text('perencanaan_kegiatan');
            $table->text('pelaksanaan_kegiatan');
            $table->text('catatan_instruktur')->nullable(); // Diisi oleh instruktur
            $table->boolean('is_approved')->default(false); // Status persetujuan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_kegiatans');
    }
};