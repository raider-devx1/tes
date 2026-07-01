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
            'email'    => $row['email'],
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
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'nip'   => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required'  => 'Kolom nama wajib diisi.',
            'email.required' => 'Kolom email wajib diisi.',
            'email.email'    => 'Format email ":input" tidak valid.',
            'email.unique'   => 'Email :input sudah terdaftar.',
        ];
    }
}