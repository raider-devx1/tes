<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu "Informasi Umum / Panduan PKL" yang dikelola Admin
 * (Latar Belakang, Tujuan, Manfaat, Panduan Laporan, Panduan Presentasi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informasis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori')->default('umum'); // umum|panduan_laporan|panduan_presentasi
            $table->longText('konten');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informasis');
    }
};
