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
        // NIP 18 digit kerap dibaca Excel sebagai ANGKA, sehingga (string)
        // menghasilkan notasi ilmiah seperti 1.9651231199E+17 dan nol di depan
        // hilang. angkaUtuh() menjaga seluruh digitnya tetap utuh.
        $nip = self::rapikan(self::angkaUtuh($row['nip'] ?? null));

        // Password bawaan = NIP (kebiasaan sekolah), bukan 'password123'.
        // Spasi hasil salin-tempel dibuang supaya guru tidak pernah terkunci
        // oleh spasi yang tidak terlihat di kolom password.
        $passwordMentah = self::rapikan(self::angkaUtuh($row['password'] ?? null));

        if ($passwordMentah === '') {
            $passwordMentah = $nip !== '' ? $nip : 'password123';
        }

        $user = new User([
            'name'     => trim((string) $row['nama']),
            'password' => Hash::make($passwordMentah),
            'nip'      => $nip !== '' ? $nip : null,
            'no_hp'    => isset($row['no_hp']) ? self::rapikan(self::angkaUtuh($row['no_hp'])) : null,
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

    /** Ubah nilai sel menjadi teks tanpa notasi ilmiah / desimal palsu. */
    private static function angkaUtuh($nilai): string
    {
        if ($nilai === null) {
            return '';
        }

        if (is_float($nilai) || is_int($nilai)) {
            return number_format((float) $nilai, 0, '', '');
        }

        $teks = (string) $nilai;

        // Tangani teks yang terlanjur berbentuk "1.98105022005E+17".
        if (preg_match('/^\d(?:\.\d+)?E\+?\d+$/i', trim($teks))) {
            return number_format((float) $teks, 0, '', '');
        }

        return $teks;
    }

    /** Buang spasi, tab, baris baru, dan spasi tak-terputus (U+00A0). */
    private static function rapikan(?string $nilai): string
    {
        return preg_replace('/[\s\x{00A0}]+/u', '', (string) $nilai);
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
