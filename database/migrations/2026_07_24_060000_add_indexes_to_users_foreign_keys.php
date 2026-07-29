<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Index & Foreign Key yang tidak bisa dipasang di migrasi pembuatan tabel
|--------------------------------------------------------------------------
| Tabel `perusahaans` (2026_06_02) dan `periode_pkls` (2026_06_20) dibuat
| SETELAH tabel `users` dan setelah semua tabel transaksi PKL (2026_06_02).
| Karena itu kolom `perusahaan_id` dan `periode_id` di tabel-tabel tersebut
| terpaksa dibuat tanpa ->constrained().
|
| Migrasi ini berjalan paling akhir, saat semua tabel tujuan sudah ada,
| sehingga di sinilah tempat yang tepat untuk:
|
| 1. Menambahkan index pada users.perusahaan_id & users.periode_id.
|    Tanpa index, setiap filter siswa per perusahaan/periode melakukan
|    full table scan pada tabel users.
|
| 2. Memasang foreign key periode_id pada 6 tabel transaksi PKL.
|    Index-nya sendiri sudah dibuat di migrasi masing-masing tabel;
|    di sini hanya relasinya yang dilengkapi.
|
|    Memakai nullOnDelete(): menghapus sebuah periode TIDAK ikut menghapus
|    jurnal/absensi/nilai di dalamnya. Riwayat PKL jauh lebih berharga
|    daripada sekadar penanda periode.
*/
return new class extends Migration
{
    /** Tabel transaksi PKL yang menyimpan periode_id sendiri. */
    private const TABEL_TRANSAKSI = [
        'jurnals',
        'absensis',
        'dokumens',
        'nilais',
        'catatan_kegiatans',
        'observasis',
    ];

    public function up(): void
    {
        // --- 1. Index pada foreign key di tabel users ---
        Schema::table('users', function (Blueprint $table) {
            // Gunakan nama index eksplisit agar mudah di-drop kembali.
            $table->index('perusahaan_id', 'users_perusahaan_id_index');
            $table->index('periode_id', 'users_periode_id_index');
        });

        // --- 2. Foreign key periode_id pada tabel transaksi PKL ---
        if (! Schema::hasTable('periode_pkls')) {
            return;
        }

        foreach (self::TABEL_TRANSAKSI as $tabel) {
            if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, 'periode_id')) {
                continue;
            }

            try {
                Schema::table($tabel, function (Blueprint $table) {
                    $table->foreign('periode_id')
                          ->references('id')
                          ->on('periode_pkls')
                          ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Foreign key sudah ada, atau engine tabel tidak mendukungnya
                // (mis. MyISAM di sebagian shared hosting). Bukan kondisi fatal:
                // kolom + index tetap berfungsi dan aplikasi tetap jalan normal.
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABEL_TRANSAKSI as $tabel) {
            if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, 'periode_id')) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table) {
                try {
                    $table->dropForeign(['periode_id']);
                } catch (\Throwable $e) {
                    // Abaikan bila foreign key memang tidak pernah terbentuk.
                }
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_perusahaan_id_index');
            $table->dropIndex('users_periode_id_index');
        });
    }
};
