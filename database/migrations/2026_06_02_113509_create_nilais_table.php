<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Siswa yang dinilai
            $table->foreignId('instruktur_id')->constrained('users')->onDelete('cascade'); // Instruktur penilai
            $table->integer('soft_skill'); // Skala 1-5
            $table->integer('hard_skill'); // Skala 1-5
            $table->integer('pengembangan_hard_skill'); // Skala 1-5
            $table->integer('kewirausahaan'); // Skala 1-5
            $table->decimal('rata_rata', 3, 2)->nullable(); // Nilai Akhir
            $table->text('catatan_rekomendasi')->nullable(); // Evaluasi tambahan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};