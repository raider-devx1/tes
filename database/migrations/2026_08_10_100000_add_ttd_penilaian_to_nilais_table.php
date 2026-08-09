<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tanda tangan pada LEMBAR PENILAIAN PKL.
 *
 * Guru mengunggah dua foto tanda tangan saat memberi nilai:
 *  - ttd_instruktur : Pembimbing Dunia Kerja / instruktur industri (kolom kanan pada cetakan)
 *  - ttd_pembimbing : Guru Pembimbing sekolah (kolom kiri pada cetakan)
 *
 * Yang disimpan hanyalah PATH relatif di disk 'public', mis.
 * ttd/nilai/instruktur/xxxx.png - sama polanya dengan tanda tangan jurnal,
 * catatan, dan absensi yang sudah ada.
 *
 * Kolom 'foto_lembar_instruktur' SENGAJA tidak diubah. Itu foto lembar
 * penilaian utuh sebagai arsip bukti, fungsinya berbeda dengan tanda tangan
 * yang ditempel ke hasil cetak.
 */
return new class extends Migration
{
    /** Kolom baru beserta urutannya (dipakai up() maupun down()). */
    private array $kolom = [
        'ttd_instruktur',
        'ttd_instruktur_nama',
        'ttd_instruktur_at',
        'ttd_pembimbing',
        'ttd_pembimbing_nama',
        'ttd_pembimbing_at',
    ];

    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            // Ditaruh setelah foto_lembar_instruktur supaya semua berkas
            // pendukung penilaian berkumpul di satu area tabel.
            if (! Schema::hasColumn('nilais', 'ttd_instruktur')) {
                $table->string('ttd_instruktur')->nullable()->after('foto_lembar_instruktur');
            }

            if (! Schema::hasColumn('nilais', 'ttd_instruktur_nama')) {
                $table->string('ttd_instruktur_nama', 150)->nullable()->after('ttd_instruktur');
            }

            if (! Schema::hasColumn('nilais', 'ttd_instruktur_at')) {
                $table->timestamp('ttd_instruktur_at')->nullable()->after('ttd_instruktur_nama');
            }

            if (! Schema::hasColumn('nilais', 'ttd_pembimbing')) {
                $table->string('ttd_pembimbing')->nullable()->after('ttd_instruktur_at');
            }

            if (! Schema::hasColumn('nilais', 'ttd_pembimbing_nama')) {
                $table->string('ttd_pembimbing_nama', 150)->nullable()->after('ttd_pembimbing');
            }

            if (! Schema::hasColumn('nilais', 'ttd_pembimbing_at')) {
                $table->timestamp('ttd_pembimbing_at')->nullable()->after('ttd_pembimbing_nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            foreach (array_reverse($this->kolom) as $nama) {
                if (Schema::hasColumn('nilais', $nama)) {
                    $table->dropColumn($nama);
                }
            }
        });
    }
};
