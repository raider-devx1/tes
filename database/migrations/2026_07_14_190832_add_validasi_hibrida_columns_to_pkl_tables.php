<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- JURNALS ----------
        Schema::table('jurnals', function (Blueprint $table) {
            if (! Schema::hasColumn('jurnals', 'status')) {
                $table->enum('status', ['draft', 'diajukan', 'disetujui'])
                      ->default('draft')->after('hari_tanggal');
            }
            if (! Schema::hasColumn('jurnals', 'foto_bukti')) {
                $table->string('foto_bukti')->nullable()->after('status');
            }
            // catatan_instruktur sudah ada di jurnals, jadi dilewati
            if (! Schema::hasColumn('jurnals', 'validated_by_guru_id')) {
                $table->foreignId('validated_by_guru_id')->nullable()
                      ->after('catatan_instruktur')
                      ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('jurnals', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by_guru_id');
            }
        });

        // ---------- ABSENSIS ----------
        Schema::table('absensis', function (Blueprint $table) {
            // Instruktur tidak lagi punya akun -> instruktur_id boleh kosong
            if (Schema::hasColumn('absensis', 'instruktur_id')) {
                $table->unsignedBigInteger('instruktur_id')->nullable()->change();
            }
            if (! Schema::hasColumn('absensis', 'status_validasi')) {
                // pakai nama 'status_validasi' agar tidak bentrok dgn kolom 'status' (Hadir/Izin/Sakit/Alpha)
                $table->enum('status_validasi', ['draft', 'diajukan', 'disetujui'])
                      ->default('draft')->after('status');
            }
            if (! Schema::hasColumn('absensis', 'foto_bukti')) {
                $table->string('foto_bukti')->nullable()->after('status_validasi');
            }
            if (! Schema::hasColumn('absensis', 'catatan_instruktur')) {
                $table->text('catatan_instruktur')->nullable()->after('foto_bukti');
            }
            if (! Schema::hasColumn('absensis', 'validated_by_guru_id')) {
                $table->foreignId('validated_by_guru_id')->nullable()
                      ->after('catatan_instruktur')
                      ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('absensis', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by_guru_id');
            }
        });

        // ---------- CATATAN_KEGIATANS ----------
        Schema::table('catatan_kegiatans', function (Blueprint $table) {
            if (! Schema::hasColumn('catatan_kegiatans', 'status')) {
                $table->enum('status', ['draft', 'diajukan', 'disetujui'])
                      ->default('draft')->after('pelaksanaan_kegiatan');
            }
            if (! Schema::hasColumn('catatan_kegiatans', 'foto_bukti')) {
                $table->string('foto_bukti')->nullable()->after('status');
            }
            // catatan_instruktur sudah ada di catatan_kegiatans, jadi dilewati
            if (! Schema::hasColumn('catatan_kegiatans', 'validated_by_guru_id')) {
                $table->foreignId('validated_by_guru_id')->nullable()
                      ->after('catatan_instruktur')
                      ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('catatan_kegiatans', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by_guru_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by_guru_id');
            $table->dropColumn(['status', 'foto_bukti', 'validated_at']);
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by_guru_id');
            $table->dropColumn(['status_validasi', 'foto_bukti', 'catatan_instruktur', 'validated_at']);
        });

        Schema::table('catatan_kegiatans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('validated_by_guru_id');
            $table->dropColumn(['status', 'foto_bukti', 'validated_at']);
        });
    }
};