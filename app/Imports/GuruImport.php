<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        return new User([
            'name'     => $row['nama'],
            'password' => Hash::make($row['password'] ?? 'password123'),
            'nip'      => isset($row['nip'])   ? (string) $row['nip']   : null,
            'no_hp'    => isset($row['no_hp']) ? (string) $row['no_hp'] : null,
            'role'     => 'guru_pembimbing',
        ]);
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
}