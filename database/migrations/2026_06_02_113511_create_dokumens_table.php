<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->string('laporan_akhir')->nullable(); // Path PDF laporan
            $table->string('surat_tugas')->nullable();
            $table->string('surat_penerimaan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dokumens');
    }
};