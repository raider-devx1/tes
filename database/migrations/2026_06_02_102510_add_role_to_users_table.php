<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan 4 role sesuai kebutuhan sistem [cite: 3, 8, 13, 18]
            $table->enum('role', ['admin', 'guru_pembimbing', 'siswa_pkl', 'instruktur_industri'])
                  ->default('siswa_pkl')
                  ->after('password');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};