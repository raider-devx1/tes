<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Jadwal hari kerja absensi (per-siswa)
|--------------------------------------------------------------------------
| Jadwal DEFAULT seluruh sekolah disimpan pada tabel `pengaturans`
| (kunci: absensi_hari_kerja) dengan nilai 'senin_jumat' atau 'senin_sabtu'.
|
| Kolom ini adalah PENGECUALIAN per siswa. Sebagian siswa tetap masuk pada
| hari Sabtu walau jadwal sekolah hanya Senin-Jumat (atau sebaliknya), jadi
| jadwalnya perlu bisa diatur satu per satu lewat pencarian NISN.
|
| Nilai:
|   null          -> ikut jadwal global admin
|   'senin_jumat' -> Sabtu & Minggu BUKAN hari kerja
|   'senin_sabtu' -> hanya Minggu yang bukan hari kerja
|
| Hari yang bukan hari kerja tidak boleh diisi absensi dan TIDAK pernah
| ditandai Alpha otomatis; barisnya sengaja dibiarkan kosong.
*/
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'hari_kerja')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('hari_kerja', 20)
                  ->nullable()
                  ->after('absensi_dibuka_pulang')
                  ->comment('null = ikut jadwal global; senin_jumat | senin_sabtu');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'hari_kerja')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hari_kerja');
        });
    }
};
