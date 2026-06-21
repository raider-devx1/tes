<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nama', 'email', 'password', 'nisn', 'jk', 'no_hp',
            'kelas', 'jurusan', 'status_pkl', 'periode',
            'tempat_pkl', 'pembimbing', 'instruktur',
        ];
    }

    public function array(): array
    {
        return [
            // === Mengacu ke master data di UserSeeder / PeriodePklSeeder ===
            // Kolom periode  -> nama periode di PeriodePklSeeder  : 'PKL Gelombang 1'
            // Kolom instruktur -> nama instruktur_industri di UserSeeder
            // Kolom pembimbing -> nama guru_pembimbing di UserSeeder

            // Penempatan di Semen Tonasa (instruktur: Pak Anton)
            [
                'Budi Santoso', 'budi@siswa.com', 'password123', '0051234570', 'L', '081255550001',
                'XI TKJ 1', 'Teknik Komputer dan Jaringan', 'belum', 'PKL Gelombang 1',
                'PT Semen Tonasa', 'Pak Andi (Guru)', 'Pak Anton (Semen Tonasa)',
            ],

            // Penempatan di Telkom (instruktur: Mbak Rina)
            [
                'Dewi Lestari', 'dewi@siswa.com', 'password123', '0051234571', 'P', '081255550002',
                'XI RPL 2', 'Rekayasa Perangkat Lunak', 'belum', 'PKL Gelombang 1',
                'PT Telkom Indonesia', 'Pak Andi (Guru)', 'Mbak Rina (Telkom)',
            ],

            // Penempatan di Kominfo (instruktur: Pak Joko)
            [
                'Andi Saputra', 'andi.saputra@siswa.com', 'password123', '0051234572', 'L', '081255550003',
                'XI TKJ 2', 'Teknik Komputer dan Jaringan', 'belum', 'PKL Gelombang 1',
                'Dinas Kominfo', 'Pak Andi (Guru)', 'Pak Joko (Kominfo)',
            ],
        ];
    }
}