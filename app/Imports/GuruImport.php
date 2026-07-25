<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithChunkReading, WithBatchInserts
{
    public function model(array $row)
    {
        $user = new User([
            'name'     => $row['nama'],
            'password' => Hash::make($row['password'] ?? 'password123'),
            'nip'      => isset($row['nip'])   ? (string) $row['nip']   : null,
            'no_hp'    => isset($row['no_hp']) ? (string) $row['no_hp'] : null,
            'role'     => 'guru_pembimbing',
        ]);

        // Set timestamp secara eksplisit. Saat memakai WithBatchInserts,
        // baris disimpan lewat INSERT massal; menetapkan created_at/updated_at
        // di sini memastikan kolom waktu TIDAK kosong (NULL) apa pun perilaku
        // internal paket.
        $now = now();
        $user->created_at = $now;
        $user->updated_at = $now;

        return $user;
    }

    public function rules(): array
    {
        return [
            'nama'  => ['required', 'string', 'max:100'],
            'nip'   => ['required', 'string', 'max:30', Rule::unique('users', 'nip')],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required' => 'Kolom nama wajib diisi.',
            'nip.required'  => 'Kolom NIP wajib diisi.',
            'nip.unique'    => 'NIP :input sudah terdaftar.',
        ];
    }

    /** Baca file per 500 baris agar hemat memori (tidak memuat seluruh file sekaligus). */
    public function chunkSize(): int
    {
        return 500;
    }

    /** Simpan ke database per 500 baris (INSERT massal) agar jauh lebih sedikit round-trip. */
    public function batchSize(): int
    {
        return 500;
    }
}
