<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->date('hari_tanggal');
            $table->string('unit_kerja');
            $table->text('deskripsi_pekerjaan');
            $table->string('dokumentasi')->nullable();       // path foto
            $table->text('catatan_instruktur')->nullable();
            $table->enum('status_persetujuan', ['pending', 'disetujui', 'revisi'])->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'status_persetujuan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
