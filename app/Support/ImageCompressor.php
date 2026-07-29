<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Kompresi & perkecil foto sebelum disimpan ke storage.
 *
 * Alasan: siswa PKL mengunggah langsung dari kamera HP (2-3 MB per foto).
 * Tanpa kompresi, halaman monitoring guru memuat puluhan foto ukuran penuh
 * sekaligus -> boros kuota dan berat di HP kelas bawah.
 *
 * Memakai ekstensi GD (aktif secara default di hampir semua shared hosting,
 * termasuk Namecheap). Tidak butuh paket Composer tambahan.
 *
 * Fail-safe: bila GD tidak aktif, format tidak didukung, atau terjadi error
 * apa pun, file tetap disimpan apa adanya. Upload tidak boleh gagal hanya
 * karena proses kompresi bermasalah.
 */
class ImageCompressor
{
    /** Sisi terpanjang maksimum (px). Cukup untuk dibaca layar & dicetak di PDF. */
    public const MAX_DIMENSI = 1280;

    /** Kualitas JPEG hasil kompresi (1-100). 72 = hemat tapi masih tajam. */
    public const KUALITAS = 72;

    /**
     * Simpan foto hasil kompresi, kembalikan path relatifnya.
     * Pengganti langsung dari: $request->file('x')->store($dir, 'public').
     *
     * @param  UploadedFile|null  $file  File dari request.
     * @param  string  $dir   Folder tujuan, mis. 'bukti_fisik/jurnal'.
     * @param  string  $disk  Nama disk Laravel.
     * @return string|null    Path relatif, atau null bila tidak ada file.
     */
    public static function store(?UploadedFile $file, string $dir, string $disk = 'public'): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        // Bukan gambar (mis. PDF) -> simpan normal tanpa diproses.
        if (! Str::startsWith((string) $file->getMimeType(), 'image/')) {
            return $file->store($dir, $disk);
        }

        $hasil = self::proses($file);

        // Kompresi gagal atau tidak menguntungkan -> pakai file asli.
        if ($hasil === null) {
            return $file->store($dir, $disk);
        }

        $path = trim($dir, '/') . '/' . Str::random(40) . '.jpg';
        Storage::disk($disk)->put($path, $hasil);

        return $path;
    }

    /**
     * Proses gambar: luruskan sesuai EXIF, perkecil, lalu encode ulang ke JPEG.
     *
     * @return string|null Biner JPEG, atau null bila gagal / tidak lebih kecil.
     */
    private static function proses(UploadedFile $file): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null; // Ekstensi GD tidak aktif.
        }

        try {
            $path = $file->getRealPath();
            $info = @getimagesize($path);

            if ($info === false) {
                return null;
            }

            [$lebarAsli, $tinggiAsli] = $info;

            $sumber = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
                IMAGETYPE_PNG  => @imagecreatefrompng($path),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
                default        => false,
            };

            if (! $sumber) {
                return null;
            }

            // Foto HP sering tersimpan miring; luruskan berdasarkan EXIF.
            $sumber = self::luruskan($sumber, $path, $info[2]);

            // Hitung dimensi target (jangan pernah memperbesar gambar kecil).
            $skala  = min(1, self::MAX_DIMENSI / max($lebarAsli, $tinggiAsli));
            $lebar  = max(1, (int) round(imagesx($sumber) * $skala));
            $tinggi = max(1, (int) round(imagesy($sumber) * $skala));

            $tujuan = imagecreatetruecolor($lebar, $tinggi);

            // PNG transparan -> beri latar putih agar tidak menjadi hitam saat ke JPEG.
            $putih = imagecolorallocate($tujuan, 255, 255, 255);
            imagefilledrectangle($tujuan, 0, 0, $lebar, $tinggi, $putih);

            imagecopyresampled(
                $tujuan,
                $sumber,
                0,
                0,
                0,
                0,
                $lebar,
                $tinggi,
                imagesx($sumber),
                imagesy($sumber)
            );

            ob_start();
            imagejpeg($tujuan, null, self::KUALITAS);
            $biner = (string) ob_get_clean();

            imagedestroy($sumber);
            imagedestroy($tujuan);

            // Kalau hasil malah lebih besar (gambar kecil ter-reencode), buang saja.
            if ($biner === '' || strlen($biner) >= $file->getSize()) {
                return null;
            }

            return $biner;
        } catch (Throwable $e) {
            report($e);

            return null; // Fail-safe: jangan pernah menggagalkan upload.
        }
    }

    /** Putar gambar mengikuti metadata EXIF Orientation (khusus JPEG). */
    private static function luruskan(GdImage $gambar, string $path, int $tipe): GdImage
    {
        if ($tipe !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $gambar;
        }

        $exif = @exif_read_data($path);
        $arah = $exif['Orientation'] ?? null;

        $derajat = match ($arah) {
            3       => 180,
            6       => -90,
            8       => 90,
            default => 0,
        };

        if ($derajat === 0) {
            return $gambar;
        }

        $diputar = imagerotate($gambar, $derajat, 0);

        return $diputar ?: $gambar;
    }
}
