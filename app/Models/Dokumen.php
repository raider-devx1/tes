<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    use HasFactory;

    protected $fillable = ['siswa_id', 'laporan_akhir', 'surat_tugas', 'surat_penerimaan'];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /** Sumber tunggal aturan hak akses. Key = nama kolom di tabel dokumens. */
    public const ATURAN = [
        'surat_tugas' => [
            'label'    => 'Surat Tugas PKL',
            'upload'   => ['admin'],
            'lihat'    => ['admin', 'guru_pembimbing', 'siswa_pkl'],
            'download' => ['admin', 'siswa_pkl', 'guru_pembimbing'],
        ],
        'surat_penerimaan' => [
            'label'    => 'Surat Penerimaan Industri',
            'upload'   => ['siswa_pkl', 'admin'],
            'lihat'    => ['siswa_pkl', 'guru_pembimbing', 'admin'],
            'download' => ['admin', 'guru_pembimbing'],
        ],
        'laporan_akhir' => [
            'label'    => 'Laporan PKL Final',
            'upload'   => ['siswa_pkl', 'admin'],
            'lihat'    => ['siswa_pkl', 'guru_pembimbing', 'admin', 'instruktur_industri'],
            'download' => ['guru_pembimbing', 'admin'],
        ],
    ];

    /**
     * Cek apakah $user boleh melakukan $aksi (upload|lihat|download)
     * pada $jenis dokumen milik $siswa.
     */
    public static function boleh(string $aksi, string $jenis, User $user, User $siswa): bool
    {
        $aturan = self::ATURAN[$jenis] ?? null;

        if (!$aturan || !in_array($user->role, $aturan[$aksi] ?? [], true)) {
            return false;
        }

        return match ($user->role) {
            'admin'               => true,
            'siswa_pkl'           => $siswa->id === $user->id,
            'guru_pembimbing'     => (int) $siswa->guru_id === $user->id
                                     && $siswa->status_pkl === 'aktif',
            'instruktur_industri' => (int) $siswa->instruktur_id === $user->id
                                     && $siswa->status_pkl === 'aktif',
            default               => false,
        };
    }
}