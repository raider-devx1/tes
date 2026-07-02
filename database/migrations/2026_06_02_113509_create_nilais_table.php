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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');        // siswa yang dinilai

            // Penilai
            $table->foreignId('instruktur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();

            // Komponen instruktur (skala 1-5) — nullable agar guru bisa mengisi lebih dulu
            $table->integer('soft_skill')->nullable();
            $table->integer('hard_skill')->nullable();
            $table->integer('pengembangan_hard_skill')->nullable();
            $table->integer('kewirausahaan')->nullable();
            $table->decimal('rata_rata', 3, 2)->nullable();
            $table->text('catatan_rekomendasi')->nullable();

            // Komponen guru (skala 0-100)
            $table->decimal('nilai_guru', 5, 2)->nullable();
            $table->decimal('nilai_laporan', 5, 2)->nullable();
            $table->text('catatan_guru')->nullable();

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