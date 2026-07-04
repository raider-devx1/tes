<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SiswaTemplateExport extends StringValueBinder implements FromArray, WithHeadings, WithColumnFormatting, WithCustomValueBinder
{
    public function headings(): array
    {
        return [
            'nama', 'password', 'nisn', 'jk', 'no_hp',
            'kelas', 'jurusan', 'status_pkl', 'periode',
            'tempat_pkl', 'pembimbing', 'instruktur',
        ];
    }

    /**
     * Paksa kolom ID-like tampil sebagai TEXT di Excel
     * (C = nisn, E = no_hp, K = pembimbing).
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // nisn
            'E' => NumberFormat::FORMAT_TEXT, // no_hp
            'K' => NumberFormat::FORMAT_TEXT, // pembimbing (NIP / nama)
        ];
    }

    public function array(): array
    {
        return [
            // === Mengacu ke master data di Master Data ===
            // Kolom periode    -> nama periode              : 'PKL Gelombang 1'
            // Kolom tempat_pkl -> nama perusahaan
            // Kolom instruktur -> nama instruktur_industri
            // Kolom pembimbing -> BOLEH NIP ATAU nama guru pembimbing yang sudah ada
            //                     (baris 1 contoh pakai NIP, baris 2 & 3 pakai nama)

            [
                'Budi Santoso', 'password123', '0051234570', 'L', '081255550001',
                'XI TKJ 1', 'Teknik Komputer dan Jaringan', 'belum', 'PKL Gelombang 1',
                'PT Semen Tonasa', '197905202003121003', 'Pak Anton (Semen Tonasa)',
            ],

            [
                'Dewi Lestari', 'password123', '0051234571', 'P', '081255550002',
                'XI RPL 2', 'Rekayasa Perangkat Lunak', 'belum', 'PKL Gelombang 1',
                'PT Telkom Indonesia', 'Pak Andi (Guru)', 'Mbak Rina (Telkom)',
            ],

            [
                'Andi Saputra', 'password123', '0051234572', 'L', '081255550003',
                'XI TKJ 2', 'Teknik Komputer dan Jaringan', 'belum', 'PKL Gelombang 1',
                'Dinas Kominfo', 'Pak Andi (Guru)', 'Pak Joko (Kominfo)',
            ],
        ];
    }
}