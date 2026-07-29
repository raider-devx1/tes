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

            // --- Periode PKL tempat data ini dibuat ---
            // Data transaksi WAJIB merekam periodenya sendiri. Kalau hanya
            // mengandalkan users.periode_id, maka saat tahun ajaran berganti
            // dan periode siswa diperbarui, seluruh riwayat lamanya ikut
            // 'berpindah' ke periode baru dan arsip angkatan jadi rusak.
            //
            // TANPA ->constrained() di sini karena tabel 'periode_pkls' dibuat
            // SETELAH tabel ini (urutan timestamp) - pola yang sama dipakai
            // users.periode_id. Foreign key-nya dipasang belakangan pada
            // migrasi 2026_07_24_060000. Diisi otomatis oleh trait MilikPeriodePkl.
            $table->foreignId('periode_id')->nullable()->index();
            
            // Kolom-kolom isi formulir berdasarkan dokumen HKI [cite: 30, 31, 32]
            $table->date('hari_tanggal'); 
           
            
            // Kolom dari sisi instruktur industri [cite: 33, 34]
            $table->text('catatan_instruktur')->nullable(); 
            $table->enum('status_persetujuan', ['pending', 'disetujui', 'revisi'])->default('pending');
            // nullOnDelete() WAJIB di sini. Tanpa itu perilaku bawaan MySQL adalah
            // RESTRICT, sehingga akun guru yang pernah menyetujui satu saja jurnal
            // TIDAK BISA dihapus - admin hanya melihat error SQL mentah.
            // Sekolah pasti mengalami pergantian guru, jadi ini pasti terjadi.
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jurnals');
    }
};