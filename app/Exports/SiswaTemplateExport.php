<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['nama', 'email', 'password', 'nisn', 'jk', 'no_hp', 'kelas', 'jurusan', 'status_pkl', 'tempat_pkl', 'pembimbing'];
    }

    public function array(): array
    {
        return [
            ['Budi Santoso', 'budi@example.com', 'password123', '0012345678', 'L', '08123456789', 'XII RPL 1', 'Rekayasa Perangkat Lunak', 'belum', 'PT Maju Jaya', 'Siti Aminah'],
        ];
    }
}