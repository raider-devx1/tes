<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PENOLAKAN FOTO ABSENSI OLEH GURU PEMBIMBING.
 *
 * Alur baru:
 *  - Guru menekan "Tolak" -> muncul pop-up berisi CATATAN penolakan + tombol
 *    Konfirmasi / Batal.
 *  - Absensi yang ditolak TIDAK dihapus dan TIDAK diubah: tanggal, status,
 *    jam masuk, dan jam pulang tetap seperti semula. Siswa hanya diminta
 *    MENGGANTI FOTO-nya saja.
 *  - Batas waktu mengganti foto: sampai jendela jam pulang hari itu berakhir
 *    (lihat Absensi::batasGantiFoto()).
 *  - Selama foto belum diganti, siswa TIDAK bisa melakukan absen pulang.
 *  - Selama foto sudah diganti sebelum batas waktu, absensi TIDAK menjadi Alpha.
 *
 * Pola penulisan kolom sengaja memakai Schema::hasColumn() seperti migrasi
 * lain di proyek ini supaya aman dijalankan ulang di hosting (Niagahoster).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            // Penanda utama: foto absensi ditolak & wajib diganti siswa.
            if (! Schema::hasColumn('absensis', 'foto_ditolak')) {
                if (Schema::hasColumn('absensis', 'foto_bukti')) {
                    $table->boolean('foto_ditolak')->default(false)->after('foto_bukti');
                } else {
                    $table->boolean('foto_ditolak')->default(false);
                }
            }

            // Alasan penolakan yang diketik guru pada pop-up konfirmasi.
            if (! Schema::hasColumn('absensis', 'catatan_penolakan')) {
                $table->text('catatan_penolakan')->nullable()->after('foto_ditolak');
            }

            // Kapan ditolak -> dipakai menghitung batas waktu ganti foto.
            if (! Schema::hasColumn('absensis', 'foto_ditolak_at')) {
                $table->timestamp('foto_ditolak_at')->nullable()->after('catatan_penolakan');
            }

            // Guru yang menolak. Sengaja TANPA foreign key constraint agar
            // migrasi tetap aman dijalankan di database hosting yang sudah
            // berisi data lama.
            if (! Schema::hasColumn('absensis', 'foto_ditolak_by')) {
                $table->unsignedBigInteger('foto_ditolak_by')->nullable()->after('foto_ditolak_at');
            }

            // Kapan siswa berhasil mengganti fotonya (untuk jejak audit).
            if (! Schema::hasColumn('absensis', 'foto_diganti_at')) {
                $table->timestamp('foto_diganti_at')->nullable()->after('foto_ditolak_by');
            }
        });

        // Index terpisah: dipakai notifikasi dashboard siswa & penandaan Alpha
        // otomatis, yaitu query "absensi milik siswa X yang fotonya ditolak".
        Schema::table('absensis', function (Blueprint $table) {
            $namaIndex = 'absensis_siswa_foto_ditolak_index';

            if (! $this->indexAda('absensis', $namaIndex)) {
                $table->index(['siswa_id', 'foto_ditolak'], $namaIndex);
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $namaIndex = 'absensis_siswa_foto_ditolak_index';

            if ($this->indexAda('absensis', $namaIndex)) {
                $table->dropIndex($namaIndex);
            }

            foreach ([
                'foto_ditolak',
                'catatan_penolakan',
                'foto_ditolak_at',
                'foto_ditolak_by',
                'foto_diganti_at',
            ] as $kolom) {
                if (Schema::hasColumn('absensis', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }

    /** Cek keberadaan index dengan cara yang aman untuk MySQL/MariaDB & SQLite. */
    private function indexAda(string $tabel, string $index): bool
    {
        try {
            return Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->introspectTable($tabel)
                ->hasIndex($index);
        } catch (\Throwable $e) {
            // Doctrine DBAL tidak tersedia (Laravel 11+) -> pakai query bawaan.
            try {
                $hasil = Schema::getConnection()->select(
                    "SHOW INDEX FROM `{$tabel}` WHERE Key_name = ?",
                    [$index]
                );

                return ! empty($hasil);
            } catch (\Throwable $e2) {
                return false;
            }
        }
    }
};
