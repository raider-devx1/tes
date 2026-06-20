<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            // Instruktur boleh kosong dulu (mis. guru mengisi nilai lebih dahulu)
            $table->foreignId('instruktur_id')->nullable()->change();

            // Guru pembimbing yang memberi nilai
            $table->foreignId('guru_id')->nullable()->after('instruktur_id')
                  ->constrained('users')->nullOnDelete();

            // Nilai guru & nilai laporan (skala 0–100)
            $table->decimal('nilai_guru', 5, 2)->nullable()->after('kewirausahaan');
            $table->decimal('nilai_laporan', 5, 2)->nullable()->after('nilai_guru');

            // Catatan guru & nilai akhir rekap (0–100)
            $table->text('catatan_guru')->nullable()->after('catatan_rekomendasi');
            $table->decimal('nilai_akhir', 5, 2)->nullable()->after('catatan_guru');
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropColumn(['guru_id', 'nilai_guru', 'nilai_laporan', 'catatan_guru', 'nilai_akhir']);
        });
    }
};