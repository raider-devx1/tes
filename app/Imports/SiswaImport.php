<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        return new User([
            'name'          => $row['nama'],
            'email'         => $row['email'],
            'password'      => Hash::make($row['password'] ?? 'password123'),
            'nisn'          => $row['nisn'] ?? null,
            'jenis_kelamin' => in_array($row['jk'] ?? null, ['L', 'P']) ? $row['jk'] : null,
            'no_hp'         => $row['no_hp'] ?? null,
            'kelas'         => $row['kelas'] ?? null,
            'jurusan'       => $row['jurusan'] ?? null,
            'status_pkl'    => in_array($row['status_pkl'] ?? null, ['belum', 'aktif', 'selesai']) ? $row['status_pkl'] : 'belum',
            'role'          => 'siswa_pkl',
        ]);
    }

    public function rules(): array
    {
        return [
            'nama'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required'  => 'Kolom nama wajib diisi.',
            'email.required' => 'Kolom email wajib diisi.',
            'email.unique'   => 'Email :input sudah terdaftar.',
        ];
    }
}