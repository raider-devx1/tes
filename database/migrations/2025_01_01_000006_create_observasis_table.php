<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->date('hari_tanggal');
            $table->text('permasalahan');
            $table->text('solusi');
            $table->enum('status_persetujuan', ['pending', 'disetujui', 'revisi'])->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'status_persetujuan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observasis');
    }
};
