<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // siswa yang dinilai

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

            // Penilai
           
            $table->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();

            // Komponen instruktur (skala 1-5) — kolom lama, disimpan untuk kompatibilitas data lama
            $table->integer('soft_skill')->nullable();
            $table->integer('hard_skill')->nullable();
            $table->integer('pengembangan_hard_skill')->nullable();
            $table->integer('kewirausahaan')->nullable();
            $table->decimal('rata_rata', 3, 2)->nullable();
            $table->text('catatan_rekomendasi')->nullable();

            // --- Komponen guru (skala 0-100) beserta Deskripsi ---
            $table->integer('skor_soft_skill')->nullable();
            $table->text('deskripsi_soft_skill')->nullable();

            $table->integer('skor_hard_skill')->nullable();
            $table->text('deskripsi_hard_skill')->nullable();

            $table->integer('skor_pengembangan')->nullable();
            $table->text('deskripsi_pengembangan')->nullable();

            $table->integer('skor_kewirausahaan')->nullable();
            $table->text('deskripsi_kewirausahaan')->nullable();

            $table->integer('skor_laporan')->nullable();
            $table->text('deskripsi_laporan')->nullable();

            $table->integer('skor_presentasi')->nullable();
            $table->text('deskripsi_presentasi')->nullable();

            $table->text('catatan_guru')->nullable();

            // Foto lembar penilaian instruktur (diunggah guru)
            $table->string('foto_lembar_instruktur')->nullable();

            // Backup kompatibilitas nilai lama
            $table->decimal('nilai_guru', 5, 2)->nullable();
            $table->decimal('nilai_laporan', 5, 2)->nullable();

            // Rekap akhir (0-100)
            $table->decimal('nilai_akhir', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};