<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tanda tangan digital GURU PEMBIMBING pada validasi absensi.
 *
 * Alur baru: tombol "Valid" di halaman monitoring absensi guru TIDAK bisa
 * dikirim sebelum guru membubuhkan tanda tangan pada kanvas. Hasil goresan
 * disimpan sebagai berkas PNG kecil di storage/app/public/ttd/absensi/guru
 * lalu ikut tercetak pada kolom "Validasi" di PDF rekap absensi.
 *
 * Pola kolom sengaja disamakan dengan migrasi paraf yang sudah ada
 * (2026_08_05_090000 dan 2026_08_05_140000) supaya konsisten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            // Path relatif di disk 'public', mis. ttd/absensi/guru/xxxx.png
            if (! Schema::hasColumn('absensis', 'ttd_guru')) {
                if (Schema::hasColumn('absensis', 'foto_bukti')) {
                    $table->string('ttd_guru')->nullable()->after('foto_bukti');
                } else {
                    $table->string('ttd_guru')->nullable();
                }
            }

            // Nama guru yang membubuhkan paraf (disimpan agar tetap benar
            // walau nama akun berubah / guru diganti di kemudian hari).
            if (! Schema::hasColumn('absensis', 'ttd_guru_nama')) {
                $table->string('ttd_guru_nama', 150)->nullable()->after('ttd_guru');
            }

            if (! Schema::hasColumn('absensis', 'ttd_guru_signed_at')) {
                $table->timestamp('ttd_guru_signed_at')->nullable()->after('ttd_guru_nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            foreach (['ttd_guru', 'ttd_guru_nama', 'ttd_guru_signed_at'] as $kolom) {
                if (Schema::hasColumn('absensis', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
