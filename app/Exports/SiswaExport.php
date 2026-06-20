<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected string $q = '',
        protected string $status = ''
    ) {}

    public function query(): Builder
    {
        return User::query()
            ->where('role', 'siswa_pkl')
            ->with(['perusahaan', 'guru', 'instruktur', 'periode'])
            ->when($this->q, function ($query) {
                $query->where('name', 'like', "%{$this->q}%")
                      ->orWhere('nisn', 'like', "%{$this->q}%")
                      ->orWhere('email', 'like', "%{$this->q}%");
            })
            ->when($this->status, fn ($query) => $query->where('status_pkl', $this->status))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No', 'Nama', 'NISN', 'Email', 'JK', 'No. HP',
            'Kelas', 'Jurusan', 'Status PKL', 'Perusahaan',
            'Guru Pembimbing', 'Instruktur', 'Periode',
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
            $siswa->email,
            $siswa->jenis_kelamin,
            $siswa->no_hp,
            $siswa->kelas,
            $siswa->jurusan,
            ucfirst($siswa->status_pkl),
            $siswa->perusahaan->nama_perusahaan ?? '-',
            $siswa->guru->name ?? '-',
            $siswa->instruktur->name ?? '-',
            $siswa->periode->nama ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}