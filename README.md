# Sistem Monitoring PKL

Aplikasi berbasis Laravel untuk memonitor Praktik Kerja Lapangan (PKL) dengan
empat peran: **Admin**, **Guru Pembimbing**, **Instruktur Industri**, dan **Siswa PKL**.

## Fitur Utama
- Manajemen master data: siswa, guru pembimbing, instruktur industri, perusahaan, dan periode PKL.
- Jurnal harian siswa (multi unit kerja + dokumentasi foto) dengan alur persetujuan instruktur.
- Catatan kegiatan & lembar observasi (permasalahan/solusi) dengan persetujuan.
- Absensi siswa dan monitoring read-only untuk admin & guru.
- Penilaian: instruktur (soft/hard skill, dsb) + guru (nilai guru & laporan) → nilai akhir berbobot.
- Cetak PDF (jurnal, catatan, observasi, nilai) per siswa maupun semua.
- Import/Export Excel untuk data siswa & guru.
- Riwayat aktivitas (log) dan pengaturan sekolah.

## Kebutuhan Sistem
- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Node.js & NPM (Vite + Tailwind CSS)

## Instalasi

git clone https://github.com/raider-devx1/tes.git
cd tes
composer install
npm install
cp .env.example .env
php artisan key:generate
atur koneksi database di .env, lalu:
php artisan migrate --seed
php artisan storage:link
npm run dev
php artisan serve

