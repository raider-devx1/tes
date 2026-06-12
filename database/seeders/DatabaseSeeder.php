<?php

namespace Database\Seeders;

use App\Models\Informasi;
use App\Models\Pengaturan;
use App\Models\PeriodePkl;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Pengaturan default ----------
        $pengaturan = [
            'nama_sekolah'   => 'SMK Negeri 1 Contoh',
            'alamat_sekolah' => 'Jl. Pendidikan No. 1, Kota Contoh',
            'tahun_ajaran'   => '2025/2026',
            'kepala_sekolah' => 'Drs. Budi Santoso',
            'nip_kepala'     => '19650101 199003 1 001',
        ];
        foreach ($pengaturan as $kunci => $nilai) {
            Pengaturan::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        }

        // ---------- Periode aktif ----------
        PeriodePkl::updateOrCreate(
            ['nama' => 'PKL Gelombang 1 - 2026'],
            [
                'tanggal_mulai'   => now()->startOfMonth(),
                'tanggal_selesai' => now()->addMonths(3),
                'aktif'           => true,
            ]
        );

        // ---------- Perusahaan / Industri ----------
        $perusahaan = Perusahaan::updateOrCreate(
            ['nama' => 'PT Teknologi Nusantara'],
            [
                'bidang'              => 'Pengembangan Perangkat Lunak',
                'alamat'              => 'Jl. Industri No. 10, Kota Contoh',
                'telepon'             => '021-1234567',
                'pembimbing_industri' => 'Andi Wijaya',
            ]
        );

        // ---------- Akun per peran ----------
        $admin = User::updateOrCreate(['email' => 'admin@pkl.test'], [
            'name' => 'Administrator', 'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN, 'email_verified_at' => now(),
        ]);

        $guru = User::updateOrCreate(['email' => 'guru@pkl.test'], [
            'name' => 'Siti Pembimbing', 'password' => Hash::make('password'),
            'role' => User::ROLE_GURU, 'telepon' => '0812000001', 'email_verified_at' => now(),
        ]);

        $instruktur = User::updateOrCreate(['email' => 'instruktur@pkl.test'], [
            'name' => 'Andi Wijaya', 'password' => Hash::make('password'),
            'role' => User::ROLE_INSTRUKTUR, 'telepon' => '0812000002',
            'perusahaan_id' => $perusahaan->id, 'email_verified_at' => now(),
        ]);

        foreach ([['Ahmad Siswa', 'siswa@pkl.test', '2025001'], ['Dewi Lestari', 'dewi@pkl.test', '2025002']] as [$nama, $email, $nis]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $nama, 'password' => Hash::make('password'),
                'role' => User::ROLE_SISWA, 'nis' => $nis,
                'kelas' => 'XII RPL 1', 'jurusan' => 'Rekayasa Perangkat Lunak',
                'perusahaan_id' => $perusahaan->id,
                'guru_id' => $guru->id, 'instruktur_id' => $instruktur->id,
                'email_verified_at' => now(),
            ]);
        }

        // ---------- Informasi / Panduan PKL ----------
        $informasi = [
            ['Latar Belakang PKL', 'umum', 'Praktik Kerja Lapangan (PKL) merupakan bagian dari pembelajaran yang menghubungkan kompetensi di sekolah dengan dunia industri.', 1],
            ['Tujuan PKL', 'umum', 'Meningkatkan kompetensi, kedisiplinan, serta pengalaman kerja nyata bagi siswa.', 2],
            ['Manfaat PKL', 'umum', 'Siswa memperoleh wawasan industri, jaringan profesional, dan kesiapan kerja.', 3],
            ['Panduan Penyusunan Laporan', 'panduan_laporan', 'Laporan disusun dengan sistematika: Pendahuluan, Profil Industri, Uraian Kegiatan, Penutup, dan Lampiran.', 4],
            ['Panduan Presentasi', 'panduan_presentasi', 'Presentasi maksimal 15 menit, gunakan slide ringkas, tampilkan dokumentasi kegiatan, dan siapkan sesi tanya jawab.', 5],
        ];
        foreach ($informasi as [$judul, $kategori, $konten, $urutan]) {
            Informasi::updateOrCreate(['judul' => $judul], compact('kategori', 'konten', 'urutan'));
        }
    }
}
