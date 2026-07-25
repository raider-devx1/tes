<?php

namespace App\Imports;

use App\Models\Perusahaan;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class InstrukturImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithChunkReading, WithBatchInserts
{
    public function model(array $row)
    {
        $perusahaan = new Perusahaan([
            'nama_perusahaan'     => $row['nama_perusahaan'],
            'alamat'              => $row['alamat'] ?? null,
            'telepon'             => isset($row['telepon']) ? (string) $row['telepon'] : null,
            'pembimbing_industri' => $row['pembimbing_industri'] ?? null,
        ]);

        // Set timestamp eksplisit karena WithBatchInserts memakai INSERT massal,
        // agar kolom created_at/updated_at tidak kosong (NULL).
        $now = now();
        $perusahaan->created_at = $now;
        $perusahaan->updated_at = $now;

        return $perusahaan;
    }

    public function rules(): array
    {
        return [
            'nama_perusahaan'     => ['required', 'string', 'max:150'],
            'alamat'              => ['required', 'string', 'max:255'],
            'telepon'             => ['nullable', 'string', 'max:20'],
            'pembimbing_industri' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama_perusahaan.required' => 'Kolom nama_perusahaan wajib diisi.',
            'alamat.required'          => 'Kolom alamat wajib diisi.',
        ];
    }

    /** Baca file per 500 baris agar hemat memori. */
    public function chunkSize(): int
    {
        return 500;
    }

    /** Simpan ke database per 500 baris (INSERT massal). */
    public function batchSize(): int
    {
        return 500;
    }
}
