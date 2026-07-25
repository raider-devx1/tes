<?php

namespace App\Imports;

use App\Models\PeriodePkl;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithChunkReading, WithBatchInserts
{
    /** Peta [nama_perusahaan => id] — dimuat SEKALI di awal (bukan query per baris). */
    private array $perusahaanMap;

    /** Peta [nama_periode => id]. */
    private array $periodeMap;

    /** Peta [nip ATAU nama guru => id] untuk resolusi pembimbing. */
    private array $guruMap;

    public function __construct()
    {
        // Muat semua data referensi SEKALI di awal, bukan query berulang tiap
        // baris. Sebelumnya setiap baris menjalankan ~6 query (cari perusahaan,
        // guru, periode + validasi exists). Dengan peta ini, resolusi &
        // validasi dilakukan di memori sehingga impor file besar jauh lebih
        // ringan bagi database.
        $this->perusahaanMap = Perusahaan::query()
            ->pluck('id', 'nama_perusahaan')
            ->toArray();

        $this->periodeMap = PeriodePkl::query()
            ->pluck('id', 'nama')
            ->toArray();

        $this->guruMap = [];
        User::query()
            ->where('role', 'guru_pembimbing')
            ->get(['id', 'nip', 'name'])
            ->each(function ($g) {
                if (!empty($g->nip)) {
                    $this->guruMap[(string) $g->nip] = $g->id;
                }
                if (!empty($g->name)) {
                    $this->guruMap[trim((string) $g->name)] = $g->id;
                }
            });
    }

    public function model(array $row)
    {
        // Tempat PKL (perusahaan) — ambil dari peta, tanpa query.
        $perusahaanId = !empty($row['tempat_pkl'])
            ? ($this->perusahaanMap[$row['tempat_pkl']] ?? null)
            : null;

        // Guru pembimbing — boleh diisi NIP ATAU nama, ambil dari peta.
        $guruId = null;
        if (!empty($row['pembimbing'])) {
            $guruId = $this->guruMap[trim((string) $row['pembimbing'])] ?? null;
        }

        // Periode PKL — ambil dari peta.
        $periodeId = !empty($row['periode'])
            ? ($this->periodeMap[$row['periode']] ?? null)
            : null;

        $user = new User([
            'name'          => $row['nama'],
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
            'role'          => 'siswa_pkl',
        ]);

        // Set timestamp eksplisit agar aman saat INSERT massal (WithBatchInserts).
        $now = now();
        $user->created_at = $now;
        $user->updated_at = $now;

        return $user;
    }

    public function rules(): array
    {
        return [
            'nama'  => ['required', 'string', 'max:100'],
            'nisn'  => ['required', 'string', 'max:20', Rule::unique('users', 'nisn')],
            'status_pkl' => ['nullable', Rule::in(['belum', 'aktif', 'selesai'])],

            // Opsional, tapi jika diisi WAJIB sudah terdaftar lebih dulu.
            // Divalidasi memakai peta yang sudah dimuat (tanpa query per baris).
            'tempat_pkl' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (trim((string) $value) === '') {
                        return;
                    }
                    if (!array_key_exists($value, $this->perusahaanMap)) {
                        $fail("Tempat PKL \"{$value}\" belum terdaftar di Master Data Industri. Tambahkan industrinya dulu.");
                    }
                },
            ],

            // Pembimbing: valid jika cocok dengan NIP ATAU nama guru pembimbing yang ada.
            'pembimbing' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $val = trim((string) $value);
                    if ($val === '') {
                        return;
                    }
                    if (!array_key_exists($val, $this->guruMap)) {
                        $fail("Guru pembimbing \"{$val}\" belum terdaftar. Isi dengan NIP atau nama persis yang ada di Master Data Guru.");
                    }
                },
            ],

            'periode' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (trim((string) $value) === '') {
                        return;
                    }
                    if (!array_key_exists($value, $this->periodeMap)) {
                        $fail("Periode \"{$value}\" belum terdaftar di Master Data Periode.");
                    }
                },
            ],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama.required'     => 'Kolom nama wajib diisi.',
            'nisn.required'     => 'Kolom NISN wajib diisi.',
            'nisn.unique'       => 'NISN :input sudah terdaftar.',
            'status_pkl.in'     => 'Status PKL ":input" tidak valid (pakai: belum / aktif / selesai).',
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
