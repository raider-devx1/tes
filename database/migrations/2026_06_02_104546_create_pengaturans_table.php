<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique(); // Contoh: 'tahun_pelajaran', 'nama_sekolah'
            $table->string('nilai'); // Contoh: '2025/2026', 'UPTD SMKN 1 MAJENE'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengaturans');
    }
};