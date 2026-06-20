<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->string('bidang_usaha')->nullable()->after('nama_perusahaan');
            $table->string('telepon', 20)->nullable()->after('alamat');
            $table->string('email')->nullable()->after('telepon');
            $table->string('pembimbing_industri')->nullable()->after('email');
            $table->unsignedInteger('kuota')->default(0)->after('pembimbing_industri');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->dropColumn(['bidang_usaha', 'telepon', 'email', 'pembimbing_industri', 'kuota']);
        });
    }
};