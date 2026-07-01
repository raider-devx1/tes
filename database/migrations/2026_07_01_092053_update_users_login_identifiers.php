<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Email tidak lagi wajib & tidak lagi unik (nama & email boleh sama / kosong)
            $table->dropUnique('users_email_unique');
            $table->string('email')->nullable()->change();

            // NISN (login siswa) & NIP (login guru) menjadi identitas unik
            $table->unique('nisn', 'users_nisn_unique');
            $table->unique('nip', 'users_nip_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_nisn_unique');
            $table->dropUnique('users_nip_unique');

            $table->string('email')->nullable(false)->change();
            $table->unique('email', 'users_email_unique');
        });
    }
};