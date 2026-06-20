<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_pkls', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                 // contoh: "PKL Gelombang 1"
            $table->string('tahun_ajaran');          // contoh: "2025/2026"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_pkls');
    }
};