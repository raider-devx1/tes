<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // --- Akun & login ---
            $table->string('name');
            $table->string('email')->nullable();              // tidak wajib & tidak unik
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // --- Role sistem ---
           $table->enum('role', ['admin', 'guru_pembimbing', 'siswa_pkl'])
                  ->default('siswa_pkl');

            // --- Identitas umum ---
            $table->string('no_hp', 20)->nullable();
            $table->string('foto')->nullable();

            // --- Khusus Siswa ---
            $table->string('nisn', 20)->nullable()->unique();  // identitas login siswa
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->enum('status_pkl', ['belum', 'aktif', 'selesai'])->default('belum');
            $table->string('kelas')->nullable();
            $table->string('jurusan')->nullable();

            // --- Jam kerja industri siswa (absensi) ---
            // Jam khusus industri yang berlaku setelah disetujui guru pembimbing.
            $table->time('jam_masuk_industri')->nullable();
            $table->time('jam_pulang_industri')->nullable();
            // Usulan jam dari siswa (bila jam admin tak sesuai template industrinya).
            $table->time('jam_masuk_usulan')->nullable();
            $table->time('jam_pulang_usulan')->nullable();
            $table->enum('status_jam_usulan', ['none', 'diajukan', 'disetujui'])->default('none');
            $table->string('catatan_jam_usulan')->nullable();

            // --- Pembukaan absensi manual (per-siswa) ---
            // true = absensi siswa ini dibuka bebas waktu (oleh admin/guru), mengabaikan jadwal jam.
            $table->boolean('absensi_dibuka')->default(false);

            // --- Khusus Guru Pembimbing ---
            $table->string('nip', 30)->nullable()->unique();   // identitas login guru
            $table->boolean('is_wakasek')->default(false);     // penanda Wakasek: boleh memvalidasi lembar observasi guru lain & lembarnya sendiri
            $table->boolean('is_admin')->default(false);       // penanda: guru pembimbing ini juga boleh mengakses panel admin

            // --- Khusus Instruktur Industri ---
            $table->string('jabatan')->nullable();

            // --- Relasi pemetaan (self-reference: guru & instruktur) ---
           
            $table->foreignId('guru_id')->nullable()->constrained('users')->nullOnDelete();

            // Catatan: perusahaan_id & periode_id TANPA ->constrained() di sini,
            // karena tabel 'perusahaans' & 'periode_pkls' dibuat SETELAH tabel users
            // (urutan timestamp). Relasi tetap jalan lewat model (belongsTo).
            $table->foreignId('perusahaan_id')->nullable();
            $table->foreignId('periode_id')->nullable();

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};