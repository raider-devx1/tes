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
            'nip' => '198131512505111111',
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
         | 5. 20 SISWA PKL + SEMUA DATA PENDUKUNG — login pakai NISN
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

            // ---- JURNAL (3 entri) ----
            for ($j = 1; $j <= 3; $j++) {
                $st = $statusJurnal[($j - 1) % 3];

                $jurnal = Jurnal::create([
                    'siswa_id'           => $siswa->id,
                    'hari_tanggal'       => now()->subDays($j)->toDateString(),
                    'catatan_instruktur' => $st === 'disetujui' ? 'Kerja bagus.' : ($st === 'revisi' ? 'Mohon diperbaiki.' : null),
                    'status_persetujuan' => $st,
                    'disetujui_oleh'     => $st === 'pending' ? null : $ind['ins']->id,
                ]);

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
                    'is_approved'          => $c === 1,
                ]);
            }

            // ---- OBSERVASI (3 entri) ----
            for ($o = 1; $o <= 3; $o++) {
                $observasi = Observasi::create([
                    'user_id'          => $siswa->id,
                    'guru_id'          => $guru->id,
                    'hari_tanggal'     => now()->subDays($o * 2)->toDateString(),
                    'pekerjaan_projek' => "Observasi projek ke-$o",
                    'foto_dokumentasi' => 'observasi/contoh_dokumentasi.jpg',
                ]);

                for ($p = 1; $p <= $o; $p++) {
                    $observasi->items()->create([
                        'permasalahan' => "Permasalahan poin ke-$p pada observasi ke-$o untuk {$siswa->name}.",
                        'solusi'       => "Solusi poin ke-$p untuk observasi ke-$o.",
                    ]);
                }
            }

            // ---- ABSENSI (5 entri) ----
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

            // ---- NILAI GURU (2/3 lengkap, 1/3 baru dinilai instruktur saja) ----
            $soft = rand(3, 5);
            $hard = rand(3, 5);
            $peng = rand(3, 5);
            $kwu  = rand(3, 5);
            $rata = round(($soft + $hard + $peng + $kwu) / 4, 2);

            $lengkap = ($i % 3 !== 0);

            // Generate data komponen form guru (6 penilaian)
            $skor_soft_skill    = $lengkap ? rand(85, 95) : null;
            $skor_hard_skill    = $lengkap ? rand(85, 95) : null;
            $skor_pengembangan  = $lengkap ? rand(85, 95) : null;
            $skor_kewirausahaan = $lengkap ? rand(85, 95) : null;
            $skor_laporan       = $lengkap ? rand(85, 95) : null;
            $skor_presentasi    = $lengkap ? rand(85, 95) : null;

            $rataGuru = $lengkap ? (($skor_soft_skill + $skor_hard_skill + $skor_pengembangan + $skor_kewirausahaan + $skor_laporan + $skor_presentasi) / 6) : null;

            $nilaiAkhir = null;
            if ($lengkap) {
                $instruktur100 = ($rata / 5) * 100;
                $nilaiAkhir = round(
                    ($instruktur100 * 0.50) + ($rataGuru * 0.20) + ($skor_laporan * 0.30),
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

                // --- Backup Lama ---
                'nilai_guru'              => $rataGuru,
                'nilai_laporan'           => $skor_laporan,

                // --- Komponen Penilaian Guru ---
                'skor_soft_skill'         => $skor_soft_skill,
                'deskripsi_soft_skill'    => $lengkap ? 'Menunjukkan kemampuan komunikasi, kerja sama tim, dan disiplin yang sangat baik.' : null,

                'skor_hard_skill'         => $skor_hard_skill,
                'deskripsi_hard_skill'    => $lengkap ? 'Mampu menerapkan kompetensi keahlian sesuai bidang PKL dengan sangat baik.' : null,

                'skor_pengembangan'       => $skor_pengembangan,
                'deskripsi_pengembangan'  => $lengkap ? 'Cepat memahami keterampilan baru dan beradaptasi mandiri.' : null,

                'skor_kewirausahaan'      => $skor_kewirausahaan,
                'deskripsi_kewirausahaan' => $lengkap ? 'Mampu melihat dan memahami peluang budaya wirausaha.' : null,

                'skor_laporan'            => $skor_laporan,
                'deskripsi_laporan'       => $lengkap ? 'Penulisan laporan rapi, tata bahasa baku dan mudah dipahami.' : null,

                'skor_presentasi'         => $skor_presentasi,
                'deskripsi_presentasi'    => $lengkap ? 'Materi presentasi disampaikan dengan sangat lugas dan profesional.' : null,

                'catatan_guru'            => $lengkap ? 'SANGAT BAIK. Terus pertahankan dan tingkatkan kemampuan secara konsisten.' : null,

                'nilai_akhir'             => $nilaiAkhir,
            ]);

            // ---- DOKUMEN ----
            Dokumen::create([
                'siswa_id'         => $siswa->id,
                'surat_tugas'      => null,
                'surat_penerimaan' => 'dokumen/contoh_surat_penerimaan.pdf',
                'laporan_akhir'    => $lengkap ? 'dokumen/contoh_laporan_akhir.pdf' : null,
            ]);
        }
    }
}