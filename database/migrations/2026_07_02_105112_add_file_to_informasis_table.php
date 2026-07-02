<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informasis', function (Blueprint $table) {
            $table->string('file')->nullable()->after('konten');
        });
    }

    public function down(): void
    {
        Schema::table('informasis', function (Blueprint $table) {
            $table->dropColumn('file');
        });
    }
};