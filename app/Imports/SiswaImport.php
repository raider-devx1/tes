<?php

namespace App\Imports;

use App\Models\Perusahaan;
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
        // Tempat PKL (perusahaan) — dicocokkan ke data yang sudah ada
        $perusahaanId = null;
        if (!empty($row['tempat_pkl'])) {
            $perusahaanId = Perusahaan::where('nama_perusahaan', $row['tempat_pkl'])->value('id');
        }

        // Pembimbing (guru) — dicocokkan ke data yang sudah ada
        $guruId = null;
        if (!empty($row['pembimbing'])) {
            $guruId = User::where('role', 'guru_pembimbing')
                ->where('name', $row['pembimbing'])
                ->value('id');
        }

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
            'perusahaan_id' => $perusahaanId,
            'guru_id'       => $guruId,
            'role'          => 'siswa_pkl',
        ]);
    }

    public function rules(): array
    {
        return [
            'nama'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            // Opsional, tapi jika diisi WAJIB sudah terdaftar lebih dulu
            'tempat_pkl' => ['nullable', Rule::exists('perusahaans', 'nama_perusahaan')],
            'pembimbing' => ['nullable', Rule::exists('users', 'name')->where('role', 'guru_pembimbing')],
            'status_pkl' => ['nullable', Rule::in(['belum', 'aktif', 'selesai'])],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required'     => 'Kolom nama wajib diisi.',
            'email.required'    => 'Kolom email wajib diisi.',
            'email.unique'      => 'Email :input sudah terdaftar.',
            'tempat_pkl.exists' => 'Tempat PKL ":input" belum terdaftar di Master Data Industri. Tambahkan industrinya dulu.',
            'pembimbing.exists' => 'Guru pembimbing ":input" belum terdaftar di Master Data Guru. Tambahkan gurunya dulu.',
            'status_pkl.in'     => 'Status PKL ":input" tidak valid (pakai: belum / aktif / selesai).',
        ];
    }
}