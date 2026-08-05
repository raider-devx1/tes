<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alur baru: siswa TIDAK lagi mengunggah foto lembar berparaf.
 * Instruktur menandatangani LANGSUNG di web (kanvas tanda tangan digital),
 * hasilnya disimpan sebagai berkas PNG kecil di storage/app/public/ttd/...
 *
 * Kolom 'foto_bukti' SENGAJA tidak dihapus supaya data pengajuan lama
 * (yang masih berupa foto) tetap bisa dibuka. Kolom itu cukup dibiarkan null
 * untuk pengajuan baru.
 */
return new class extends Migration
{
    /** Tabel yang memakai paraf/tanda tangan digital instruktur. */
    private array $tabel = ['jurnals', 'catatan_kegiatans'];

    public function up(): void
    {
        foreach ($this->tabel as $nama) {
            Schema::table($nama, function (Blueprint $table) use ($nama) {
                if (! Schema::hasColumn($nama, 'ttd_instruktur')) {
                    // Path relatif di disk 'public', mis. ttd/jurnal/xxxx.png
                    $table->string('ttd_instruktur')->nullable()->after('foto_bukti');
                }
                if (! Schema::hasColumn($nama, 'ttd_instruktur_nama')) {
                    $table->string('ttd_instruktur_nama', 150)->nullable()->after('ttd_instruktur');
                }
                if (! Schema::hasColumn($nama, 'ttd_signed_at')) {
                    $table->timestamp('ttd_signed_at')->nullable()->after('ttd_instruktur_nama');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabel as $nama) {
            Schema::table($nama, function (Blueprint $table) use ($nama) {
                foreach (['ttd_instruktur', 'ttd_instruktur_nama', 'ttd_signed_at'] as $kolom) {
                    if (Schema::hasColumn($nama, $kolom)) {
                        $table->dropColumn($kolom);
                    }
                }
            });
        }
    }
};
