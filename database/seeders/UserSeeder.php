<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Perusahaan;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Data Perusahaan / Industri
        $pt1 = Perusahaan::create(['nama_perusahaan' => 'PT Semen Tonasa', 'alamat' => 'Kabupaten Pangkep']);
        $pt2 = Perusahaan::create(['nama_perusahaan' => 'PT Telkom Indonesia', 'alamat' => 'Kabupaten Majene']);
        $pt3 = Perusahaan::create(['nama_perusahaan' => 'Dinas Kominfo', 'alamat' => 'Provinsi Sulawesi Barat']);

        // 2. Buat Akun Admin
        User::create([
            'name' => 'Admin HKI SMKN 1 Majene',
            'email' => 'admin@smkn1majene.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 3. Buat 3 Akun Guru Pembimbing
        $guru1 = User::create(['name' => 'Pak Budi (Guru)', 'email' => 'guru1@smkn1majene.sch.id', 'password' => Hash::make('password123'), 'role' => 'guru_pembimbing']);
        $guru2 = User::create(['name' => 'Bu Siti (Guru)', 'email' => 'guru2@smkn1majene.sch.id', 'password' => Hash::make('password123'), 'role' => 'guru_pembimbing']);
        $guru3 = User::create(['name' => 'Pak Andi (Guru)', 'email' => 'guru3@smkn1majene.sch.id', 'password' => Hash::make('password123'), 'role' => 'guru_pembimbing']);

        // 4. Buat 3 Akun Instruktur Industri
        $ins1 = User::create(['name' => 'Pak Anton (Semen Tonasa)', 'email' => 'anton@tonasa.com', 'password' => Hash::make('password123'), 'role' => 'instruktur_industri']);
        $ins2 = User::create(['name' => 'Mbak Rina (Telkom)', 'email' => 'rina@telkom.co.id', 'password' => Hash::make('password123'), 'role' => 'instruktur_industri']);
        $ins3 = User::create(['name' => 'Pak Joko (Kominfo)', 'email' => 'joko@kominfo.go.id', 'password' => Hash::make('password123'), 'role' => 'instruktur_industri']);

        // 5. Buat 3 Akun Siswa (Langsung di-mapping ke Guru, Instruktur, dan Perusahaan)
        User::create([
            'name' => 'Siswa Ahmad',
            'email' => 'ahmad@siswa.com',
            'password' => Hash::make('password123'),
            'role' => 'siswa_pkl',
            'kelas' => 'XI TKJ 1',
            'jurusan' => 'Teknik Komputer dan Jaringan',
            'perusahaan_id' => $pt1->id,
            'instruktur_id' => $ins1->id,
            'guru_id' => $guru1->id,
        ]);

        User::create([
            'name' => 'Siswa Nisa',
            'email' => 'nisa@siswa.com',
            'password' => Hash::make('password123'),
            'role' => 'siswa_pkl',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'perusahaan_id' => $pt2->id,
            'instruktur_id' => $ins2->id,
            'guru_id' => $guru2->id,
        ]);

        User::create([
            'name' => 'Siswa Reza',
            'email' => 'reza@siswa.com',
            'password' => Hash::make('password123'),
            'role' => 'siswa_pkl',
            'kelas' => 'XI MM 1',
            'jurusan' => 'Multimedia',
            'perusahaan_id' => $pt3->id,
            'instruktur_id' => $ins3->id,
            'guru_id' => $guru3->id,
        ]);
    }
}