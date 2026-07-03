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
            $table->date('hari_tanggal');                 // Hari / tanggal monitoring
            $table->string('pekerjaan_projek')->nullable(); // Header PDF
            $table->boolean('is_approved')->default(false); // Persetujuan Instruktur Industri
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observasis');
    }
};