<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Perusahaan;
use App\Models\PeriodePkl;

/**
 * Seeder DATA DIRI saja.
 *
 * Yang dibuat:
 *   - 1 periode PKL aktif
 *   - 32 industri + nama instruktur (dari kolom "Nama Pimpinan")
 *   - 1 akun admin
 *   - 44 akun guru pembimbing
 *   - 112 akun siswa PKL
 *
 * TIDAK dibuat: jurnal, catatan kegiatan, observasi, absensi, nilai, dokumen.
 * Semua transaksi itu diisi sendiri oleh siswa/guru lewat aplikasi.
 *
 * Sumber data: DAFTAR MURID BIMBINGAN PKL.xlsx (UPTD SMK Negeri 1 Majene, 2026)
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        /* ============================================================
         | 1. PERIODE PKL AKTIF
         ============================================================ */
        $periode = PeriodePkl::create([
            'nama'            => 'PKL Gelombang 1',
            'tahun_ajaran'    => '2026/2027',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_active'       => true,
            'keterangan'      => 'Periode PKL aktif semester ganjil tahun ajaran 2026/2027.',
        ]);

        /* ============================================================
         | 2. INDUSTRI + INSTRUKTUR  (32 industri)
         |    pembimbing_industri = nama instruktur / pimpinan industri
         ============================================================ */
        $perusahaanData = [
            ['nama_perusahaan' => 'LKPS SUKA MAJU', 'alamat' => 'Komplex BTN Leppe Blok M.7 No.12, LEMBANG, Kec. Banggae Timur, Kab. Majene', 'telepon' => '082190952069', 'pembimbing_industri' => 'Amrah Achmad'], // Majene
            ['nama_perusahaan' => 'RUMAH JAHIT ARSYI', 'alamat' => 'Jl. Ahmad Kirang No.51, Labuang, Kabupaten Majene', 'telepon' => '081359480064', 'pembimbing_industri' => 'Arsyi Wahdaniah Mahda, S.Pd.'], // Majene
            ['nama_perusahaan' => 'TEFA UPTD SMKN 1 MAJENE', 'alamat' => 'Jl. K.H.Muh. Saleh No.41, Kel. Labuang Utara, Kec. Banggae Timur, Kab. Majene', 'telepon' => '085255324219', 'pembimbing_industri' => 'Masturah, S.Pd.'], // Majene
            ['nama_perusahaan' => 'KONVEKSI NABHAN', 'alamat' => 'Jl. Poros Majene-Mamuju, Lembang, Majene', 'telepon' => '085340507339', 'pembimbing_industri' => 'Muhammad Fahri'], // Majene
            ['nama_perusahaan' => 'RUMAH JAHIT FATIMA', 'alamat' => 'Jl. Abdul Wahab Anas No. 10, Saleppa', 'telepon' => '085299117820', 'pembimbing_industri' => 'Fatimah Nur'], // Majene
            ['nama_perusahaan' => 'INES GALERI', 'alamat' => 'PAKKOLA (SAMPING BRI PASAR)', 'telepon' => '081352923965', 'pembimbing_industri' => 'Inis Totaktum'], // Majene
            ['nama_perusahaan' => 'JIHAN COLLECTION', 'alamat' => 'Komplek Pasar Sentral Majene', 'telepon' => '081387461096', 'pembimbing_industri' => 'Wahida'], // Majene
            ['nama_perusahaan' => 'BIRU SALON', 'alamat' => 'Pasar Sentral Majene Blok I/04', 'telepon' => '085343874969', 'pembimbing_industri' => 'Nur Rifqah'], // Majene
            ['nama_perusahaan' => 'SALON MUTIARA', 'alamat' => 'Komplek Pasar Sentral Majene', 'telepon' => '082188370300', 'pembimbing_industri' => 'Sitti Nurasiah'], // Majene
            ['nama_perusahaan' => 'RUMAH CANTIK', 'alamat' => 'Perumahan Lutang Blok C/3. Kabupaten Majene', 'telepon' => '085343663703', 'pembimbing_industri' => 'Nurlela Tasrif, S.P., M.Si.'], // Majene
            ['nama_perusahaan' => 'ZEHRA BEAUTY CLINIC', 'alamat' => 'Jl. Poros Majene-Mamuju, Labuang, Kec. Banggae, Kabupaten Majene', 'telepon' => '083853532589', 'pembimbing_industri' => 'Reskia Amelia'], // Majene
            ['nama_perusahaan' => 'JENS CAFE & BILLYARD', 'alamat' => 'Jalan Poros Majene-Mamuju, labuang, Kab. Majene', 'telepon' => '082333133179', 'pembimbing_industri' => 'Syamsuddin'], // Majene
            ['nama_perusahaan' => 'ARRAYA', 'alamat' => 'Jl. Jend. Sudirman No.130 Lembang, Majene', 'telepon' => '082348137321', 'pembimbing_industri' => 'Fifin Suryati, S.E.'], // Majene
            ['nama_perusahaan' => 'ARCI CAFÉ & ROASBERY', 'alamat' => 'Jl. Poros Majene - Mamuju, Baurung Kabupaten Majene', 'telepon' => '082348286069', 'pembimbing_industri' => 'Noerfaim'], // Majene
            ['nama_perusahaan' => 'UNSULBAR', 'alamat' => 'Jl. Prof. Dr. Baharuddin Lopa, S.H, Talumung, Kelurahan Baurung, Kecamatan Banggae Timur, Kabupaten Majene, Sulawesi Barat 91412', 'telepon' => '085342567123', 'pembimbing_industri' => 'Muh. Rafli Rasyid, S.Kom., M.T.'], // Majene
            ['nama_perusahaan' => 'PT. Bank Sulselbar Cab. Majene', 'alamat' => 'Jl. Gatot Subroto No.59, Majene', 'telepon' => '(0422) 21099', 'pembimbing_industri' => 'Imran Tariwowo Rahim'], // Majene
            ['nama_perusahaan' => 'Universitas Terbuka', 'alamat' => 'Jl. Sultan Hasanuddin, Poros Majene-Mamuju', 'telepon' => null, 'pembimbing_industri' => 'Surahmansyah, S.IP., M.M.'], // Majene
            ['nama_perusahaan' => 'BAPENDA', 'alamat' => 'Jalan Gatot Subroto No. 12, Majene, Sulawesi Barat.', 'telepon' => '081355632322', 'pembimbing_industri' => 'Hasri, S.Kom., M.Kom.'], // Majene
            ['nama_perusahaan' => 'Villa Bogor', 'alamat' => 'Jl. Muh. Yamin No.9, Lingk. Leppe, kel. Labuang, Kec. Banggae Timur, Kab. Majene', 'telepon' => '085299831592', 'pembimbing_industri' => 'Faradillah Rizal Putri'], // Majene
            ['nama_perusahaan' => 'KONVEKSI ATZILAH', 'alamat' => 'Kampung Baru, Katitting, Perbatasan Polewali-Majene', 'telepon' => '082347269938', 'pembimbing_industri' => 'Sukirah, S.Pd.'], // Polewali
            ['nama_perusahaan' => 'AMELIA COLLECTION', 'alamat' => 'Jln. Poros Tinambung Katitting, Polewali Mandar, Sulawesi Barat', 'telepon' => '082349786801', 'pembimbing_industri' => 'Jamalia Djabir, S.Pd.'], // Polewali
            ['nama_perusahaan' => 'ZESHA BEAUTY SALON', 'alamat' => 'Jl. Mr. Muh Yamin, Manding, Kabupaten Polewali Mandar', 'telepon' => '081354849922', 'pembimbing_industri' => 'Nur Aeni'], // Polewali
            ['nama_perusahaan' => 'SALON FANI POLEWALI', 'alamat' => 'Jl. Mr. Muh. Yamin, Madatte, Kec. Polewali Mandar, Sulawesi Barat, 91311', 'telepon' => '085386509096', 'pembimbing_industri' => 'Fani Rahma Sari'], // Polewali
            ['nama_perusahaan' => 'MUTMA SALON', 'alamat' => 'BTN Tandung Blok D/5, Tinambung, Polewali', 'telepon' => '085255017344', 'pembimbing_industri' => 'Mutmainnah'], // Polewali
            ['nama_perusahaan' => 'SALON LOLITA', 'alamat' => 'Jl. Ganggawa No.21 Pangkajenne Sidenreng Rappang', 'telepon' => '081145611144', 'pembimbing_industri' => 'Hj. Dahniar'], // Sidrap
            ['nama_perusahaan' => 'SALON MUSLIMAH JAMILAH', 'alamat' => 'Jl. Martadinata Kec. Simboro Kab. Mamuju', 'telepon' => '082346784028', 'pembimbing_industri' => 'Elma'], // Mamuju
            ['nama_perusahaan' => 'SALON FATIKHA', 'alamat' => 'Jl. Pababari, Karema, Kec. Mamuju, Kabupaten Mamuju, Sulawesi Barat 91511', 'telepon' => '08114118810', 'pembimbing_industri' => 'Hj. Andi Fatmawati'], // Mamuju
            ['nama_perusahaan' => 'MATOS MALL DAN HOTEL', 'alamat' => 'Jl. Yos Sudarso No. 37 Binanga, Mamuju, Sulawesi Barat', 'telepon' => '082353425376', 'pembimbing_industri' => 'Arief Budi'], // Mamuju
            ['nama_perusahaan' => 'GRAND MALEO HOTEL MAMUJU', 'alamat' => 'Jl. Yos Sudarso No.51, Binanga, Kecamatan Mamuju, Kabupaten Mamuju, Sulawesi Barat', 'telepon' => '082347001001', 'pembimbing_industri' => 'Arief Budi'], // Mamuju
            ['nama_perusahaan' => 'BUTIK ATHOLYIAH', 'alamat' => 'Jl. Dg. Tata Raya,  Makassar', 'telepon' => '085294444323', 'pembimbing_industri' => 'Rahmi'], // Makassar
            ['nama_perusahaan' => 'SALON MAHKOTA', 'alamat' => 'Jl. Kumala, pa\'baeng-baeng, Kec.Tamalate, Kota Makassar.', 'telepon' => '081242368765', 'pembimbing_industri' => 'H. Sakri, HS.'], // Makassar
            ['nama_perusahaan' => 'SALON PELARIAN YANG MANIS', 'alamat' => 'Mannuruki Raya No.32, Makassar', 'telepon' => '085333468887', 'pembimbing_industri' => 'Dewi Yulianti'], // Makassar
        ];

        $perusahaanMap = [];
        foreach ($perusahaanData as $p) {
            $perusahaanMap[$p['nama_perusahaan']] = Perusahaan::create($p);
        }

        /* ============================================================
         | 3. ADMIN  (login pakai NISN: admin / password123)
         ============================================================ */
        User::create([
            'name'     => 'Admin HKI SMKN 1 Majene',
            'nisn'     => 'admin',
            'nip'      => '198131512505111111',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'no_hp'    => '081200000001',
        ]);

        /* ============================================================
         | 4. GURU PEMBIMBING  (44 guru, login pakai NIP)
         |    NIP asli tidak ada di Excel -> dipakai kode sementara
         |    GP001..GP044. Ganti dengan NIP asli lewat menu admin.
         ============================================================ */
        $guruData = [
            ['name' => 'Adliatun Najdah, S.Pd.', 'nip' => 'GP001', 'no_hp' => '081355500001'],
            ['name' => 'Anjas Nur Amir, S.Pd.', 'nip' => 'GP002', 'no_hp' => '081355500002'],
            ['name' => 'Ashariah, S.Pd., M.Pd.', 'nip' => 'GP003', 'no_hp' => '081355500003'],
            ['name' => 'Asmawati J, S.Pd.', 'nip' => 'GP004', 'no_hp' => '081355500004'],
            ['name' => 'Bahnar, S.Pd.I', 'nip' => 'GP005', 'no_hp' => '081355500005'],
            ['name' => 'Dahlia, S.Pd.', 'nip' => 'GP006', 'no_hp' => '081355500006'],
            ['name' => 'Darmi Hafid, S.Pd.', 'nip' => 'GP007', 'no_hp' => '081355500007'],
            ['name' => 'Darnawati, S.Pd., M.Pd.', 'nip' => 'GP008', 'no_hp' => '081355500008'],
            ['name' => 'Dra. Hj. Nur Hidaya, M.M.', 'nip' => 'GP009', 'no_hp' => '081355500009'],
            ['name' => 'Drs. Andi Zainal T', 'nip' => 'GP010', 'no_hp' => '081355500010'],
            ['name' => 'Fatmawati, S.Pd.', 'nip' => 'GP011', 'no_hp' => '081355500011'],
            ['name' => 'Fitriyani Ganing, S.Pd.', 'nip' => 'GP012', 'no_hp' => '081355500012'],
            ['name' => 'Hijrawati L, S.Pd.', 'nip' => 'GP013', 'no_hp' => '081355500013'],
            ['name' => 'Hikmawati Kardi, S.Pd., Gr.', 'nip' => 'GP014', 'no_hp' => '081355500014'],
            ['name' => 'Junamia Junus, S.Pd.', 'nip' => 'GP015', 'no_hp' => '081355500015'],
            ['name' => 'Kurniah, S.Ag.', 'nip' => 'GP016', 'no_hp' => '081355500016'],
            ['name' => 'M. Asri, Amd.Kom', 'nip' => 'GP017', 'no_hp' => '081355500017'],
            ['name' => 'Maspar, S.Pd., M.Pd.', 'nip' => 'GP018', 'no_hp' => '081355500018'],
            ['name' => 'Masturah, S.Pd.', 'nip' => 'GP019', 'no_hp' => '081355500019'],
            ['name' => 'Masyita, S.Pd.', 'nip' => 'GP020', 'no_hp' => '081355500020'],
            ['name' => 'Muliah H, S.Pd., M.Si.', 'nip' => 'GP021', 'no_hp' => '081355500021'],
            ['name' => 'Musniati, S.Pd.', 'nip' => 'GP022', 'no_hp' => '081355500022'],
            ['name' => 'Musyirifah, S.Pd., M.Pd.', 'nip' => 'GP023', 'no_hp' => '081355500023'],
            ['name' => 'Nasmiah, S.Pd.', 'nip' => 'GP024', 'no_hp' => '081355500024'],
            ['name' => 'Neli Nandriani Ulfatin, S.Pd.', 'nip' => 'GP025', 'no_hp' => '081355500025'],
            ['name' => 'Nur Faiqa Rezkiana., S.Tr.Par', 'nip' => 'GP026', 'no_hp' => '081355500026'],
            ['name' => 'Nurfasirah, S.Pd.', 'nip' => 'GP027', 'no_hp' => '081355500027'],
            ['name' => 'Nurhayati, S.Pd., M.Pd.', 'nip' => 'GP028', 'no_hp' => '081355500028'],
            ['name' => 'Nurhidanah Y, S.Pd.', 'nip' => 'GP029', 'no_hp' => '081355500029'],
            ['name' => 'Nurhidayah, S.Pd.', 'nip' => 'GP030', 'no_hp' => '081355500030'],
            ['name' => 'Nurlina, S.Si., S.Pd., M.Pd.', 'nip' => 'GP031', 'no_hp' => '081355500031'],
            ['name' => 'Nurmi Ningsih, S.Pd., Gr.', 'nip' => 'GP032', 'no_hp' => '081355500032'],
            ['name' => 'Nurpadilah Sukina Sari, S.Tr.Par', 'nip' => 'GP033', 'no_hp' => '081355500033'],
            ['name' => 'Nurul Jihad, S.Pd.', 'nip' => 'GP034', 'no_hp' => '081355500034'],
            ['name' => 'Ramlah Sagal, S.Pd.', 'nip' => 'GP035', 'no_hp' => '081355500035'],
            ['name' => 'Raodah, S.Pd., Gr.', 'nip' => 'GP036', 'no_hp' => '081355500036'],
            ['name' => 'Rosdiana, S.Pd.I., M.Pd., Gr.', 'nip' => 'GP037', 'no_hp' => '081355500037'],
            ['name' => 'Rosmiani, S.Kom.', 'nip' => 'GP038', 'no_hp' => '081355500038'],
            ['name' => 'Sahra, S.Pd.I', 'nip' => 'GP039', 'no_hp' => '081355500039'],
            ['name' => 'Siti A\'isyah J, S.Pd.', 'nip' => 'GP040', 'no_hp' => '081355500040'],
            ['name' => 'Sitti Nasrah, S.Pd., M.Pd.', 'nip' => 'GP041', 'no_hp' => '081355500041'],
            ['name' => 'St. Rakhmaniah, S.Pd.', 'nip' => 'GP042', 'no_hp' => '081355500042'],
            ['name' => 'Syaipul, S.Pd.', 'nip' => 'GP043', 'no_hp' => '081355500043'],
            ['name' => 'Zukhrinab Abdul Kadir, S.Pd.', 'nip' => 'GP044', 'no_hp' => '081355500044'],
        ];

        $guruMap = [];
        foreach ($guruData as $g) {
            $guruMap[$g['nip']] = User::create([
                'name'     => $g['name'],
                'password' => Hash::make('password123'),
                'role'     => 'guru_pembimbing',
                'nip'      => $g['nip'],
                'no_hp'    => $g['no_hp'],
            ]);
        }

        /* ============================================================
         | 5. SISWA PKL  (112 siswa, login pakai NISN = NIS)
         ============================================================ */
        $siswaData = [
            ['name' => 'FAIKA HIJRAH', 'nisn' => '5845', 'jenis_kelamin' => 'P', 'no_hp' => '08120000001', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP036'],
            ['name' => 'AINI', 'nisn' => '5868', 'jenis_kelamin' => 'P', 'no_hp' => '08120000002', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP001'],
            ['name' => 'ELINDA', 'nisn' => '5878', 'jenis_kelamin' => 'P', 'no_hp' => '08120000003', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP012'],
            ['name' => 'MILA', 'nisn' => '5884', 'jenis_kelamin' => 'P', 'no_hp' => '08120000004', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP012'],
            ['name' => 'NADIYA SAPUTRI', 'nisn' => '5885', 'jenis_kelamin' => 'P', 'no_hp' => '08120000005', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP006'],
            ['name' => 'NUR AINA', 'nisn' => '5886', 'jenis_kelamin' => 'P', 'no_hp' => '08120000006', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP006'],
            ['name' => 'NURUL', 'nisn' => '5888', 'jenis_kelamin' => 'P', 'no_hp' => '08120000007', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP006'],
            ['name' => 'SADRIA', 'nisn' => '5890', 'jenis_kelamin' => 'P', 'no_hp' => '08120000008', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => 'GP036'],
            ['name' => 'KARMILA', 'nisn' => '5847', 'jenis_kelamin' => 'P', 'no_hp' => '08120000009', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => 'GP020'],
            ['name' => 'MAGFIRA WARAHMAH', 'nisn' => '5848', 'jenis_kelamin' => 'P', 'no_hp' => '08120000010', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => 'GP020'],
            ['name' => 'MILDA', 'nisn' => '5849', 'jenis_kelamin' => 'P', 'no_hp' => '08120000011', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => 'GP010'],
            ['name' => 'MUTMAINNA', 'nisn' => '5850', 'jenis_kelamin' => 'P', 'no_hp' => '08120000012', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => 'GP003'],
            ['name' => 'NADRIA', 'nisn' => '5851', 'jenis_kelamin' => 'P', 'no_hp' => '08120000013', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => 'GP003'],
            ['name' => 'HERNIATI', 'nisn' => '5880', 'jenis_kelamin' => 'P', 'no_hp' => '08120000014', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => 'GP015'],
            ['name' => 'FITRA WATI', 'nisn' => '5846', 'jenis_kelamin' => 'P', 'no_hp' => '08120000015', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'TEFA UPTD SMKN 1 MAJENE', 'guru_nip' => 'GP016'],
            ['name' => 'NUR INTAN', 'nisn' => '5854', 'jenis_kelamin' => 'P', 'no_hp' => '08120000016', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'TEFA UPTD SMKN 1 MAJENE', 'guru_nip' => 'GP016'],
            ['name' => 'PURNAMA', 'nisn' => '5860', 'jenis_kelamin' => 'P', 'no_hp' => '08120000017', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'TEFA UPTD SMKN 1 MAJENE', 'guru_nip' => 'GP030'],
            ['name' => 'SRI AFNI', 'nisn' => '5863', 'jenis_kelamin' => 'P', 'no_hp' => '08120000018', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'TEFA UPTD SMKN 1 MAJENE', 'guru_nip' => 'GP030'],
            ['name' => 'ZAKIYAH MAULIDYA', 'nisn' => '5867', 'jenis_kelamin' => 'P', 'no_hp' => '08120000019', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'TEFA UPTD SMKN 1 MAJENE', 'guru_nip' => 'GP030'],
            ['name' => 'ANITA APRILIA', 'nisn' => '5843', 'jenis_kelamin' => 'P', 'no_hp' => '08120000020', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => 'GP004'],
            ['name' => 'NURLIAN', 'nisn' => '5855', 'jenis_kelamin' => 'P', 'no_hp' => '08120000021', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => 'GP019'],
            ['name' => 'NUR FADILAH SARI', 'nisn' => '5853', 'jenis_kelamin' => 'P', 'no_hp' => '08120000022', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => 'GP019'],
            ['name' => 'ALMA', 'nisn' => '5842', 'jenis_kelamin' => 'P', 'no_hp' => '08120000023', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => 'GP043'],
            ['name' => 'PRISKA ANDRIANI', 'nisn' => '5859', 'jenis_kelamin' => 'P', 'no_hp' => '08120000024', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => 'GP043'],
            ['name' => 'SITTI AULIA NAFISAH', 'nisn' => '5862', 'jenis_kelamin' => 'P', 'no_hp' => '08120000025', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT FATIMA', 'guru_nip' => 'GP044'],
            ['name' => 'SUKMA', 'nisn' => '5864', 'jenis_kelamin' => 'P', 'no_hp' => '08120000026', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT FATIMA', 'guru_nip' => 'GP044'],
            ['name' => 'YUNITA PUTRI WANDIRA', 'nisn' => '5866', 'jenis_kelamin' => 'P', 'no_hp' => '08120000027', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT FATIMA', 'guru_nip' => 'GP044'],
            ['name' => 'NAILA', 'nisn' => '5852', 'jenis_kelamin' => 'P', 'no_hp' => '08120000028', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => 'GP039'],
            ['name' => 'WANDA SARI', 'nisn' => '5865', 'jenis_kelamin' => 'P', 'no_hp' => '08120000029', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => 'GP039'],
            ['name' => 'ISMAWATI', 'nisn' => '5881', 'jenis_kelamin' => 'P', 'no_hp' => '08120000030', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => 'GP044'],
            ['name' => 'ZALZABILA', 'nisn' => '5894', 'jenis_kelamin' => 'P', 'no_hp' => '08120000031', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => 'GP044'],
            ['name' => 'AL MUNAWAR', 'nisn' => '5869', 'jenis_kelamin' => 'L', 'no_hp' => '08120000032', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'JIHAN COLLECTION', 'guru_nip' => 'GP015'],
            ['name' => 'DINDA', 'nisn' => '5877', 'jenis_kelamin' => 'P', 'no_hp' => '08120000033', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'JIHAN COLLECTION', 'guru_nip' => 'GP016'],
            ['name' => 'SERLI', 'nisn' => '5892', 'jenis_kelamin' => 'P', 'no_hp' => '08120000034', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'JIHAN COLLECTION', 'guru_nip' => 'GP016'],
            ['name' => 'ADRIANI MARSHANDA', 'nisn' => '5896', 'jenis_kelamin' => 'P', 'no_hp' => '08120000035', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => 'GP002'],
            ['name' => 'QALBI AISYAH', 'nisn' => '5974', 'jenis_kelamin' => 'P', 'no_hp' => '08120000036', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => 'GP002'],
            ['name' => 'KHAERUNNISA IDHAM', 'nisn' => '5934', 'jenis_kelamin' => 'P', 'no_hp' => '08120000037', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => 'GP025'],
            ['name' => 'ADILA PUTRI RAMADANI', 'nisn' => '5923', 'jenis_kelamin' => 'P', 'no_hp' => '08120000038', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => 'GP031'],
            ['name' => 'PUTRI ARINI', 'nisn' => '5944', 'jenis_kelamin' => 'P', 'no_hp' => '08120000039', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => 'GP012'],
            ['name' => 'AULIA', 'nisn' => '5927', 'jenis_kelamin' => 'P', 'no_hp' => '08120000040', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUTIARA', 'guru_nip' => 'GP042'],
            ['name' => 'NUR FAIKA', 'nisn' => '5940', 'jenis_kelamin' => 'P', 'no_hp' => '08120000041', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUTIARA', 'guru_nip' => 'GP042'],
            ['name' => 'DIAN ASTUTI', 'nisn' => '5903', 'jenis_kelamin' => 'P', 'no_hp' => '08120000042', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => 'GP022'],
            ['name' => 'MINTARSIH', 'nisn' => '5907', 'jenis_kelamin' => 'P', 'no_hp' => '08120000043', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => 'GP022'],
            ['name' => 'NAILA', 'nisn' => '5908', 'jenis_kelamin' => 'P', 'no_hp' => '08120000044', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => 'GP012'],
            ['name' => 'NURFADILA', 'nisn' => '5914', 'jenis_kelamin' => 'P', 'no_hp' => '08120000045', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => 'GP012'],
            ['name' => 'AL ZAHRA ANUGRAH CAHYANI', 'nisn' => '5925', 'jenis_kelamin' => 'P', 'no_hp' => '08120000046', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZEHRA BEAUTY CLINIC', 'guru_nip' => 'GP030'],
            ['name' => 'DAHLIA', 'nisn' => '5930', 'jenis_kelamin' => 'P', 'no_hp' => '08120000047', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZEHRA BEAUTY CLINIC', 'guru_nip' => 'GP030'],
            ['name' => 'KHAERUNNISA', 'nisn' => '5933', 'jenis_kelamin' => 'P', 'no_hp' => '08120000048', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZEHRA BEAUTY CLINIC', 'guru_nip' => 'GP022'],
            ['name' => 'NUR ALIPAH', 'nisn' => '5785', 'jenis_kelamin' => 'P', 'no_hp' => '08120000049', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'JENS CAFE & BILLYARD', 'guru_nip' => 'GP007'],
            ['name' => 'MULHAMINA', 'nisn' => '5834', 'jenis_kelamin' => 'P', 'no_hp' => '08120000050', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'JENS CAFE & BILLYARD', 'guru_nip' => 'GP007'],
            ['name' => 'INTAN', 'nisn' => '5976', 'jenis_kelamin' => 'P', 'no_hp' => '08120000051', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'ARRAYA', 'guru_nip' => 'GP007'],
            ['name' => 'NOVITA ANASTASYAH', 'nisn' => '5977', 'jenis_kelamin' => 'P', 'no_hp' => '08120000052', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'ARRAYA', 'guru_nip' => 'GP007'],
            ['name' => 'ARIKA RAHMAN', 'nisn' => '5832', 'jenis_kelamin' => 'P', 'no_hp' => '08120000053', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'ARCI CAFÉ & ROASBERY', 'guru_nip' => 'GP026'],
            ['name' => 'KARMILAH', 'nisn' => '5957', 'jenis_kelamin' => 'P', 'no_hp' => '08120000054', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => 'GP023'],
            ['name' => 'AIRA MILASARI', 'nisn' => '5951', 'jenis_kelamin' => 'P', 'no_hp' => '08120000055', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => 'GP029'],
            ['name' => 'ADELIA BELA', 'nisn' => '5949', 'jenis_kelamin' => 'P', 'no_hp' => '08120000056', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => 'GP028'],
            ['name' => 'AMANDA', 'nisn' => '5952', 'jenis_kelamin' => 'P', 'no_hp' => '08120000057', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => 'GP028'],
            ['name' => 'SULAEHA', 'nisn' => '5966', 'jenis_kelamin' => 'P', 'no_hp' => '08120000058', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => 'GP028'],
            ['name' => 'HAIRUL MIZAN', 'nisn' => '5956', 'jenis_kelamin' => 'L', 'no_hp' => '08120000059', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => 'GP028'],
            ['name' => 'LUBNAH AHMAD', 'nisn' => '5958', 'jenis_kelamin' => 'P', 'no_hp' => '08120000060', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'PT. Bank Sulselbar Cab. Majene', 'guru_nip' => 'GP018'],
            ['name' => 'NURUL ARIFKAH H', 'nisn' => '5963', 'jenis_kelamin' => 'P', 'no_hp' => '08120000061', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'PT. Bank Sulselbar Cab. Majene', 'guru_nip' => 'GP038'],
            ['name' => 'AHMAD AFDHAL S', 'nisn' => '5950', 'jenis_kelamin' => 'L', 'no_hp' => '08120000062', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'Universitas Terbuka', 'guru_nip' => 'GP021'],
            ['name' => 'MUHAMMAD IKRAM', 'nisn' => '5960', 'jenis_kelamin' => 'L', 'no_hp' => '08120000063', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'Universitas Terbuka', 'guru_nip' => 'GP004'],
            ['name' => 'ASNITA', 'nisn' => '5955', 'jenis_kelamin' => 'P', 'no_hp' => '08120000064', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'BAPENDA', 'guru_nip' => 'GP017'],
            ['name' => 'NADIA ALFHANY', 'nisn' => '6175', 'jenis_kelamin' => 'P', 'no_hp' => '08120000065', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'BAPENDA', 'guru_nip' => 'GP017'],
            ['name' => 'RINA', 'nisn' => '5965', 'jenis_kelamin' => 'P', 'no_hp' => '08120000066', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'BAPENDA', 'guru_nip' => 'GP017'],
            ['name' => 'MUH. RYZAL', 'nisn' => '5968', 'jenis_kelamin' => 'L', 'no_hp' => '08120000067', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'Villa Bogor', 'guru_nip' => 'GP032'],
            ['name' => 'MUH. IQLAL', 'nisn' => '5808', 'jenis_kelamin' => 'L', 'no_hp' => '08120000068', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'Villa Bogor', 'guru_nip' => 'GP032'],
            ['name' => 'NUR ANNISA', 'nisn' => '5961', 'jenis_kelamin' => 'P', 'no_hp' => '08120000069', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'Villa Bogor', 'guru_nip' => 'GP032'],
            ['name' => 'AINUN CAHYA', 'nisn' => '5841', 'jenis_kelamin' => 'P', 'no_hp' => '08120000070', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => 'GP022'],
            ['name' => 'NURUL HIKMAH', 'nisn' => '5857', 'jenis_kelamin' => 'P', 'no_hp' => '08120000071', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => 'GP041'],
            ['name' => 'SAKINAH', 'nisn' => '5861', 'jenis_kelamin' => 'P', 'no_hp' => '08120000072', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => 'GP041'],
            ['name' => 'AULIA PRATIWI NEDAN', 'nisn' => '5873', 'jenis_kelamin' => 'P', 'no_hp' => '08120000073', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => 'GP034'],
            ['name' => 'AULIA SYAHRANI', 'nisn' => '5874', 'jenis_kelamin' => 'P', 'no_hp' => '08120000074', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => 'GP034'],
            ['name' => 'NURUL APRILIA G', 'nisn' => '5856', 'jenis_kelamin' => 'P', 'no_hp' => '08120000075', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'AMELIA COLLECTION', 'guru_nip' => 'GP008'],
            ['name' => 'NURALISA', 'nisn' => '5887', 'jenis_kelamin' => 'P', 'no_hp' => '08120000076', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'AMELIA COLLECTION', 'guru_nip' => 'GP016'],
            ['name' => 'ANNISA', 'nisn' => '5899', 'jenis_kelamin' => 'P', 'no_hp' => '08120000077', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZESHA BEAUTY SALON', 'guru_nip' => 'GP013'],
            ['name' => 'DIANA SALSABILA', 'nisn' => '5904', 'jenis_kelamin' => 'P', 'no_hp' => '08120000078', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZESHA BEAUTY SALON', 'guru_nip' => 'GP037'],
            ['name' => 'SRI MULYANI AULIA', 'nisn' => '5919', 'jenis_kelamin' => 'P', 'no_hp' => '08120000079', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZESHA BEAUTY SALON', 'guru_nip' => 'GP014'],
            ['name' => 'AWALIA RAMADHANI N', 'nisn' => '5983', 'jenis_kelamin' => 'P', 'no_hp' => '08120000080', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FANI POLEWALI', 'guru_nip' => 'GP014'],
            ['name' => 'NURKALSUM', 'nisn' => '5941', 'jenis_kelamin' => 'P', 'no_hp' => '08120000081', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FANI POLEWALI', 'guru_nip' => 'GP014'],
            ['name' => 'DEWI NINDYA PUTRI', 'nisn' => '5902', 'jenis_kelamin' => 'P', 'no_hp' => '08120000082', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'MUTMA SALON', 'guru_nip' => 'GP016'],
            ['name' => 'AINI RIZKYAT UNNISA', 'nisn' => '5924', 'jenis_kelamin' => 'P', 'no_hp' => '08120000083', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => 'GP009'],
            ['name' => 'YANURA INTAN', 'nisn' => '5947', 'jenis_kelamin' => 'P', 'no_hp' => '08120000084', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => 'GP009'],
            ['name' => 'AMEL AULIA RAMADHANI', 'nisn' => '5926', 'jenis_kelamin' => 'P', 'no_hp' => '08120000085', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => 'GP009'],
            ['name' => 'LUTFHIYYA', 'nisn' => '5935', 'jenis_kelamin' => 'P', 'no_hp' => '08120000086', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => 'GP009'],
            ['name' => 'AISYAH ANNISA PUTRI', 'nisn' => '5897', 'jenis_kelamin' => 'P', 'no_hp' => '08120000087', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUSLIMAH JAMILAH', 'guru_nip' => 'GP033'],
            ['name' => 'AZ ZAHRA PUTRI MAULIA', 'nisn' => '5901', 'jenis_kelamin' => 'P', 'no_hp' => '08120000088', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUSLIMAH JAMILAH', 'guru_nip' => 'GP033'],
            ['name' => 'NUR FELISYAH QUEEN', 'nisn' => '5911', 'jenis_kelamin' => 'P', 'no_hp' => '08120000089', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUSLIMAH JAMILAH', 'guru_nip' => 'GP024'],
            ['name' => 'NUR RAHMA RN', 'nisn' => '5913', 'jenis_kelamin' => 'P', 'no_hp' => '08120000090', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => 'GP039'],
            ['name' => 'BADRIA', 'nisn' => '5929', 'jenis_kelamin' => 'P', 'no_hp' => '08120000091', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => 'GP039'],
            ['name' => 'NUR AINI', 'nisn' => '5938', 'jenis_kelamin' => 'P', 'no_hp' => '08120000092', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => 'GP027'],
            ['name' => 'SRI WAHYUNI', 'nisn' => '5945', 'jenis_kelamin' => 'P', 'no_hp' => '08120000093', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => 'GP027'],
            ['name' => 'FAZA RAYHANA', 'nisn' => '5833', 'jenis_kelamin' => 'P', 'no_hp' => '08120000094', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => 'GP029'],
            ['name' => 'NABILA AZZAHRA', 'nisn' => '5835', 'jenis_kelamin' => 'P', 'no_hp' => '08120000095', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => 'GP029'],
            ['name' => 'NAYZILA ADHWA', 'nisn' => '5836', 'jenis_kelamin' => 'P', 'no_hp' => '08120000096', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => 'GP029'],
            ['name' => 'DEBI AYUMITA', 'nisn' => '5876', 'jenis_kelamin' => 'P', 'no_hp' => '08120000097', 'kelas' => 'XII', 'jurusan' => 'Teknik Komputer dan Jaringan', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => 'GP029'],
            ['name' => 'NURASIFAH', 'nisn' => '5962', 'jenis_kelamin' => 'P', 'no_hp' => '08120000098', 'kelas' => 'XII', 'jurusan' => 'Teknik Komputer dan Jaringan', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => 'GP005'],
            ['name' => 'RAMLAH', 'nisn' => '5964', 'jenis_kelamin' => 'P', 'no_hp' => '08120000099', 'kelas' => 'XII', 'jurusan' => 'Teknik Komputer dan Jaringan', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => 'GP011'],
            ['name' => 'MARFIN', 'nisn' => '5979', 'jenis_kelamin' => 'L', 'no_hp' => '08120000100', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'GRAND MALEO HOTEL MAMUJU', 'guru_nip' => 'GP033'],
            ['name' => 'RIDWAN', 'nisn' => '5839', 'jenis_kelamin' => 'L', 'no_hp' => '08120000101', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'GRAND MALEO HOTEL MAMUJU', 'guru_nip' => 'GP032'],
            ['name' => 'ATRI FANI SIFAAN', 'nisn' => '5872', 'jenis_kelamin' => 'P', 'no_hp' => '08120000102', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => 'GP021'],
            ['name' => 'ANDINI', 'nisn' => '5871', 'jenis_kelamin' => 'P', 'no_hp' => '08120000103', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => 'GP035'],
            ['name' => 'SUCI RAMADANI', 'nisn' => '5893', 'jenis_kelamin' => 'P', 'no_hp' => '08120000104', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => 'GP035'],
            ['name' => 'REPALINA', 'nisn' => '5889', 'jenis_kelamin' => 'P', 'no_hp' => '08120000105', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => 'GP006'],
            ['name' => 'KHAIRUNNISA', 'nisn' => '5883', 'jenis_kelamin' => 'P', 'no_hp' => '08120000106', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => 'GP006'],
            ['name' => 'ANNISA KURRA TAAYUN', 'nisn' => '5900', 'jenis_kelamin' => 'P', 'no_hp' => '08120000107', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => 'GP040'],
            ['name' => 'ILDA RISQI ILYAS', 'nisn' => '5905', 'jenis_kelamin' => 'P', 'no_hp' => '08120000108', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => 'GP040'],
            ['name' => 'INDRY', 'nisn' => '5906', 'jenis_kelamin' => 'P', 'no_hp' => '08120000109', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => 'GP040'],
            ['name' => 'WINARTY', 'nisn' => '5946', 'jenis_kelamin' => 'P', 'no_hp' => '08120000110', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => 'GP009'],
            ['name' => 'ANDHARA MAHADEWI D', 'nisn' => '5898', 'jenis_kelamin' => 'P', 'no_hp' => '08120000111', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON PELARIAN YANG MANIS', 'guru_nip' => 'GP040'],
            ['name' => 'REZKY NUR AMALIAH', 'nisn' => '5916', 'jenis_kelamin' => 'P', 'no_hp' => '08120000112', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON PELARIAN YANG MANIS', 'guru_nip' => 'GP040'],
        ];

        foreach ($siswaData as $row) {
            User::create([
                'name'          => $row['name'],
                'password'      => Hash::make('password123'),
                'role'          => 'siswa_pkl',
                'nisn'          => $row['nisn'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'no_hp'         => $row['no_hp'],
                'status_pkl'    => 'aktif',
                'kelas'         => $row['kelas'],
                'jurusan'       => $row['jurusan'],
                'perusahaan_id' => $perusahaanMap[$row['perusahaan']]->id,
                'guru_id'       => $guruMap[$row['guru_nip']]->id,
                'periode_id'    => $periode->id,
            ]);
        }
    }
}
