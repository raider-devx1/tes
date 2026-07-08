<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\PeriodePkl;
use App\Models\Jurnal;
use App\Models\CatatanKegiatan;
use App\Models\Observasi;
use App\Models\Nilai;
use App\Models\Absensi;
use App\Models\Dokumen;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Set true untuk menaruh SEMUA siswa ke guru1 (uji pagination halaman "Daftar Siswa Bimbingan" guru)
        $fokusSatuGuru = false;

        /* ============================================================
         | 0. PERIODE PKL  (2 periode -> untuk uji filter dropdown periode)
         ============================================================ */
        $periodeLama = PeriodePkl::create([
            'nama'            => 'PKL Gelombang 0 (Lampau)',
            'tahun_ajaran'    => '2024/2025',
            'tanggal_mulai'   => '2025-01-06',
            'tanggal_selesai' => '2025-06-30',
            'is_active'       => false,
            'keterangan'      => 'Periode lampau untuk uji filter.',
        ]);

        $periodeAktif = PeriodePkl::create([
            'nama'            => 'PKL Gelombang 1',
            'tahun_ajaran'    => '2025/2026',
            'tanggal_mulai'   => '2026-01-06',
            'tanggal_selesai' => '2026-06-30',
            'is_active'       => true,
            'keterangan'      => 'Periode PKL aktif hasil seeder.',
        ]);

        /* ============================================================
         | 1. PERUSAHAAN / INDUSTRI
         ============================================================ */
        $pt1 = Perusahaan::create([
            'nama_perusahaan'     => 'PT Semen Tonasa',
            'alamat'              => 'Kabupaten Pangkep',
            'telepon'             => '0410123456',
            'pembimbing_industri' => 'Pak Anton',
        ]);
        $pt2 = Perusahaan::create([
            'nama_perusahaan'     => 'PT Telkom Indonesia',
            'alamat'              => 'Kabupaten Majene',
            'telepon'             => '0422123456',
            'pembimbing_industri' => 'Mbak Rina',
        ]);
        $pt3 = Perusahaan::create([
            'nama_perusahaan'     => 'Dinas Kominfo',
            'alamat'              => 'Provinsi Sulawesi Barat',
            'telepon'             => '0426123456',
            'pembimbing_industri' => 'Pak Joko',
        ]);

        /* ============================================================
         | 2. ADMIN  (login pakai email)
         ============================================================ */
        User::create([
            'name'     => 'Admin HKI SMKN 1 Majene',
            'email'    => 'admin@smkn1majene.sch.id',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'no_hp'    => '081200000001',
        ]);

        /* ============================================================
         | 3. GURU PEMBIMBING (3 akun) — login pakai NIP (tanpa email)
         ============================================================ */
        $guru1 = User::create([
            'name' => 'Pak Budi (Guru)',
            'password' => Hash::make('password123'), 'role' => 'guru_pembimbing',
            'nip' => '198001012005011001', 'no_hp' => '081211110001',
        ]);
        $guru2 = User::create([
            'name' => 'Bu Siti (Guru)',
            'password' => Hash::make('password123'), 'role' => 'guru_pembimbing',
            'nip' => '198203152006042002', 'no_hp' => '081211110002',
        ]);
        $guru3 = User::create([
            'name' => 'Pak Andi (Guru)',
            'password' => Hash::make('password123'), 'role' => 'guru_pembimbing',
            'nip' => '197905202003121003', 'no_hp' => '081211110003',
        ]);

        /* ============================================================
         | 4. INSTRUKTUR INDUSTRI (3 akun, masing-masing 1 perusahaan) — login pakai email
         ============================================================ */
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

        /* ============================================================
         | 5. 20 SISWA PKL + SEMUA DATA PENDUKUNG — login pakai NISN (tanpa email)
         ============================================================ */
        $gurus = [$guru1, $guru2, $guru3];
        $industri = [
            ['ins' => $ins1, 'pt' => $pt1],
            ['ins' => $ins2, 'pt' => $pt2],
            ['ins' => $ins3, 'pt' => $pt3],
        ];

        $namaList = [
            'Andi', 'Budi', 'Citra', 'Dewi', 'Eka', 'Fajar', 'Gina', 'Hadi',
            'Indah', 'Joko', 'Kiki', 'Lina', 'Maya', 'Nanda', 'Omar', 'Putri',
            'Qori', 'Rian', 'Sari', 'Tono',
        ];

        $kelasList = ['XI KULINER 1', 'XI BUSANA 1', 'XI KECANTIKAN 1', 'XI TJKT 1', 'XI PERHOTELAN 1'];

