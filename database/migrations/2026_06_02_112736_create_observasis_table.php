<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Siswa yang diobservasi

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
            $table->foreignId('guru_id')->constrained('users')->onDelete('cascade'); // Guru pembimbing
            $table->date('hari_tanggal');                     // Hari / tanggal monitoring
            $table->string('pekerjaan_projek')->nullable();   // Header PDF

            // Status lembar observasi: draft -> diajukan (menunggu divalidasi Wakasek) -> tervalidasi
            // Guru pembimbing kini hanya "mengajukan" (seperti siswa). Validasi dilakukan oleh Wakasek;
            // Wakasek boleh memvalidasi lembar observasinya sendiri secara langsung.
            $table->enum('status', ['draft', 'diajukan', 'tervalidasi'])->default('draft');

            // Waktu guru mengajukan lembar observasi untuk divalidasi Wakasek
            $table->timestamp('diajukan_at')->nullable();

            // Foto dokumentasi kegiatan/kunjungan (diunggah saat validasi, jadi nullable)
            $table->string('foto_dokumentasi')->nullable();

            // Foto lembar observasi fisik yang sudah diparaf instruktur & guru pembimbing
            $table->string('foto_lembar_observasi')->nullable();

            // Guru pembimbing yang melakukan validasi
            $table->foreignId('validated_by_guru_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            // Waktu validasi
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observasis');
    }
};