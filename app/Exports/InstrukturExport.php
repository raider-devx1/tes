<?php

namespace App\Exports;

use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InstrukturExport extends StringValueBinder implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithCustomValueBinder
{
    public function __construct(protected string $q = '') {}

    public function query(): Builder
    {
        return Perusahaan::query()
            ->when($this->q, function ($query) {
                $query->where('nama_perusahaan', 'like', "%{$this->q}%")
                      ->orWhere('pembimbing_industri', 'like', "%{$this->q}%")
                      ->orWhere('alamat', 'like', "%{$this->q}%");
            })
            ->orderBy('nama_perusahaan');
    }

    public function headings(): array
    {
        return ['No', 'Nama Perusahaan', 'Pembimbing (Instruktur)', 'Alamat', 'Telepon'];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $item->nama_perusahaan,
            $item->pembimbing_industri ?? '-',
            $item->alamat ?? '-',
            $item->telepon ?? '-',
        ];
    }

    /**
     * Paksa kolom Telepon jadi TEXT agar 0 depan tidak hilang / tidak jadi format ilmiah.
     * E = Telepon
     */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