$jurusanMap = [
    'XI KULINER 1'    => 'Kuliner',
    'XI BUSANA 1'     => 'Busana',
    'XI KECANTIKAN 1' => 'Kecantikan & Spa',
    'XI TJKT 1'       => 'Teknik Jaringan Komputer dan Telekomunikasi',
    'XI PERHOTELAN 1' => 'Perhotelan',
];

        $statusJurnal = ['pending', 'disetujui', 'revisi'];
        $statusAbsen  = ['Hadir', 'Hadir', 'Izin', 'Sakit', 'Alpha'];

        for ($i = 1; $i <= 20; $i++) {
            $guru  = $fokusSatuGuru ? $guru1 : $gurus[($i - 1) % 3];
            $ind   = $industri[($i - 1) % 3];
            $kelas = $kelasList[($i - 1) % count($kelasList)];

            // Sebagian siswa di periode lampau (i = 5,10,15,20) untuk uji filter periode
            $periode = ($i % 5 === 0) ? $periodeLama : $periodeAktif;

            $siswa = User::create([
                'name'          => 'Siswa ' . $namaList[$i - 1],
                'password'      => Hash::make('password123'),
                'role'          => 'siswa_pkl',
                'nisn'          => '005123' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'jenis_kelamin' => $i % 2 === 0 ? 'P' : 'L',
                'no_hp'         => '0812' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'status_pkl'    => 'aktif',
                'kelas'         => $kelas,
                'jurusan'       => $jurusanMap[$kelas],
                'perusahaan_id' => $ind['pt']->id,
                'instruktur_id' => $ind['ins']->id,
                'guru_id'       => $guru->id,
                'periode_id'    => $periode->id,
            ]);

            // ---- JURNAL (3 entri, tiap entri punya beberapa pekerjaan/unit kerja) ----
            for ($j = 1; $j <= 3; $j++) {
                $st = $statusJurnal[($j - 1) % 3];

                $jurnal = Jurnal::create([
                    'siswa_id'           => $siswa->id,
                    'hari_tanggal'       => now()->subDays($j)->toDateString(),
                    'catatan_instruktur' => $st === 'disetujui' ? 'Kerja bagus.' : ($st === 'revisi' ? 'Mohon diperbaiki.' : null),
                    'status_persetujuan' => $st,
                    'disetujui_oleh'     => $st === 'pending' ? null : $ind['ins']->id,
                ]);

                // Jumlah pekerjaan bervariasi (jurnal ke-1 = 1 pekerjaan, ke-2 = 2, ke-3 = 3)
                for ($k = 1; $k <= $j; $k++) {
                    $jurnal->items()->create([
                        'unit_kerja'  => "Pekerjaan ke-$k pada Divisi $j untuk {$siswa->name}.",
                        'dokumentasi' => null,
                    ]);
                }
            }

            // ---- CATATAN KEGIATAN (3 entri) ----
            for ($c = 1; $c <= 3; $c++) {
                CatatanKegiatan::create([
                    'user_id'              => $siswa->id,
                    'nama_pekerjaan'       => "Proyek ke-$c",
                    'perencanaan_kegiatan' => "Rencana kegiatan ke-$c.",
                    'pelaksanaan_kegiatan' => "Pelaksanaan & hasil kegiatan ke-$c.",
                    'catatan_instruktur'   => $c === 1 ? 'Sudah sesuai target.' : null,
                    'is_approved'          => $c === 1, // 1 disetujui, sisanya menunggu
                ]);
            }

            // ---- OBSERVASI (3 entri; tiap observasi punya beberapa poin masalah & solusi) ----
            for ($o = 1; $o <= 3; $o++) {
                $observasi = Observasi::create([
                    'user_id'          => $siswa->id,
                    'guru_id'          => $guru->id,
                    'hari_tanggal'     => now()->subDays($o * 2)->toDateString(),
                    'pekerjaan_projek' => "Observasi projek ke-$o",
                    'is_approved'      => $o === 1, // 1 disetujui, sisanya menunggu
                ]);

                // Jumlah poin bervariasi (observasi ke-1 = 1 poin, ke-2 = 2 poin, ke-3 = 3 poin)
                for ($p = 1; $p <= $o; $p++) {
                    $observasi->items()->create([
                        'permasalahan' => "Permasalahan poin ke-$p pada observasi ke-$o untuk {$siswa->name}.",
                        'solusi'       => "Solusi poin ke-$p untuk observasi ke-$o.",
                    ]);
                }
            }

            // ---- ABSENSI (5 entri, status & jam bervariasi) ----
            foreach ($statusAbsen as $idx => $stAbs) {
                Absensi::create([
                    'siswa_id'      => $siswa->id,
                    'instruktur_id' => $ind['ins']->id,
                    'tanggal'       => now()->subDays($idx)->toDateString(),
                    'status'        => $stAbs,
                    'jam_masuk'     => $stAbs === 'Hadir' ? '07:30:00' : null,
                    'jam_pulang'    => $stAbs === 'Hadir' ? '16:00:00' : null,
                ]);
            }

            // ---- NILAI (2/3 lengkap, 1/3 baru dinilai instruktur saja) ----
            $soft = rand(3, 5);
            $hard = rand(3, 5);
            $peng = rand(3, 5);
            $kwu  = rand(3, 5);
            $rata = round(($soft + $hard + $peng + $kwu) / 4, 2);

            $lengkap      = ($i % 3 !== 0); // sebagian belum dinilai guru -> nilai_akhir null
            $nilaiGuru    = $lengkap ? rand(75, 95) : null;
            $nilaiLaporan = $lengkap ? rand(75, 95) : null;

            $nilaiAkhir = null;
            if ($lengkap) {
                $instruktur100 = ($rata / 5) * 100;
                $nilaiAkhir = round(
                    ($instruktur100 * 0.50) + ($nilaiGuru * 0.20) + ($nilaiLaporan * 0.30),
                    2
                );
            }

            Nilai::create([
                'user_id'                 => $siswa->id,
                'instruktur_id'           => $ind['ins']->id,
                'guru_id'                 => $lengkap ? $guru->id : null,
                'soft_skill'              => $soft,
                'hard_skill'              => $hard,
                'pengembangan_hard_skill' => $peng,
                'kewirausahaan'           => $kwu,
                'rata_rata'               => $rata,
                'catatan_rekomendasi'     => 'Direkomendasikan untuk pengembangan lebih lanjut.',
                'nilai_guru'              => $nilaiGuru,
                'nilai_laporan'           => $nilaiLaporan,
                'catatan_guru'            => $lengkap ? 'Laporan disusun dengan baik.' : null,
                'nilai_akhir'             => $nilaiAkhir,
            ]);

            // ---- DOKUMEN (1 baris per siswa) ----
            Dokumen::create([
                'siswa_id'         => $siswa->id,
                'surat_tugas'      => null, // surat tugas bersifat global (diunggah admin)
                'surat_penerimaan' => 'dokumen/contoh_surat_penerimaan.pdf',
                'laporan_akhir'    => $lengkap ? 'dokumen/contoh_laporan_akhir.pdf' : null,
            ]);
        }
    }
}