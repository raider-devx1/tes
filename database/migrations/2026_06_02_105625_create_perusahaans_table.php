<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perusahaans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->text('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('pembimbing_industri')->nullable();
            $table->timestamps();

            // DIHAPUS (tidak dipakai): bidang_usaha, email, kuota
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perusahaans');
    }
};