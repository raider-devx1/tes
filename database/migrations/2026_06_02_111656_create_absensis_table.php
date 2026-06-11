<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('instruktur_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpha'])->default('Hadir');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensis');
    }
};