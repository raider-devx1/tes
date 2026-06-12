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
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('instruktur_id')->nullable()->constrained('users')->nullOnDelete();

            // 4 komponen penilaian, skala 1-5 (sesuai dokumen rancangan)
            $table->unsignedTinyInteger('soft_skill');
            $table->unsignedTinyInteger('hard_skill');
            $table->unsignedTinyInteger('pengembangan_hard_skill');
            $table->unsignedTinyInteger('kewirausahaan');
            $table->decimal('rata_rata', 4, 2)->default(0);
            $table->text('catatan_rekomendasi')->nullable();
            $table->timestamps();

            // satu siswa satu set nilai
            $table->unique('siswa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
