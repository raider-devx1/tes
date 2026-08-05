<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PARAF DIGITAL PADA LEMBAR OBSERVASI (halaman Guru Pembimbing).
 *
 * Alur baru saat guru mengajukan lembar observasi:
 *   1. foto_dokumentasi  -> BUKTI FOTO OBSERVASI (tetap diunggah, wajib)
 *   2. ttd_guru          -> paraf digital guru pembimbing (kanvas di web/HP)
 *   3. ttd_instruktur    -> paraf digital instruktur, dibubuhkan di bawah paraf guru
 *
 * Guru TIDAK lagi memotret lembar observasi yang sudah diparaf.
 * Kolom `foto_lembar_observasi` SENGAJA dipertahankan (tidak dihapus) supaya
 * data lama yang masih memakai foto lembar berparaf tetap bisa dibuka.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observasis', function (Blueprint $table) {
            if (! Schema::hasColumn('observasis', 'ttd_guru')) {
                $table->string('ttd_guru')->nullable()->after('foto_lembar_observasi');
            }
            if (! Schema::hasColumn('observasis', 'ttd_guru_nama')) {
                $table->string('ttd_guru_nama', 150)->nullable()->after('ttd_guru');
            }
            if (! Schema::hasColumn('observasis', 'ttd_guru_signed_at')) {
                $table->timestamp('ttd_guru_signed_at')->nullable()->after('ttd_guru_nama');
            }
            if (! Schema::hasColumn('observasis', 'ttd_instruktur')) {
                $table->string('ttd_instruktur')->nullable()->after('ttd_guru_signed_at');
            }
            if (! Schema::hasColumn('observasis', 'ttd_instruktur_nama')) {
                $table->string('ttd_instruktur_nama', 150)->nullable()->after('ttd_instruktur');
            }
            if (! Schema::hasColumn('observasis', 'ttd_instruktur_signed_at')) {
                $table->timestamp('ttd_instruktur_signed_at')->nullable()->after('ttd_instruktur_nama');
            }
        });
    }

    public function down(): void
    {
        $kolom = [
            'ttd_guru',
            'ttd_guru_nama',
            'ttd_guru_signed_at',
            'ttd_instruktur',
            'ttd_instruktur_nama',
            'ttd_instruktur_signed_at',
        ];

        Schema::table('observasis', function (Blueprint $table) use ($kolom) {
            foreach ($kolom as $nama) {
                if (Schema::hasColumn('observasis', $nama)) {
                    $table->dropColumn($nama);
                }
            }
        });
    }
};
