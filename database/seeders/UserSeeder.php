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
        // Set true untuk menaruh SEMUA siswa ke guru pertama (uji pagination "Daftar Siswa Bimbingan")
        $fokusSatuGuru = false;

        /* ============================================================
         | 0. PERIODE PKL
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
            'keterangan'      => 'Periode PKL aktif hasil seeder (data real SMKN 1 Majene 2026).',
        ]);

        /* ============================================================
         | 1. PERUSAHAAN / INDUSTRI (data real dari Data PKL 2026)
         |    pembimbing_industri = nama pimpinan/instruktur industri
         ============================================================ */
        $perusahaanData = [
            ['nama_perusahaan' => 'LKPS SUKA MAJU', 'alamat' => 'Komplex BTN Leppe Blok M.7 No.12, LEMBANG, Kec. Banggae Timur, Kab. Majene', 'telepon' => '082190952069', 'pembimbing_industri' => 'Amrah Achmad', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'RUMAH JAHIT ARSYI', 'alamat' => 'Jl. Ahmad Kirang No.51, Labuang, Kabupaten Majene', 'telepon' => '081359480064', 'pembimbing_industri' => 'Arsyi Wahdaniah Mahda, S.Pd.', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'UPTD SMKN 1 MAJENE', 'alamat' => 'Jl. K.H.Muh. Saleh No.41, Kel. Labuang Utara, Kec. Banggae Timur, Kab. Majene', 'telepon' => '085255324219', 'pembimbing_industri' => 'Sitti Nasrah, S.Pd., M.Pd.', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'KONVEKSI NABHAN', 'alamat' => 'Jl. Poros Majene-Mamuju, Lembang, Majene', 'telepon' => '08210000004', 'pembimbing_industri' => 'Muhammad Fahri', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'RUMAH JAHIT FATIMA', 'alamat' => 'Jl. Abdul Wahab Anas No. 10, Saleppa', 'telepon' => '085299117820', 'pembimbing_industri' => 'Fatimah Nur', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'INES GALERI', 'alamat' => 'PAKKOLA (SAMPING BRI PASAR)', 'telepon' => '081352923965', 'pembimbing_industri' => 'Inis Totaktum', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'JIHAN COLLECTION', 'alamat' => 'Komplek Pasar Sentral Majene', 'telepon' => '081387461096', 'pembimbing_industri' => 'Wahida', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'RUMAH CANTIK', 'alamat' => 'Perumahan Lutang Blok C/3. Kabupaten Majene', 'telepon' => '085343663703', 'pembimbing_industri' => 'Nurlela Tasrif, S.P., M.Si.', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'BIRU SALON', 'alamat' => 'Pasar Sentral Majene Blok I/04', 'telepon' => '085343874969', 'pembimbing_industri' => 'Nur Rifqah', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'SALON MUTIARA', 'alamat' => 'Komplek Pasar Sentral Majene', 'telepon' => '082188370300', 'pembimbing_industri' => 'Sitti Nurasiah', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'MUTMA SALON', 'alamat' => 'BTN Tandung Blok D/5, Tinambung, Polewali', 'telepon' => '085255017344', 'pembimbing_industri' => 'Mutmainnah', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'ZEHRA BEAUTY CLINIC', 'alamat' => 'Jl. Poros Majene-Mamuju, Labuang, Kec. Banggae, Kabupaten Majene', 'telepon' => '083853532589', 'pembimbing_industri' => 'Reskia Amelia', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'JENS CAFE & BILLYARD', 'alamat' => 'Jalan Poros Majene-Mamuju, labuang, Kab. Majene', 'telepon' => '082333133179', 'pembimbing_industri' => 'Syamsuddin', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'ARRAYA', 'alamat' => 'Jl. Jend. Sudirman No.130 Lembang, Majene', 'telepon' => '082348137321', 'pembimbing_industri' => 'Fifin Suryari, S.E.', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'ARCI CAFÉ & ROASBERY', 'alamat' => 'Jl. Poros Majene - Mamuju, Baurung Kabupaten Majene', 'telepon' => '082348286069', 'pembimbing_industri' => 'Noerfaim', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'UNSULBAR', 'alamat' => 'Jl. Prof. Dr. Baharuddin Lopa, S.H, Talumung, Kelurahan Baurung, Kecamatan Banggae Timur, Kabupaten Majene, Sulawesi Barat 91412', 'telepon' => '085342567123', 'pembimbing_industri' => 'Muh. Rafli Rasyid, S.Kom., M.T.', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'PT. Bank Sulselbar Cab. Majene', 'alamat' => 'Jl. Gatot Subroto No.59, Majene', 'telepon' => '(0422) 21099', 'pembimbing_industri' => 'Imran Tariwowo Rahim', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'Universitas Terbuka', 'alamat' => 'Jl. Sultan Hasanuddin, Poros Majene-Mamuju', 'telepon' => '08210000018', 'pembimbing_industri' => 'Surahmansyah, S.IP., M.M.', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'BAPENDA', 'alamat' => 'Jalan Gatot Subroto No. 12, Majene, Sulawesi Barat.', 'telepon' => '081355632322', 'pembimbing_industri' => 'Hasri, S.Kom., M.Kom.', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'Villa Bogor', 'alamat' => 'Jl. Muh. Yamin No.9, Lingk. Leppe, kel. Labuang, Kec. Banggae Timur, Kab. Majene', 'telepon' => '085299831592', 'pembimbing_industri' => 'Faradillah Rizal Putri', 'kota' => 'Majene'],
            ['nama_perusahaan' => 'KONVEKSI ATZILAH', 'alamat' => 'Kampung Baru, Katitting, Perbatasan Polewali-Majene', 'telepon' => '082347269938', 'pembimbing_industri' => 'Sukirah, S.Pd.', 'kota' => 'Polewali'],
            ['nama_perusahaan' => 'AMELIA COLLECTION', 'alamat' => 'Jln. Poros Tinambung Katitting', 'telepon' => '082349786801', 'pembimbing_industri' => 'Jamalia Djabir, S.Pd.', 'kota' => 'Polewali'],
            ['nama_perusahaan' => 'ZESHA BEAUTY SALON', 'alamat' => 'Jl. Mr. Muh Yamin, Manding, Kabupaten Polewali Mandar', 'telepon' => '081354849922', 'pembimbing_industri' => 'Nur Aeni', 'kota' => 'Polewali'],
            ['nama_perusahaan' => 'SALON FANI POLEWALI', 'alamat' => 'Jl. Mr. Muh. Yamin, Madatte, Kec. Polewali Mandar, Sulawesi Barat, 91311', 'telepon' => '085386509096', 'pembimbing_industri' => 'Fani Rahma Sari', 'kota' => 'Polewali'],
            ['nama_perusahaan' => 'SALON LOLITA', 'alamat' => 'Jl. Ganggawa No.21 Pangkajenne Sidenreng Rappang', 'telepon' => '081145611144', 'pembimbing_industri' => 'Hj. Dahniar', 'kota' => 'Sidrap'],
            ['nama_perusahaan' => 'SALON MUSLIMAH JAMILAH', 'alamat' => 'Jl. Martadinata Kec. Simboro Kab. Mamuju', 'telepon' => '082346784028', 'pembimbing_industri' => 'Elma', 'kota' => 'Mamuju'],
            ['nama_perusahaan' => 'SALON FATIKHA', 'alamat' => 'Jl. Pababari, Karema, Kec. Mamuju, Kabupaten Mamuju, Sulawesi Barat 91511', 'telepon' => '08114118810', 'pembimbing_industri' => 'Hj. Andi Fatmawati', 'kota' => 'Mamuju'],
            ['nama_perusahaan' => 'MATOS MALL DAN HOTEL', 'alamat' => 'Jl. Yos Sudarso No. 37 Binanga, Mamuju, Sulawesi Barat', 'telepon' => '082353425376', 'pembimbing_industri' => 'Arief Budi', 'kota' => 'Mamuju'],
            ['nama_perusahaan' => 'GRAND MALEO HOTEL MAMUJU', 'alamat' => 'Jl. Yos Sudarso No.51, Binanga, Kecamatan Mamuju, Kabupaten Mamuju, Sulawesi Barat', 'telepon' => '082347001001', 'pembimbing_industri' => 'Arief Budi', 'kota' => 'Mamuju'],
            ['nama_perusahaan' => 'BUTIK ATHOLYIAH', 'alamat' => 'Jl. Dg. Tata Raya,  Makassar', 'telepon' => '085294444323', 'pembimbing_industri' => 'Rahmi', 'kota' => 'Makassar'],
            ['nama_perusahaan' => 'SALON MAHKOTA', 'alamat' => 'Jl. Kumala, pa\'baeng-baeng, Kec.Tamalate, Kota Makassar.', 'telepon' => '081242368765', 'pembimbing_industri' => 'H. Sakri, HS.', 'kota' => 'Makassar'],
            ['nama_perusahaan' => 'SALON PELARIAN YANG MANIS', 'alamat' => 'Mannuruki Raya No.32, Makassar', 'telepon' => '085333468887', 'pembimbing_industri' => 'Dewi Yulianti', 'kota' => 'Makassar'],
        ];

        $perusahaanMap = [];
        foreach ($perusahaanData as $p) {
            $perusahaanMap[$p['nama_perusahaan']] = Perusahaan::create([
                'nama_perusahaan'     => $p['nama_perusahaan'],
                'alamat'              => $p['alamat'],
                'telepon'             => $p['telepon'],
                'pembimbing_industri' => $p['pembimbing_industri'],
            ]);
        }

        /* ============================================================
         | 2. ADMIN  (login pakai NISN) - TIDAK DIUBAH
         ============================================================ */
        User::create([
            'name'     => 'Admin HKI SMKN 1 Majene',
            'nisn'     => 'admin',                       // <- login admin pakai NISN ini (mis. "admin" / password123)
            'nip'      => '198131512505111111',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'no_hp'    => '081200000001',
        ]);

        /* ============================================================
         | 3. GURU PEMBIMBING (login pakai NIP) - data dummy per jurusan
         ============================================================ */
        $guruData = [
            ['name' => 'Hj. Sitti Rahmawati, S.Pd.', 'nip' => '197803152005012003', 'no_hp' => '081355510001'],
            ['name' => 'Nurhaeda, S.Pd., M.Pd.', 'nip' => '198106202006042010', 'no_hp' => '081355510002'],
            ['name' => 'Andi Kurniawan, S.Pd.', 'nip' => '198504112009011005', 'no_hp' => '081355510003'],
            ['name' => 'Sri Wahyuningsih, S.Pd.', 'nip' => '198709232010012008', 'no_hp' => '081355510004'],
            ['name' => 'Muh. Ilham, S.Pd.', 'nip' => '198302172008011004', 'no_hp' => '081355510005'],
            ['name' => 'Rustan, S.Kom., M.T.', 'nip' => '198611052011011006', 'no_hp' => '081355510006'],
            ['name' => 'Fadhilah Amir, S.Par.', 'nip' => '199001152014042009', 'no_hp' => '081355510007'],
            ['name' => 'Drs. H. Abdul Malik, M.M.', 'nip' => '196805121994031007', 'no_hp' => '081355510008'],
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
        $guruPertama = $guruMap[array_key_first($guruMap)];

        /* ============================================================
         | 5. SISWA PKL (data real) + SEMUA DATA PENDUKUNG - login pakai NISN
         ============================================================ */
        $siswaData = [
            ['name' => 'FAIKA HIJRAH', 'nisn' => '5845', 'jenis_kelamin' => 'P', 'no_hp' => '08120001001', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '197803152005012003'],
            ['name' => 'AINI', 'nisn' => '5868', 'jenis_kelamin' => 'P', 'no_hp' => '08120001002', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '198106202006042010'],
            ['name' => 'ELINDA', 'nisn' => '5878', 'jenis_kelamin' => 'P', 'no_hp' => '08120001003', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '197803152005012003'],
            ['name' => 'MILA', 'nisn' => '5884', 'jenis_kelamin' => 'P', 'no_hp' => '08120001004', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '198106202006042010'],
            ['name' => 'NADIYA SAPUTRI', 'nisn' => '5885', 'jenis_kelamin' => 'P', 'no_hp' => '08120001005', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '197803152005012003'],
            ['name' => 'NUR AINA', 'nisn' => '5886', 'jenis_kelamin' => 'P', 'no_hp' => '08120001006', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '198106202006042010'],
            ['name' => 'NURUL', 'nisn' => '5888', 'jenis_kelamin' => 'P', 'no_hp' => '08120001007', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '197803152005012003'],
            ['name' => 'SADRIA', 'nisn' => '5890', 'jenis_kelamin' => 'P', 'no_hp' => '08120001008', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'LKPS SUKA MAJU', 'guru_nip' => '198106202006042010'],
            ['name' => 'KARMILA', 'nisn' => '5847', 'jenis_kelamin' => 'P', 'no_hp' => '08120001009', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => '197803152005012003'],
            ['name' => 'MAGFIRA WARAHMAH', 'nisn' => '5848', 'jenis_kelamin' => 'P', 'no_hp' => '08120001010', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => '198106202006042010'],
            ['name' => 'MILDA', 'nisn' => '5849', 'jenis_kelamin' => 'P', 'no_hp' => '08120001011', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => '197803152005012003'],
            ['name' => 'MUTMAINNA', 'nisn' => '5850', 'jenis_kelamin' => 'P', 'no_hp' => '08120001012', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => '198106202006042010'],
            ['name' => 'NADRIA', 'nisn' => '5851', 'jenis_kelamin' => 'P', 'no_hp' => '08120001013', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => '197803152005012003'],
            ['name' => 'HERNIATI', 'nisn' => '5880', 'jenis_kelamin' => 'P', 'no_hp' => '08120001014', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT ARSYI', 'guru_nip' => '198106202006042010'],
            ['name' => 'FITRA WATI', 'nisn' => '5846', 'jenis_kelamin' => 'P', 'no_hp' => '08120001015', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'UPTD SMKN 1 MAJENE', 'guru_nip' => '197803152005012003'],
            ['name' => 'NUR INTAN', 'nisn' => '5854', 'jenis_kelamin' => 'P', 'no_hp' => '08120001016', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'UPTD SMKN 1 MAJENE', 'guru_nip' => '198106202006042010'],
            ['name' => 'PURNAMA', 'nisn' => '5860', 'jenis_kelamin' => 'P', 'no_hp' => '08120001017', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'UPTD SMKN 1 MAJENE', 'guru_nip' => '197803152005012003'],
            ['name' => 'SRI AFNI', 'nisn' => '5863', 'jenis_kelamin' => 'P', 'no_hp' => '08120001018', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'UPTD SMKN 1 MAJENE', 'guru_nip' => '198106202006042010'],
            ['name' => 'ZAKIYAH MAULIDYA', 'nisn' => '5867', 'jenis_kelamin' => 'P', 'no_hp' => '08120001019', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'UPTD SMKN 1 MAJENE', 'guru_nip' => '197803152005012003'],
            ['name' => 'ANITA APRILIA', 'nisn' => '5843', 'jenis_kelamin' => 'P', 'no_hp' => '08120001020', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => '198106202006042010'],
            ['name' => 'NURLIAN', 'nisn' => '5855', 'jenis_kelamin' => 'P', 'no_hp' => '08120001021', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => '197803152005012003'],
            ['name' => 'NUR FADILAH SARI', 'nisn' => '5853', 'jenis_kelamin' => 'P', 'no_hp' => '08120001022', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => '198106202006042010'],
            ['name' => 'ALMA', 'nisn' => '5842', 'jenis_kelamin' => 'P', 'no_hp' => '08120001023', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => '197803152005012003'],
            ['name' => 'PRISKA ANDRIANI', 'nisn' => '5859', 'jenis_kelamin' => 'P', 'no_hp' => '08120001024', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI NABHAN', 'guru_nip' => '198106202006042010'],
            ['name' => 'SITTI AULIA NAFISAH', 'nisn' => '5862', 'jenis_kelamin' => 'P', 'no_hp' => '08120001025', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT FATIMA', 'guru_nip' => '197803152005012003'],
            ['name' => 'SUKMA', 'nisn' => '5864', 'jenis_kelamin' => 'P', 'no_hp' => '08120001026', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT FATIMA', 'guru_nip' => '198106202006042010'],
            ['name' => 'YUNITA PUTRI WANDIRA', 'nisn' => '5866', 'jenis_kelamin' => 'P', 'no_hp' => '08120001027', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'RUMAH JAHIT FATIMA', 'guru_nip' => '197803152005012003'],
            ['name' => 'NAILA', 'nisn' => '5852', 'jenis_kelamin' => 'P', 'no_hp' => '08120001028', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => '198106202006042010'],
            ['name' => 'WANDA SARI', 'nisn' => '5865', 'jenis_kelamin' => 'P', 'no_hp' => '08120001029', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => '197803152005012003'],
            ['name' => 'ISMAWATI', 'nisn' => '5881', 'jenis_kelamin' => 'P', 'no_hp' => '08120001030', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => '198106202006042010'],
            ['name' => 'ZALZABILA', 'nisn' => '5894', 'jenis_kelamin' => 'P', 'no_hp' => '08120001031', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'INES GALERI', 'guru_nip' => '197803152005012003'],
            ['name' => 'AL MUNAWAR', 'nisn' => '5869', 'jenis_kelamin' => 'L', 'no_hp' => '08120001032', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'JIHAN COLLECTION', 'guru_nip' => '198106202006042010'],
            ['name' => 'DINDA', 'nisn' => '5877', 'jenis_kelamin' => 'P', 'no_hp' => '08120001033', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'JIHAN COLLECTION', 'guru_nip' => '197803152005012003'],
            ['name' => 'SERLI', 'nisn' => '5892', 'jenis_kelamin' => 'P', 'no_hp' => '08120001034', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'JIHAN COLLECTION', 'guru_nip' => '198106202006042010'],
            ['name' => 'DIAN ASTUTI', 'nisn' => '5903', 'jenis_kelamin' => 'P', 'no_hp' => '08120001035', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => '198504112009011005'],
            ['name' => 'MINTARSIH', 'nisn' => '5907', 'jenis_kelamin' => 'P', 'no_hp' => '08120001036', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => '198709232010012008'],
            ['name' => 'NAILA', 'nisn' => '5908', 'jenis_kelamin' => 'P', 'no_hp' => '08120001037', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => '198504112009011005'],
            ['name' => 'NURFADILA', 'nisn' => '5914', 'jenis_kelamin' => 'P', 'no_hp' => '08120001038', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'RUMAH CANTIK', 'guru_nip' => '198709232010012008'],
            ['name' => 'ADRIANI MARSHANDA', 'nisn' => '5896', 'jenis_kelamin' => 'P', 'no_hp' => '08120001039', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => '198504112009011005'],
            ['name' => 'QALBI AISYAH', 'nisn' => '5974', 'jenis_kelamin' => 'P', 'no_hp' => '08120001040', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => '198709232010012008'],
            ['name' => 'KHAERUNNISA IDHAM', 'nisn' => '5934', 'jenis_kelamin' => 'P', 'no_hp' => '08120001041', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => '198504112009011005'],
            ['name' => 'ADILA PUTRI RAMADANI', 'nisn' => '5923', 'jenis_kelamin' => 'P', 'no_hp' => '08120001042', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => '198709232010012008'],
            ['name' => 'PUTRI ARINI', 'nisn' => '5944', 'jenis_kelamin' => 'P', 'no_hp' => '08120001043', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'BIRU SALON', 'guru_nip' => '198504112009011005'],
            ['name' => 'AULIA', 'nisn' => '5927', 'jenis_kelamin' => 'P', 'no_hp' => '08120001044', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUTIARA', 'guru_nip' => '198709232010012008'],
            ['name' => 'NUR FAIKA', 'nisn' => '5940', 'jenis_kelamin' => 'P', 'no_hp' => '08120001045', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUTIARA', 'guru_nip' => '198504112009011005'],
            ['name' => 'DEWI NINDYA PUTRI', 'nisn' => '5902', 'jenis_kelamin' => 'P', 'no_hp' => '08120001046', 'kelas' => 'XI.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'MUTMA SALON', 'guru_nip' => '198709232010012008'],
            ['name' => 'AL ZAHRA ANUGRAH CAHYANI', 'nisn' => '5925', 'jenis_kelamin' => 'L', 'no_hp' => '08120001047', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZEHRA BEAUTY CLINIC', 'guru_nip' => '198504112009011005'],
            ['name' => 'DAHLIA', 'nisn' => '5930', 'jenis_kelamin' => 'P', 'no_hp' => '08120001048', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZEHRA BEAUTY CLINIC', 'guru_nip' => '198709232010012008'],
            ['name' => 'KHAERUNNISA', 'nisn' => '5933', 'jenis_kelamin' => 'P', 'no_hp' => '08120001049', 'kelas' => 'XII.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZEHRA BEAUTY CLINIC', 'guru_nip' => '198504112009011005'],
            ['name' => 'NUR ALIPAH', 'nisn' => '5785', 'jenis_kelamin' => 'P', 'no_hp' => '08120001050', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'JENS CAFE & BILLYARD', 'guru_nip' => '198302172008011004'],
            ['name' => 'MULHAMINA', 'nisn' => '5834', 'jenis_kelamin' => 'P', 'no_hp' => '08120001051', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'JENS CAFE & BILLYARD', 'guru_nip' => '198302172008011004'],
            ['name' => 'INTAN', 'nisn' => '5976', 'jenis_kelamin' => 'P', 'no_hp' => '08120001052', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'ARRAYA', 'guru_nip' => '198302172008011004'],
            ['name' => 'NOVITA ANASTASYAH', 'nisn' => '5977', 'jenis_kelamin' => 'P', 'no_hp' => '08120001053', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'ARRAYA', 'guru_nip' => '198302172008011004'],
            ['name' => 'ARIKA RAHMAN', 'nisn' => '5832', 'jenis_kelamin' => 'P', 'no_hp' => '08120001054', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'ARCI CAFÉ & ROASBERY', 'guru_nip' => '198302172008011004'],
            ['name' => 'KARMILAH', 'nisn' => '5957', 'jenis_kelamin' => 'P', 'no_hp' => '08120001055', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => '198611052011011006'],
            ['name' => 'AIRA MILASARI', 'nisn' => '5951', 'jenis_kelamin' => 'P', 'no_hp' => '08120001056', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => '198611052011011006'],
            ['name' => 'ADELIA BELA', 'nisn' => '5949', 'jenis_kelamin' => 'P', 'no_hp' => '08120001057', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => '198611052011011006'],
            ['name' => 'AMANDA', 'nisn' => '5952', 'jenis_kelamin' => 'P', 'no_hp' => '08120001058', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => '198611052011011006'],
            ['name' => 'SULAEHA', 'nisn' => '5966', 'jenis_kelamin' => 'P', 'no_hp' => '08120001059', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => '198611052011011006'],
            ['name' => 'HAIRUL MIZAN', 'nisn' => '5956', 'jenis_kelamin' => 'L', 'no_hp' => '08120001060', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'UNSULBAR', 'guru_nip' => '198611052011011006'],
            ['name' => 'RINA', 'nisn' => '5965', 'jenis_kelamin' => 'P', 'no_hp' => '08120001061', 'kelas' => 'XII', 'jurusan' => 'Umum', 'perusahaan' => 'UNSULBAR', 'guru_nip' => '197803152005012003'],
            ['name' => 'LUBNAH AHMAD', 'nisn' => '5958', 'jenis_kelamin' => 'L', 'no_hp' => '08120001062', 'kelas' => 'XII', 'jurusan' => 'Umum', 'perusahaan' => 'PT. Bank Sulselbar Cab. Majene', 'guru_nip' => '198106202006042010'],
            ['name' => 'NURUL ARIFKAH H', 'nisn' => '5963', 'jenis_kelamin' => 'P', 'no_hp' => '08120001063', 'kelas' => 'XII', 'jurusan' => 'Umum', 'perusahaan' => 'PT. Bank Sulselbar Cab. Majene', 'guru_nip' => '197803152005012003'],
            ['name' => 'AHMAD AFDHAL S', 'nisn' => '5950', 'jenis_kelamin' => 'L', 'no_hp' => '08120001064', 'kelas' => 'XII', 'jurusan' => 'Umum', 'perusahaan' => 'Universitas Terbuka', 'guru_nip' => '198106202006042010'],
            ['name' => 'MUHAMMAD IKRAM', 'nisn' => '5960', 'jenis_kelamin' => 'L', 'no_hp' => '08120001065', 'kelas' => 'XII', 'jurusan' => 'Umum', 'perusahaan' => 'Universitas Terbuka', 'guru_nip' => '197803152005012003'],
            ['name' => 'ASNITA', 'nisn' => '5955', 'jenis_kelamin' => 'P', 'no_hp' => '08120001066', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'BAPENDA', 'guru_nip' => '198611052011011006'],
            ['name' => 'NADIA ALFHANY', 'nisn' => '6175', 'jenis_kelamin' => 'P', 'no_hp' => '08120001067', 'kelas' => 'XII', 'jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'perusahaan' => 'BAPENDA', 'guru_nip' => '198611052011011006'],
            ['name' => 'MUH. RYZAL', 'nisn' => '5968', 'jenis_kelamin' => 'L', 'no_hp' => '08120001068', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'Villa Bogor', 'guru_nip' => '199001152014042009'],
            ['name' => 'MUH. IQLAL', 'nisn' => '5808', 'jenis_kelamin' => 'L', 'no_hp' => '08120001069', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'Villa Bogor', 'guru_nip' => '199001152014042009'],
            ['name' => 'NUR ANNISA', 'nisn' => '5961', 'jenis_kelamin' => 'P', 'no_hp' => '08120001070', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'Villa Bogor', 'guru_nip' => '199001152014042009'],
            ['name' => 'AINUN CAHYA', 'nisn' => '5841', 'jenis_kelamin' => 'P', 'no_hp' => '08120001071', 'kelas' => 'XI.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => '197803152005012003'],
            ['name' => 'NURUL HIKMAH', 'nisn' => '5857', 'jenis_kelamin' => 'P', 'no_hp' => '08120001072', 'kelas' => 'XI.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => '198106202006042010'],
            ['name' => 'SAKINAH', 'nisn' => '5861', 'jenis_kelamin' => 'P', 'no_hp' => '08120001073', 'kelas' => 'XI.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => '197803152005012003'],
            ['name' => 'AULIA PRATIWI NEDAN', 'nisn' => '5873', 'jenis_kelamin' => 'P', 'no_hp' => '08120001074', 'kelas' => 'XI.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => '198106202006042010'],
            ['name' => 'AULIA SYAHRANI', 'nisn' => '5874', 'jenis_kelamin' => 'P', 'no_hp' => '08120001075', 'kelas' => 'XI.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'KONVEKSI ATZILAH', 'guru_nip' => '197803152005012003'],
            ['name' => 'NURUL APRILIA G', 'nisn' => '5856', 'jenis_kelamin' => 'P', 'no_hp' => '08120001076', 'kelas' => 'XII.1', 'jurusan' => 'Tata Busana', 'perusahaan' => 'AMELIA COLLECTION', 'guru_nip' => '198106202006042010'],
            ['name' => 'NURALISA', 'nisn' => '5887', 'jenis_kelamin' => 'P', 'no_hp' => '08120001077', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'AMELIA COLLECTION', 'guru_nip' => '197803152005012003'],
            ['name' => 'ANNISA', 'nisn' => '5899', 'jenis_kelamin' => 'P', 'no_hp' => '08120001078', 'kelas' => 'XI.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZESHA BEAUTY SALON', 'guru_nip' => '198709232010012008'],
            ['name' => 'DIANA SALSABILA', 'nisn' => '5904', 'jenis_kelamin' => 'P', 'no_hp' => '08120001079', 'kelas' => 'XI.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZESHA BEAUTY SALON', 'guru_nip' => '198504112009011005'],
            ['name' => 'SRI MULYANI AULIA', 'nisn' => '5919', 'jenis_kelamin' => 'P', 'no_hp' => '08120001080', 'kelas' => 'XI.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'ZESHA BEAUTY SALON', 'guru_nip' => '198709232010012008'],
            ['name' => 'AWALIA RAMADHANI N', 'nisn' => '5983', 'jenis_kelamin' => 'P', 'no_hp' => '08120001081', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FANI POLEWALI', 'guru_nip' => '198504112009011005'],
            ['name' => 'NURKALSUM', 'nisn' => '5941', 'jenis_kelamin' => 'P', 'no_hp' => '08120001082', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FANI POLEWALI', 'guru_nip' => '198709232010012008'],
            ['name' => 'AINI RIZKYAT UNNISA', 'nisn' => '5924', 'jenis_kelamin' => 'P', 'no_hp' => '08120001083', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => '198504112009011005'],
            ['name' => 'YANURA INTAN', 'nisn' => '5947', 'jenis_kelamin' => 'P', 'no_hp' => '08120001084', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => '198709232010012008'],
            ['name' => 'AMEL AULIA RAMADHANI', 'nisn' => '5926', 'jenis_kelamin' => 'P', 'no_hp' => '08120001085', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => '198504112009011005'],
            ['name' => 'LUTFHIYYA', 'nisn' => '5935', 'jenis_kelamin' => 'P', 'no_hp' => '08120001086', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON LOLITA', 'guru_nip' => '198709232010012008'],
            ['name' => 'AISYAH ANNISA PUTRI', 'nisn' => '5897', 'jenis_kelamin' => 'P', 'no_hp' => '08120001087', 'kelas' => 'XI.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUSLIMAH JAMILAH', 'guru_nip' => '198504112009011005'],
            ['name' => 'AZ ZAHRA PUTRI MAULIA', 'nisn' => '5901', 'jenis_kelamin' => 'P', 'no_hp' => '08120001088', 'kelas' => 'XI.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUSLIMAH JAMILAH', 'guru_nip' => '198709232010012008'],
            ['name' => 'NUR FELISYAH QUEEN', 'nisn' => '5911', 'jenis_kelamin' => 'P', 'no_hp' => '08120001089', 'kelas' => 'XI.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MUSLIMAH JAMILAH', 'guru_nip' => '198504112009011005'],
            ['name' => 'NUR RAHMA RN', 'nisn' => '5913', 'jenis_kelamin' => 'P', 'no_hp' => '08120001090', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => '198709232010012008'],
            ['name' => 'BADRIA', 'nisn' => '5929', 'jenis_kelamin' => 'P', 'no_hp' => '08120001091', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => '198504112009011005'],
            ['name' => 'NUR AINI', 'nisn' => '5938', 'jenis_kelamin' => 'P', 'no_hp' => '08120001092', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => '198709232010012008'],
            ['name' => 'SRI WAHYUNI', 'nisn' => '5945', 'jenis_kelamin' => 'P', 'no_hp' => '08120001093', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON FATIKHA', 'guru_nip' => '198504112009011005'],
            ['name' => 'FAZA RAYHANA', 'nisn' => '5833', 'jenis_kelamin' => 'P', 'no_hp' => '08120001094', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => '198302172008011004'],
            ['name' => 'NABILA AZZAHRA', 'nisn' => '5835', 'jenis_kelamin' => 'P', 'no_hp' => '08120001095', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => '198302172008011004'],
            ['name' => 'NAYZILA ADHWA', 'nisn' => '5836', 'jenis_kelamin' => 'P', 'no_hp' => '08120001096', 'kelas' => 'XII', 'jurusan' => 'Kuliner', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => '198302172008011004'],
            ['name' => 'DEBI AYUMITA', 'nisn' => '5876', 'jenis_kelamin' => 'P', 'no_hp' => '08120001097', 'kelas' => 'XII', 'jurusan' => 'Teknik Komputer dan Jaringan', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => '198611052011011006'],
            ['name' => 'NURASIFAH', 'nisn' => '5962', 'jenis_kelamin' => 'P', 'no_hp' => '08120001098', 'kelas' => 'XII', 'jurusan' => 'Teknik Komputer dan Jaringan', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => '198611052011011006'],
            ['name' => 'RAMLAH', 'nisn' => '5964', 'jenis_kelamin' => 'P', 'no_hp' => '08120001099', 'kelas' => 'XII', 'jurusan' => 'Teknik Komputer dan Jaringan', 'perusahaan' => 'MATOS MALL DAN HOTEL', 'guru_nip' => '198611052011011006'],
            ['name' => 'MARFIN', 'nisn' => '5979', 'jenis_kelamin' => 'L', 'no_hp' => '08120001100', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'GRAND MALEO HOTEL MAMUJU', 'guru_nip' => '199001152014042009'],
            ['name' => 'RIDWAN', 'nisn' => '5839', 'jenis_kelamin' => 'L', 'no_hp' => '08120001101', 'kelas' => 'XII', 'jurusan' => 'Perhotelan', 'perusahaan' => 'GRAND MALEO HOTEL MAMUJU', 'guru_nip' => '199001152014042009'],
            ['name' => 'ATRI FANI SIFAAN', 'nisn' => '5872', 'jenis_kelamin' => 'P', 'no_hp' => '08120001102', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => '198106202006042010'],
            ['name' => 'ANDINI', 'nisn' => '5871', 'jenis_kelamin' => 'P', 'no_hp' => '08120001103', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => '197803152005012003'],
            ['name' => 'SUCI RAMADANI', 'nisn' => '5893', 'jenis_kelamin' => 'P', 'no_hp' => '08120001104', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => '198106202006042010'],
            ['name' => 'REPALINA', 'nisn' => '5889', 'jenis_kelamin' => 'P', 'no_hp' => '08120001105', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => '197803152005012003'],
            ['name' => 'KHAIRUNNISA', 'nisn' => '5883', 'jenis_kelamin' => 'P', 'no_hp' => '08120001106', 'kelas' => 'XII.2', 'jurusan' => 'Tata Busana', 'perusahaan' => 'BUTIK ATHOLYIAH', 'guru_nip' => '198106202006042010'],
            ['name' => 'ANNISA KURRA TAAYUN', 'nisn' => '5900', 'jenis_kelamin' => 'P', 'no_hp' => '08120001107', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => '198709232010012008'],
            ['name' => 'ILDA RISQI ILYAS', 'nisn' => '5905', 'jenis_kelamin' => 'P', 'no_hp' => '08120001108', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => '198504112009011005'],
            ['name' => 'INDRY', 'nisn' => '5906', 'jenis_kelamin' => 'P', 'no_hp' => '08120001109', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => '198709232010012008'],
            ['name' => 'WINARTY', 'nisn' => '5946', 'jenis_kelamin' => 'P', 'no_hp' => '08120001110', 'kelas' => 'XI.2', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON MAHKOTA', 'guru_nip' => '198504112009011005'],
            ['name' => 'ANDHARA MAHADEWI D', 'nisn' => '5898', 'jenis_kelamin' => 'P', 'no_hp' => '08120001111', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON PELARIAN YANG MANIS', 'guru_nip' => '198709232010012008'],
            ['name' => 'REZKY NUR AMALIAH', 'nisn' => '5916', 'jenis_kelamin' => 'P', 'no_hp' => '08120001112', 'kelas' => 'XII.1', 'jurusan' => 'Tata Kecantikan Kulit dan Rambut', 'perusahaan' => 'SALON PELARIAN YANG MANIS', 'guru_nip' => '198504112009011005'],
        ];

        $statusJurnal = ['pending', 'disetujui', 'revisi'];
        $statusAbsen  = ['Hadir', 'Hadir', 'Izin', 'Sakit', 'Alpha'];

        $i = 0;
        foreach ($siswaData as $row) {
            $i++;

            $guru  = $fokusSatuGuru ? $guruPertama : ($guruMap[$row['guru_nip']] ?? $guruPertama);
            $pt    = $perusahaanMap[$row['perusahaan']] ?? null;

            // Sebagian kecil siswa ke periode lampau untuk uji filter dropdown periode
            $periode = ($i % 10 === 0) ? $periodeLama : $periodeAktif;

            $siswa = User::create([
                'name'          => $row['name'],
                'password'      => Hash::make('password123'),
                'role'          => 'siswa_pkl',
                'nisn'          => $row['nisn'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'no_hp'         => $row['no_hp'],
                'status_pkl'    => 'aktif',
                'kelas'         => $row['kelas'],
                'jurusan'       => $row['jurusan'],
                'perusahaan_id' => $pt?->id,
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
                    'disetujui_oleh'     => $st === 'pending' ? null : $guru->id,
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
