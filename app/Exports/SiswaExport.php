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

class SiswaExport extends StringValueBinder implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithCustomValueBinder
{
    protected string $q;
    protected string $status;

    public function __construct(?string $q = '', ?string $status = '')
    {
        // Terima null dari controller lalu normalkan jadi string kosong
        $this->q = (string) $q;
        $this->status = (string) $status;
    }

    public function query(): Builder
    {
        return User::query()
            ->where('role', 'siswa_pkl')
            ->with(['perusahaan', 'guru', 'instruktur', 'periode'])
            ->when($this->q, function ($query) {
                $query->where('name', 'like', "%{$this->q}%")
                      ->orWhere('nisn', 'like', "%{$this->q}%");
            })
            ->when($this->status, fn ($query) => $query->where('status_pkl', $this->status))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No', 'Nama', 'NISN', 'JK', 'No. HP',
            'Kelas', 'Jurusan', 'Tempat PKL',
            'Guru Pembimbing', 'Instruktur', 'Periode', 'Status PKL',
        ];
    }

    public function map($siswa): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $siswa->name,
            $siswa->nisn,
            $siswa->jenis_kelamin,
            $siswa->no_hp,
            $siswa->kelas,
            $siswa->jurusan,
            $siswa->perusahaan->nama_perusahaan ?? '-',
            $siswa->guru->name ?? '-',
            $siswa->instruktur->name ?? '-',
            $siswa->periode->nama ?? '-',
            ucfirst($siswa->status_pkl),
        ];
    }

    /**
     * Paksa kolom NISN & No. HP jadi TEXT agar tidak format ilmiah / 0 depan hilang.
     * C = NISN, E = No. HP
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // NISN
            'E' => NumberFormat::FORMAT_TEXT, // No. HP
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}