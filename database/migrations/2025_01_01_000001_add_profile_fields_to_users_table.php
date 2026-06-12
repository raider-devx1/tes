<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan field profil & pemetaan PKL ke tabel users.
 * Satu tabel users dipakai untuk semua peran (admin, guru_pembimbing,
 * siswa_pkl, instruktur_industri) agar tidak ada duplikasi tabel akun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('siswa_pkl')->after('email');
            $table->string('nis')->nullable()->after('role');        // No induk siswa
            $table->string('telepon')->nullable()->after('nis');
            $table->string('kelas')->nullable()->after('telepon');
            $table->string('jurusan')->nullable()->after('kelas');

           // Pemetaan siswa -> perusahaan / instruktur / guru
$table->foreignId('perusahaan_id')->nullable()->after('jurusan');
$table->foreignId('instruktur_id')->nullable()->after('perusahaan_id');
$table->foreignId('guru_id')->nullable()->after('instruktur_id');

            $table->index('role');
        });
    }

   public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['role', 'nis', 'telepon', 'kelas', 'jurusan', 'perusahaan_id', 'instruktur_id', 'guru_id']);
    });
}
};
