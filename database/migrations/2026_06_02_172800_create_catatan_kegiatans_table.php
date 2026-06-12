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