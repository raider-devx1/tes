<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Tanda tangan digital (paraf) instruktur.
 *
 * Kanvas di browser mengirim gambar dalam bentuk data URL
 * ("data:image/png;base64,...."). Kelas ini yang memvalidasi, menormalkan,
 * menyimpan, dan menyiapkannya kembali untuk dicetak ke PDF.
 *
 * Catatan shared hosting (Niagahoster):
 * - Hanya butuh ekstensi GD yang sudah aktif secara default. Bila GD mati,
 *   gambar tetap disimpan apa adanya (fail-safe) sehingga pengajuan siswa
 *   tidak pernah gagal hanya karena proses gambar.
 * - Ukuran berkas hasil tanda tangan biasanya 5-40 KB, jauh lebih hemat
 *   daripada foto kamera HP 2-3 MB.
 */
class TandaTangan
{
    /** Batas aman ukuran gambar hasil dekode (byte). */
    public const MAKS_BYTE = 1500000; // ~1,5 MB

    /** Lebar maksimum yang disimpan (px). Cukup tajam untuk dicetak di PDF. */
    public const MAKS_LEBAR = 900;

    /**
     * Ubah data URL kanvas menjadi biner gambar.
     * Mengembalikan null bila bukan gambar PNG/JPEG yang sah.
     */
    public static function dekode(?string $dataUrl): ?string
    {
        if (! is_string($dataUrl) || trim($dataUrl) === '') {
            return null;
        }

        if (! preg_match('#^data:image/(png|jpe?g);base64,#i', $dataUrl)) {
            return null;
        }

        $base64 = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $base64 = str_replace(' ', '+', trim((string) $base64));
        $biner  = base64_decode($base64, true);

        if ($biner === false || $biner === '' || strlen($biner) > self::MAKS_BYTE) {
            return null;
        }

        // Pastikan benar-benar gambar (bukan berkas lain yang disamarkan).
        $info = @getimagesizefromstring($biner);
        if (! $info || ! in_array($info['mime'] ?? '', ['image/png', 'image/jpeg'], true)) {
            return null;
        }

        return $biner;
    }

    /** Validasi cepat untuk dipakai di aturan validasi controller. */
    public static function valid(?string $dataUrl): bool
    {
        return self::dekode($dataUrl) !== null;
    }

    /**
     * Simpan tanda tangan ke storage, kembalikan path relatifnya.
     *
     * @param  string  $dir  Folder tujuan, mis. 'ttd/jurnal'.
     * @return string|null   Path relatif (mis. ttd/jurnal/xxx.png) atau null bila gagal.
     */
    public static function simpan(?string $dataUrl, string $dir, string $disk = 'public'): ?string
    {
        $biner = self::dekode($dataUrl);
        if ($biner === null) {
            return null;
        }

        // Ratakan ke latar putih + perkecil bila terlalu lebar.
        $biner = self::rapikan($biner) ?? $biner;

        $path = trim($dir, '/') . '/' . Str::random(40) . '.png';

        try {
            Storage::disk($disk)->put($path, $biner);
        } catch (Throwable $e) {
            return null;
        }

        return $path;
    }

    /** Hapus berkas tanda tangan (dipakai saat pengajuan ulang / data dihapus). */
    public static function hapus(?string $path, string $disk = 'public'): void
    {
        if (! $path) {
            return;
        }

        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        } catch (Throwable $e) {
            // Diabaikan: gagal hapus berkas tidak boleh menggagalkan aksi utama.
        }
    }

    /**
     * Data URI base64 untuk <img> di dalam PDF (DomPDF).
     *
     * Dipakai di view PDF supaya gambar tidak bergantung pada symlink
     * public/storage maupun izin baca folder di shared hosting.
     */
    public static function dataUri(?string $path, string $disk = 'public'): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            if (! Storage::disk($disk)->exists($path)) {
                return null;
            }
            $biner = Storage::disk($disk)->get($path);
        } catch (Throwable $e) {
            return null;
        }

        if (! $biner) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($biner);
    }

    /** Ratakan latar transparan menjadi putih + perkecil bila kelebaran. */
    private static function rapikan(string $biner): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null; // GD tidak aktif -> pakai biner asli.
        }

        try {
            $asal = @imagecreatefromstring($biner);
            if (! $asal) {
                return null;
            }

            $lebar  = imagesx($asal);
            $tinggi = imagesy($asal);
            $skala  = $lebar > self::MAKS_LEBAR ? self::MAKS_LEBAR / $lebar : 1;

            $lebarBaru  = max(1, (int) round($lebar * $skala));
            $tinggiBaru = max(1, (int) round($tinggi * $skala));

            $kanvas = imagecreatetruecolor($lebarBaru, $tinggiBaru);
            $putih  = imagecolorallocate($kanvas, 255, 255, 255);
            imagefilledrectangle($kanvas, 0, 0, $lebarBaru, $tinggiBaru, $putih);
            imagecopyresampled($kanvas, $asal, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);

            ob_start();
            imagepng($kanvas, null, 9);
            $hasil = ob_get_clean();

            imagedestroy($asal);
            imagedestroy($kanvas);

            return $hasil !== false && $hasil !== '' ? $hasil : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
