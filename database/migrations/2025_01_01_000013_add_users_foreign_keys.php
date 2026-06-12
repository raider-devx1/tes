<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('perusahaan_id')->references('id')->on('perusahaans')->nullOnDelete();
            $table->foreign('instruktur_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('guru_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->dropForeign(['instruktur_id']);
            $table->dropForeign(['guru_id']);
        });
    }
};