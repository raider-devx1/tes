<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            // Menghubungkan jurnal dengan siswa yang mengisi [cite: 26]
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            
            // Kolom-kolom isi formulir berdasarkan dokumen HKI [cite: 30, 31, 32]
            $table->date('hari_tanggal'); 
           
            
            // Kolom dari sisi instruktur industri [cite: 33, 34]
            $table->text('catatan_instruktur')->nullable(); 
            $table->enum('status_persetujuan', ['pending', 'disetujui', 'revisi'])->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users'); 

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jurnals');
    }
};