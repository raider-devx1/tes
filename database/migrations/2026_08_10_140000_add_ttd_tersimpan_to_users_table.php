<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TANDA TANGAN TERSIMPAN MILIK GURU PEMBIMBING
 * -------------------------------------------------------------------------
 * Guru mengunggah tanda tangannya SEKALI dari halaman Monitoring Absensi,
 * lalu tanda tangan itu bisa dipakai ulang setiap kali memberi nilai tanpa
 * perlu mengunggah berkas yang sama berulang-ulang.
 *
 * Catatan penting:
 * Kolom ini BUKAN pengganti nilais.ttd_pembimbing. Saat guru memilih
 * "pakai tanda tangan tersimpan", berkasnya DISALIN ke penilaian tersebut.
 * Jadi kalau nanti guru mengganti tanda tangan tersimpannya, penilaian yang
 * sudah terlanjur disimpan tidak ikut berubah dan arsip cetakan tetap utuh.
 *
 * Semua perubahan dijaga Schema::hasColumn supaya aman dijalankan berulang
 * pada basis data yang sudah pernah dimigrasikan sebagian.
 */
return new class extends Migration
{
    /** Kolom baru pada tabel users, berurutan. */
    private array $kolom = [
        'ttd_tersimpan',
        'ttd_tersimpan_at',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Path berkas tanda tangan pada disk 'public'.
            if (! Schema::hasColumn('users', 'ttd_tersimpan')) {
                $table->string('ttd_tersimpan')->nullable()->after('foto');
            }

            // Kapan terakhir kali guru memperbarui tanda tangannya.
            if (! Schema::hasColumn('users', 'ttd_tersimpan_at')) {
                $table->timestamp('ttd_tersimpan_at')->nullable()->after('ttd_tersimpan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (array_reverse($this->kolom) as $kolom) {
                if (Schema::hasColumn('users', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
