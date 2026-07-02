<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informasis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('konten');
            $table->string('file')->nullable();      // path lampiran (WAJIB ada — dipakai controller)
            $table->integer('urutan')->default(0);   // untuk pengurutan tampilan
            $table->timestamps();

            // DIHAPUS (tidak dipakai lagi): kategori
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informasis');
    }
};