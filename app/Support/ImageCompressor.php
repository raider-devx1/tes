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

    /*
    |--------------------------------------------------------------------------
    | FOTO DARI KAMERA (data URL base64)
    |--------------------------------------------------------------------------
    | Absensi siswa tidak lagi memakai <input type="file">. Foto diambil
    | langsung dari kamera HP lewat getUserMedia(), digambar ke <canvas>,
    | lalu dikirim sebagai data URL: "data:image/jpeg;base64,....".
    |
    | Kompresi dilakukan DUA lapis:
    |  1. Di browser (canvas resize + toDataURL kualitas 0.7) -> hemat kuota
    |     internet siswa saat mengirim.
    |  2. Di server (method ini) -> menjamin ukuran & dimensi akhir seragam
    |     walaupun ada yang mengakali payload dari sisi klien.
    */

    /** Batas aman ukuran biner foto kamera setelah di-decode (8 MB). */
    public const MAX_BYTE_KAMERA = 8 * 1024 * 1024;

    /**
     * Validasi data URL kamera: format benar, base64 valid, dan benar-benar gambar.
     * Dipakai pada rule validasi controller sebelum menyimpan.
     */
    public static function validDataUrl(?string $dataUrl): bool
    {
        return self::dataUrlKeBiner($dataUrl) !== null;
    }

    /**
     * Simpan foto kamera (data URL) ke storage dalam bentuk JPEG terkompresi.
     *
     * @param  string|null  $dataUrl  Isi input tersembunyi "foto_kamera".
     * @param  string  $dir   Folder tujuan, mis. 'bukti_fisik/absensi'.
     * @param  string  $disk  Nama disk Laravel.
     * @return string|null    Path relatif hasil simpan, atau null bila gagal.
     */
    public static function storeDataUrl(?string $dataUrl, string $dir, string $disk = 'public'): ?string
    {
        $biner = self::dataUrlKeBiner($dataUrl);

        if ($biner === null) {
            return null;
        }

        // Kompres ulang di server. Bila GD bermasalah, pakai biner asli dari
        // browser (yang sudah dikompres canvas) agar absensi tidak gagal.
        $hasil = self::prosesBiner($biner) ?? $biner;

        try {
            $path = trim($dir, '/') . '/' . Str::random(40) . '.jpg';
            Storage::disk($disk)->put($path, $hasil);

            return $path;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Ubah data URL menjadi biner gambar yang sudah terverifikasi.
     *
     * @return string|null Biner gambar, atau null bila tidak valid.
     */
    public static function dataUrlKeBiner(?string $dataUrl): ?string
    {
        $dataUrl = trim((string) $dataUrl);

        if ($dataUrl === '') {
            return null;
        }

        // Hanya menerima image/jpeg, image/png, image/webp.
        if (! preg_match('#^data:image/(jpe?g|png|webp);base64,(.+)$#is', $dataUrl, $cocok)) {
            return null;
        }

        // Beberapa browser/proxy mengubah "+" menjadi spasi saat POST.
        $base64 = str_replace(' ', '+', preg_replace('/\s+/', '', $cocok[2]));
        $biner  = base64_decode($base64, true);

        if ($biner === false || $biner === '') {
            return null;
        }

        if (strlen($biner) > self::MAX_BYTE_KAMERA) {
            return null;
        }

        // Pastikan isinya benar-benar gambar, bukan berkas lain yang disamarkan.
        $info = @getimagesizefromstring($biner);

        if ($info === false) {
            return null;
        }

        if (! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }

        return $biner;
    }

    /**
     * Perkecil & encode ulang biner gambar ke JPEG.
     * Tidak perlu koreksi EXIF: gambar dari <canvas> selalu sudah lurus.
     *
     * @return string|null Biner JPEG, atau null bila gagal / tidak menguntungkan.
     */
    private static function prosesBiner(string $biner): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null; // Ekstensi GD tidak aktif.
        }

        try {
            $sumber = @imagecreatefromstring($biner);

            if (! $sumber) {
                return null;
            }

            $lebarAsli  = imagesx($sumber);
            $tinggiAsli = imagesy($sumber);

            $skala  = min(1, self::MAX_DIMENSI / max($lebarAsli, $tinggiAsli));
            $lebar  = max(1, (int) round($lebarAsli * $skala));
            $tinggi = max(1, (int) round($tinggiAsli * $skala));

            $tujuan = imagecreatetruecolor($lebar, $tinggi);

            // Latar putih agar PNG transparan tidak menjadi hitam saat ke JPEG.
            $putih = imagecolorallocate($tujuan, 255, 255, 255);
            imagefilledrectangle($tujuan, 0, 0, $lebar, $tinggi, $putih);

            imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $lebar, $tinggi, $lebarAsli, $tinggiAsli);

            ob_start();
            imagejpeg($tujuan, null, self::KUALITAS);
            $hasil = (string) ob_get_clean();

            imagedestroy($sumber);
            imagedestroy($tujuan);

            if ($hasil === '') {
                return null;
            }

            // Bila hasil re-encode malah lebih besar, pakai biner asli saja.
            return strlen($hasil) < strlen($biner) ? $hasil : null;
        } catch (Throwable $e) {
            report($e);

            return null; // Fail-safe: jangan pernah menggagalkan absensi.
        }
    }
}
