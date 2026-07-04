<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class GuruTemplateExport extends StringValueBinder implements FromArray, WithHeadings, WithColumnFormatting, WithCustomValueBinder
{
    public function headings(): array
    {
        return ['nama', 'password', 'nip', 'no_hp'];
    }

    // C = nip, D = no_hp → paksa TEXT agar NIP panjang tidak jadi format ilmiah
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function array(): array
    {
        return [
            ['Siti Pembimbing', 'password123', '197905202003121003', '081233340001'],
            ['Budi Pengajar',   'password123', '198203152006041002', '081233340002'],
        ];
    }
}