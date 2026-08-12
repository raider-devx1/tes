<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * USULAN HARI KERJA DARI SISWA
 * -------------------------------------------------------------------------
 * Siswa mengajukan HARI KERJA (hari awal s.d. hari akhir) bersamaan dengan
 * usulan JAM kerja industri. Nilai usulan ditampung sementara pada kolom
 * `hari_kerja_usulan`, lalu dipindahkan ke kolom `hari_kerja` setelah guru
 * pembimbing (atau admin) menyetujui pengajuan tersebut.
 *
 * Format nilainya sama dengan kolom `hari_kerja`, yaitu
 * "{hari_awal}_{hari_akhir}" -- mis. senin_jumat, selasa_sabtu, sabtu_rabu.
 * Bernilai null berarti siswa tidak sedang mengajukan hari kerja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'hari_kerja_usulan')) {
                $table->string('hari_kerja_usulan', 20)
                    ->nullable()
                    ->after('hari_kerja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'hari_kerja_usulan')) {
                $table->dropColumn('hari_kerja_usulan');
            }
        });
    }
};
