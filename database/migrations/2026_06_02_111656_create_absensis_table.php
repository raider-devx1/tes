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
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpha'])->default('Hadir');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->timestamps();

            // --- Satu siswa, satu baris per tanggal ---
            // Siswa yang mengetuk tombol absen dua kali karena sinyal lambat bisa
            // membuat dua baris pada hari yang sama. Rekap kehadiran jadi salah dan
            // biasanya baru ketahuan saat penilaian akhir - sudah terlambat.
            //
            // Pengaman di sisi PHP (firstOrNew, Cache::lock) tidak cukup: dua request
            // yang tiba benar-benar bersamaan masih bisa lolos. Hanya database yang
            // bisa menjamin keunikan secara mutlak.
            $table->unique(['siswa_id', 'tanggal'], 'absensis_siswa_tanggal_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensis');
    }
};
