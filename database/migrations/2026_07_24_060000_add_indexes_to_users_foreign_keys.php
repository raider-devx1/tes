<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Tambah Index pada Foreign Key di Tabel users
|--------------------------------------------------------------------------
| Kolom `perusahaan_id` & `periode_id` dibuat TANPA ->constrained() / index
| di migrasi awal (karena urutan pembuatan tabel). Akibatnya setiap query
| yang memfilter siswa berdasarkan perusahaan atau periode PKL melakukan
| full table scan pada tabel users.
|
| Migrasi ini menambahkan index biasa (bukan foreign key) agar query
| filter/join berdasarkan kedua kolom tersebut jauh lebih cepat, terutama
| saat data siswa sudah banyak dan diakses banyak user sekaligus.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Gunakan nama index eksplisit agar mudah di-drop kembali.
            $table->index('perusahaan_id', 'users_perusahaan_id_index');
            $table->index('periode_id', 'users_periode_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_perusahaan_id_index');
            $table->dropIndex('users_periode_id_index');
        });
    }
};
