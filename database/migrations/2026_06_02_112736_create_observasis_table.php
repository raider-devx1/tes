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