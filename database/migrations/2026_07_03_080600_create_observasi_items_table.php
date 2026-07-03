<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observasi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observasi_id')
                  ->constrained('observasis')
                  ->onDelete('cascade'); // ikut terhapus bila observasi induk dihapus
            $table->text('permasalahan');
            $table->text('solusi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observasi_items');
    }
};