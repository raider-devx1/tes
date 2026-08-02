<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah pilihan identitas siswa yang dipakai pada hasil CETAK penilaian guru.
 *
 * - label_identitas : 'nisn' (bawaan) atau 'nis'. Menentukan kata yang tercetak.
 * - nomor_identitas : nomor yang diketik bebas oleh guru. Bila kosong, cetakan
 *                     otomatis memakai NISN yang tersimpan pada data siswa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            if (! Schema::hasColumn('nilais', 'label_identitas')) {
                $table->string('label_identitas', 10)->nullable()->after('guru_id');
            }

            if (! Schema::hasColumn('nilais', 'nomor_identitas')) {
                $table->string('nomor_identitas', 30)->nullable()->after('label_identitas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            foreach (['nomor_identitas', 'label_identitas'] as $kolom) {
                if (Schema::hasColumn('nilais', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
