<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\PeriodePkl;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Buat Periode PKL Aktif (fondasi penempatan siswa)
        $periode = PeriodePkl::create([
            'nama'            => 'PKL Gelombang 1',
            'tahun_ajaran'    => '2025/2026',
            'tanggal_mulai'   => '2026-01-06',
            'tanggal_selesai' => '2026-06-30',
            'is_active'       => true,
            'keterangan'      => 'Periode PKL default hasil seeder',
        ]);

        // 1. Buat Data Perusahaan / Industri
        $pt1 = Perusahaan::create([
            'nama_perusahaan'     => 'PT Semen Tonasa',
            'bidang_usaha'        => 'Manufaktur / Semen',
            'alamat'              => 'Kabupaten Pangkep',
            'telepon'             => '0410123456',
            'email'               => 'hrd@sementonasa.co.id',
            'pembimbing_industri' => 'Pak Anton',
            'kuota'               => 5,
        ]);
        $pt2 = Perusahaan::create([
            'nama_perusahaan'     => 'PT Telkom Indonesia',
            'bidang_usaha'        => 'Telekomunikasi',
            'alamat'              => 'Kabupaten Majene',
            'telepon'             => '0422123456',
            'email'               => 'magang@telkom.co.id',
            'pembimbing_industri' => 'Mbak Rina',
            'kuota'               => 4,
        ]);
        $pt3 = Perusahaan::create([
            'nama_perusahaan'     => 'Dinas Kominfo',
            'bidang_usaha'        => 'Pemerintahan / IT',
            'alamat'              => 'Provinsi Sulawesi Barat',
            'telepon'             => '0426123456',
            'email'               => 'kominfo@sulbarprov.go.id',
            'pembimbing_industri' => 'Pak Joko',
            'kuota'               => 3,
        ]);

        // 2. Buat Akun Admin
        User::create([
            'name'     => 'Admin HKI SMKN 1 Majene',
            'email'    => 'admin@smkn1majene.sch.id',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'no_hp'    => '081200000001',
        ]);

        // 3. Buat 3 Akun Guru Pembimbing (dengan NIP)
        $guru1 = User::create([
            'name' => 'Pak Budi (Guru)', 'email' => 'guru1@smkn1majene.sch.id',
            'password' => Hash::make('password123'), 'role' => 'guru_pembimbing',
            'nip' => '198001012005011001', 'no_hp' => '081211110001',
        ]);
        $guru2 = User::create([
            'name' => 'Bu Siti (Guru)', 'email' => 'guru2@smkn1majene.sch.id',
            'password' => Hash::make('password123'), 'role' => 'guru_pembimbing',
            'nip' => '198203152006042002', 'no_hp' => '081211110002',
        ]);
        $guru3 = User::create([
            'name' => 'Pak Andi (Guru)', 'email' => 'guru3@smkn1majene.sch.id',
            'password' => Hash::make('password123'), 'role' => 'guru_pembimbing',
            'nip' => '197905202003121003', 'no_hp' => '081211110003',
        ]);

        // 4. Buat 3 Akun Instruktur Industri (dengan Jabatan)
        $ins1 = User::create([
            'name' => 'Pak Anton (Semen Tonasa)', 'email' => 'anton@tonasa.com',
            'password' => Hash::make('password123'), 'role' => 'instruktur_industri',
            'jabatan' => 'Supervisor Produksi', 'no_hp' => '081222220001',
            'perusahaan_id' => $pt1->id,
        ]);
        $ins2 = User::create([
            'name' => 'Mbak Rina (Telkom)', 'email' => 'rina@telkom.co.id',
            'password' => Hash::make('password123'), 'role' => 'instruktur_industri',
            'jabatan' => 'Staff IT Support', 'no_hp' => '081222220002',
            'perusahaan_id' => $pt2->id,
        ]);
        $ins3 = User::create([
            'name' => 'Pak Joko (Kominfo)', 'email' => 'joko@kominfo.go.id',
            'password' => Hash::make('password123'), 'role' => 'instruktur_industri',
            'jabatan' => 'Kepala Seksi Infrastruktur', 'no_hp' => '081222220003',
            'perusahaan_id' => $pt3->id,
        ]);

        // 5. Buat 3 Akun Siswa (di-mapping + data master lengkap)
        User::create([
            'name'          => 'Siswa Ahmad',
            'email'         => 'ahmad@siswa.com',
            'password'      => Hash::make('password123'),
            'role'          => 'siswa_pkl',
            'nisn'          => '0051234561',
            'jenis_kelamin' => 'L',
            'no_hp'         => '081233330001',
            'status_pkl'    => 'aktif',
            'kelas'         => 'XI TKJ 1',
            'jurusan'       => 'Teknik Komputer dan Jaringan',
            'perusahaan_id' => $pt1->id,
            'instruktur_id' => $ins1->id,
            'guru_id'       => $guru1->id,
            'periode_id'    => $periode->id,
        ]);

        User::create([
            'name'          => 'Siswa Nisa',
            'email'         => 'nisa@siswa.com',
            'password'      => Hash::make('password123'),
            'role'          => 'siswa_pkl',
            'nisn'          => '0051234562',
            'jenis_kelamin' => 'P',
            'no_hp'         => '081233330002',
            'status_pkl'    => 'aktif',
            'kelas'         => 'XI RPL 2',
            'jurusan'       => 'Rekayasa Perangkat Lunak',
            'perusahaan_id' => $pt2->id,
            'instruktur_id' => $ins2->id,
            'guru_id'       => $guru2->id,
            'periode_id'    => $periode->id,
        ]);

        User::create([
            'name'          => 'Siswa Reza',
            'email'         => 'reza@siswa.com',
            'password'      => Hash::make('password123'),
            'role'          => 'siswa_pkl',
            'nisn'          => '0051234563',
            'jenis_kelamin' => 'L',
            'no_hp'         => '081233330003',
            'status_pkl'    => 'aktif',
            'kelas'         => 'XI MM 1',
            'jurusan'       => 'Multimedia',
            'perusahaan_id' => $pt3->id,
            'instruktur_id' => $ins3->id,
            'guru_id'       => $guru3->id,
            'periode_id'    => $periode->id,
        ]);
    }
}