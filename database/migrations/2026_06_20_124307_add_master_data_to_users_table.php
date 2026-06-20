<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // --- Identitas umum ---
            $table->string('no_hp', 20)->nullable()->after('email');
            $table->string('foto')->nullable()->after('no_hp');

            // --- Khusus Siswa ---
            $table->string('nisn', 20)->nullable()->after('foto');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nisn');
            $table->enum('status_pkl', ['belum', 'aktif', 'selesai'])->default('belum')->after('jenis_kelamin');

            // --- Khusus Guru Pembimbing ---
            $table->string('nip', 30)->nullable()->after('status_pkl');

            // --- Khusus Instruktur Industri ---
            $table->string('jabatan')->nullable()->after('nip');

            // --- Relasi ke Periode PKL (untuk siswa) ---
            $table->foreignId('periode_id')->nullable()->after('guru_id')
                  ->constrained('periode_pkls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periode_id');
            $table->dropColumn([
                'no_hp', 'foto', 'nisn', 'jenis_kelamin',
                'status_pkl', 'nip', 'jabatan',
            ]);
        });
    }
};