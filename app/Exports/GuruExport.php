<?php

namespace App\Exports;

use App\Models\User;
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

class GuruExport extends StringValueBinder implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithCustomValueBinder
{
    public function __construct(protected string $q = '') {}

    public function query(): Builder
    {
        return User::query()
            ->where('role', 'guru_pembimbing')
            ->when($this->q, function ($query) {
                $query->where('name', 'like', "%{$this->q}%")
                      ->orWhere('nip', 'like', "%{$this->q}%");
            })
            ->orderBy('name');
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'NIP', 'No. HP'];
    }

    public function map($guru): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $guru->name,
            $guru->nip ?? '-',
            $guru->no_hp ?? '-',
        ];
    }

    /**
     * Paksa kolom NIP & No. HP jadi TEXT agar tidak format ilmiah / 0 depan hilang.
     * C = NIP, D = No. HP
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // NIP
            'D' => NumberFormat::FORMAT_TEXT, // No. HP
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}