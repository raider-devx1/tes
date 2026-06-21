<?php

namespace App\Imports;

use App\Models\PeriodePkl;
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
        // Tempat PKL (perusahaan)
        $perusahaanId = !empty($row['tempat_pkl'])
            ? Perusahaan::where('nama_perusahaan', $row['tempat_pkl'])->value('id')
            : null;

        // Guru pembimbing
        $guruId = !empty($row['pembimbing'])
            ? User::where('role', 'guru_pembimbing')->where('name', $row['pembimbing'])->value('id')
            : null;

        // Instruktur industri (dipisah dari pembimbing)
        $instrukturId = !empty($row['instruktur'])
            ? User::where('role', 'instruktur_industri')->where('name', $row['instruktur'])->value('id')
            : null;

        // Periode PKL
        $periodeId = !empty($row['periode'])
            ? PeriodePkl::where('nama', $row['periode'])->value('id')
            : null;

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
            'periode_id'    => $periodeId,
            'perusahaan_id' => $perusahaanId,
            'guru_id'       => $guruId,
            'instruktur_id' => $instrukturId,
            'role'          => 'siswa_pkl',
        ]);
    }

    public function rules(): array
    {
        return [
            'nama'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'status_pkl' => ['nullable', Rule::in(['belum', 'aktif', 'selesai'])],
            // Opsional, tapi jika diisi WAJIB sudah terdaftar lebih dulu
            'tempat_pkl' => ['nullable', Rule::exists('perusahaans', 'nama_perusahaan')],
            'pembimbing' => ['nullable', Rule::exists('users', 'name')->where('role', 'guru_pembimbing')],
            'instruktur' => ['nullable', Rule::exists('users', 'name')->where('role', 'instruktur_industri')],
            'periode'    => ['nullable', Rule::exists('periode_pkls', 'nama')],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required'     => 'Kolom nama wajib diisi.',
            'email.required'    => 'Kolom email wajib diisi.',
            'email.unique'      => 'Email :input sudah terdaftar.',
            'status_pkl.in'     => 'Status PKL ":input" tidak valid (pakai: belum / aktif / selesai).',
            'tempat_pkl.exists' => 'Tempat PKL ":input" belum terdaftar di Master Data Industri. Tambahkan industrinya dulu.',
            'pembimbing.exists' => 'Guru pembimbing ":input" belum terdaftar di Master Data Guru. Tambahkan gurunya dulu.',
            'instruktur.exists' => 'Instruktur ":input" belum terdaftar di Master Data Instruktur. Tambahkan instrukturnya dulu.',
            'periode.exists'    => 'Periode ":input" belum terdaftar di Master Data Periode.',
        ];
    }
}