<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InstrukturTemplateExport extends StringValueBinder implements FromArray, WithHeadings, WithColumnFormatting, WithCustomValueBinder
{
    public function headings(): array
    {
        return ['nama_perusahaan', 'pembimbing_industri', 'alamat', 'telepon'];
    }

    // D = telepon -> paksa TEXT agar 0 depan tidak hilang
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function array(): array
    {
        return [
            ['PT Maju Bersama', 'Andi Wijaya', 'Jl. Merdeka No. 10, Makassar', '0411123456'],
            ['CV Karya Mandiri', 'Siti Rahma', 'Jl. Sudirman No. 5, Makassar', '0411765432'],
        ];
    }
}
