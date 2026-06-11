<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kelas')->nullable(); // Contoh: XI TKJ 1
            $table->string('jurusan')->nullable(); // Contoh: Teknik Komputer dan Jaringan
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaans')->onDelete('set null');
            $table->foreignId('instruktur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('guru_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->dropForeign(['instruktur_id']);
            $table->dropForeign(['guru_id']);
            $table->dropColumn(['kelas', 'jurusan', 'perusahaan_id', 'instruktur_id', 'guru_id']);
        });
    }
};