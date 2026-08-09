<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
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

    /* ==================================================================
     * TANDA TANGAN HASIL UNGGAH BERKAS (foto / hasil pindai)
     * ==================================================================
     * Dipakai pada penilaian PKL: guru mengunggah foto tanda tangan
     * instruktur dan tanda tangannya sendiri, lalu gambar itu ditempel
     * otomatis ke kolom tanda tangan pada hasil cetak PDF.
     */

    /** Batas ukuran berkas unggahan setelah dibaca (byte). */
    public const MAKS_BYTE_UNGGAH = 3145728; // 3 MB

    /** Kotak penyimpanan hasil rapikan (px). Cukup tajam untuk dicetak. */
    public const MAKS_LEBAR_UNGGAH  = 900;
    public const MAKS_TINGGI_UNGGAH = 340;

    /** Piksel dianggap "tinta" bila kecerahannya di bawah nilai ini (0-255). */
    public const AMBANG_TINTA = 205;

    /** Kotak tanda tangan pada PDF (px @96dpi) - kira-kira 4,5 cm x 1,6 cm. */
    public const CETAK_LEBAR  = 170.0;
    public const CETAK_TINGGI = 60.0;

    /** Batas pembesaran supaya gambar mungil tidak jadi pecah saat dicetak. */
    public const MAKS_PEMBESARAN = 3.0;

    /**
     * Simpan tanda tangan yang diunggah sebagai berkas gambar.
     *
     * Alurnya: baca -> pastikan benar gambar -> ratakan ke putih ->
     * pangkas margin kosong -> perkecil -> simpan PNG.
     *
     * @param  string  $dir  Folder tujuan, mis. 'ttd/nilai/instruktur'.
     * @return string|null   Path relatif, atau null bila berkas tidak sah.
     */
    public static function simpanUnggahan(?UploadedFile $berkas, string $dir, string $disk = 'public'): ?string
    {
        if (! $berkas instanceof UploadedFile || ! $berkas->isValid()) {
            return null;
        }

        try {
            $biner = @file_get_contents($berkas->getRealPath());
        } catch (Throwable $e) {
            return null;
        }

        if (! is_string($biner) || $biner === '' || strlen($biner) > self::MAKS_BYTE_UNGGAH) {
            return null;
        }

        // Pastikan benar-benar gambar (bukan berkas lain yang disamarkan).
        $info = @getimagesizefromstring($biner);
        if (! $info || ! in_array($info['mime'] ?? '', ['image/png', 'image/jpeg'], true)) {
            return null;
        }

        $rapi = self::rapikanUnggahan($biner);

        if ($rapi !== null) {
            $biner    = $rapi;
            $ekstensi = 'png';
        } else {
            // GD mati atau gambar aneh: simpan apa adanya supaya penyimpanan
            // nilai oleh guru tidak pernah gagal hanya karena proses gambar.
            $ekstensi = ($info['mime'] ?? '') === 'image/jpeg' ? 'jpg' : 'png';
        }

        $path = trim($dir, '/') . '/' . Str::random(40) . '.' . $ekstensi;

        try {
            Storage::disk($disk)->put($path, $biner);
        } catch (Throwable $e) {
            return null;
        }

        return $path;
    }

    /**
     * Siapkan gambar tanda tangan untuk dicetak pada PDF.
     *
     * Lebar & tinggi dihitung di sini (bukan diserahkan ke CSS) karena
     * DomPDF tidak menangani max-width/max-height pada <img> dengan andal.
     * Dengan cara ini semua tanda tangan tampil pada ukuran yang seragam:
     * gambar melebar mentok di lebar kotak, gambar menjulang mentok di
     * tingginya, dan perbandingan sisinya tidak pernah berubah.
     *
     * @return array{src:string,lebar:int,tinggi:int}|null
     */
    public static function ukuranCetak(
        ?string $path,
        float $maksLebar = self::CETAK_LEBAR,
        float $maksTinggi = self::CETAK_TINGGI,
        string $disk = 'public'
    ): ?array {
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

        $info   = @getimagesizefromstring($biner);
        $mime   = $info['mime'] ?? 'image/png';
        $lebar  = (int) ($info[0] ?? 0);
        $tinggi = (int) ($info[1] ?? 0);

        // Base64 ditanam langsung supaya DomPDF tidak bergantung pada symlink
        // public/storage maupun izin baca folder di shared hosting.
        $src = 'data:' . $mime . ';base64,' . base64_encode($biner);

        if ($lebar < 1 || $tinggi < 1) {
            return [
                'src'    => $src,
                'lebar'  => (int) round($maksLebar),
                'tinggi' => (int) round($maksTinggi),
            ];
        }

        $skala = min($maksLebar / $lebar, $maksTinggi / $tinggi);
        $skala = min($skala, self::MAKS_PEMBESARAN);

        return [
            'src'    => $src,
            'lebar'  => max(1, (int) round($lebar * $skala)),
            'tinggi' => max(1, (int) round($tinggi * $skala)),
        ];
    }

    /** Ratakan ke putih, pangkas margin kosong, lalu perkecil ke kotak simpan. */
    private static function rapikanUnggahan(string $biner): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null; // GD tidak aktif -> pakai biner asli.
        }

        $gambar = null;

        try {
            $gambar = @imagecreatefromstring($biner);
            if (! $gambar) {
                return null;
            }

            $lebar  = imagesx($gambar);
            $tinggi = imagesy($gambar);

            // 1) Perkecil dulu bila fotonya raksasa. Pemindaian piksel jadi
            //    ringan dan aman untuk memori shared hosting.
            $batasPindai = 1200;
            if (max($lebar, $tinggi) > $batasPindai) {
                $skala = $batasPindai / max($lebar, $tinggi);
                $kecil = self::ubahUkuran($gambar, (int) round($lebar * $skala), (int) round($tinggi * $skala));
                if ($kecil) {
                    imagedestroy($gambar);
                    $gambar = $kecil;
                    $lebar  = imagesx($gambar);
                    $tinggi = imagesy($gambar);
                }
            }

            // 2) Ratakan latar transparan (PNG) menjadi putih.
            $rata  = imagecreatetruecolor($lebar, $tinggi);
            $putih = imagecolorallocate($rata, 255, 255, 255);
            imagefilledrectangle($rata, 0, 0, $lebar, $tinggi, $putih);
            imagecopy($rata, $gambar, 0, 0, 0, 0, $lebar, $tinggi);
            imagedestroy($gambar);
            $gambar = $rata;

            // 3) Buang area putih di sekeliling coretan supaya tanda tangan
            //    selalu memenuhi bingkainya. Tanpa langkah ini, foto yang
            //    banyak ruang kosongnya akan tercetak sangat kecil.
            $kotak = self::batasTinta($gambar);
            if ($kotak !== null) {
                $potong = imagecreatetruecolor($kotak['w'], $kotak['h']);
                $putih2 = imagecolorallocate($potong, 255, 255, 255);
                imagefilledrectangle($potong, 0, 0, $kotak['w'], $kotak['h'], $putih2);
                imagecopy($potong, $gambar, 0, 0, $kotak['x'], $kotak['y'], $kotak['w'], $kotak['h']);
                imagedestroy($gambar);
                $gambar = $potong;
                $lebar  = $kotak['w'];
                $tinggi = $kotak['h'];
            }

            // 4) Turunkan ke kotak simpan bila masih kebesaran.
            $skalaSimpan = min(self::MAKS_LEBAR_UNGGAH / $lebar, self::MAKS_TINGGI_UNGGAH / $tinggi, 1);
            if ($skalaSimpan < 1) {
                $akhir = self::ubahUkuran($gambar, (int) round($lebar * $skalaSimpan), (int) round($tinggi * $skalaSimpan));
                if ($akhir) {
                    imagedestroy($gambar);
                    $gambar = $akhir;
                }
            }

            ob_start();
            imagepng($gambar, null, 9);
            $hasil = ob_get_clean();

            imagedestroy($gambar);
            $gambar = null;

            return $hasil !== false && $hasil !== '' ? $hasil : null;
        } catch (Throwable $e) {
            if ($gambar) {
                @imagedestroy($gambar);
            }

            return null;
        }
    }

    /** Perkecil / perbesar gambar dengan penghalusan, latar putih. */
    private static function ubahUkuran($gambar, int $lebarBaru, int $tinggiBaru)
    {
        $lebarBaru  = max(1, $lebarBaru);
        $tinggiBaru = max(1, $tinggiBaru);

        $kanvas = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        $putih  = imagecolorallocate($kanvas, 255, 255, 255);
        imagefilledrectangle($kanvas, 0, 0, $lebarBaru, $tinggiBaru, $putih);

        $ok = imagecopyresampled(
            $kanvas,
            $gambar,
            0,
            0,
            0,
            0,
            $lebarBaru,
            $tinggiBaru,
            imagesx($gambar),
            imagesy($gambar)
        );

        if (! $ok) {
            imagedestroy($kanvas);

            return null;
        }

        return $kanvas;
    }

    /**
     * Cari kotak terkecil yang masih memuat seluruh coretan tinta.
     *
     * Mengembalikan null bila tidak ada tinta terdeteksi atau hasilnya
     * mencurigakan; dalam kondisi itu gambar sengaja dibiarkan utuh supaya
     * tidak ada tanda tangan yang terpotong.
     *
     * @return array{x:int,y:int,w:int,h:int}|null
     */
    private static function batasTinta($gambar): ?array
    {
        $lebar  = imagesx($gambar);
        $tinggi = imagesy($gambar);

        if ($lebar < 2 || $tinggi < 2) {
            return null;
        }

        // Sampling: cukup periksa sebagian piksel. Tanda tangan berupa coretan
        // tebal sehingga tidak mungkin terlewat, tetapi jauh lebih cepat.
        $langkah = max(1, (int) floor(max($lebar, $tinggi) / 600));

        $minX  = $lebar;
        $minY  = $tinggi;
        $maksX = -1;
        $maksY = -1;

        for ($y = 0; $y < $tinggi; $y += $langkah) {
            for ($x = 0; $x < $lebar; $x += $langkah) {
                $rgb = imagecolorat($gambar, $x, $y);
                $r   = ($rgb >> 16) & 0xFF;
                $g   = ($rgb >> 8) & 0xFF;
                $b   = $rgb & 0xFF;

                // Kecerahan dengan pembobotan mata manusia.
                if ((0.299 * $r + 0.587 * $g + 0.114 * $b) >= self::AMBANG_TINTA) {
                    continue;
                }

                if ($x < $minX) {
                    $minX = $x;
                }
                if ($x > $maksX) {
                    $maksX = $x;
                }
                if ($y < $minY) {
                    $minY = $y;
                }
                if ($y > $maksY) {
                    $maksY = $y;
                }
            }
        }

        // Tidak ada tinta sama sekali (kertas polos / foto kelewat terang).
        if ($maksX < 0 || $maksY < 0) {
            return null;
        }

        // Sisakan sedikit napas di sekeliling coretan.
        $napas = (int) round(max($lebar, $tinggi) * 0.02) + $langkah;

        $minX  = max(0, $minX - $napas);
        $minY  = max(0, $minY - $napas);
        $maksX = min($lebar - 1, $maksX + $napas);
        $maksY = min($tinggi - 1, $maksY + $napas);

        $w = $maksX - $minX + 1;
        $h = $maksY - $minY + 1;

        // Hasil terlalu mungil -> kemungkinan salah deteksi (noda / debu).
        if ($w < 20 || $h < 10) {
            return null;
        }

        // Nyaris sama dengan gambar asli (mis. foto berlatar gelap) -> tidak
        // perlu dipangkas sama sekali.
        if ($w >= $lebar * 0.98 && $h >= $tinggi * 0.98) {
            return null;
        }

        return ['x' => $minX, 'y' => $minY, 'w' => $w, 'h' => $h];
    }

    /**
     * Salin berkas tanda tangan yang sudah ada ke folder lain.
     *
     * Dipakai saat guru memilih "pakai tanda tangan tersimpan" ketika menilai.
     * Sengaja MENYALIN, bukan sekadar menunjuk path yang sama, supaya:
     *  - penilaian yang sudah disimpan tidak ikut berubah bila guru nanti
     *    mengganti tanda tangan tersimpannya, dan
     *  - menghapus tanda tangan di satu penilaian tidak merusak penilaian lain
     *    maupun tanda tangan tersimpan milik guru.
     *
     * Berkas sumber sudah dirapikan saat pertama kali diunggah, jadi di sini
     * cukup disalin apa adanya tanpa diproses ulang.
     *
     * @param  string|null  $sumber  Path berkas asal pada disk yang sama.
     * @param  string       $dir     Folder tujuan, mis. 'ttd/nilai/pembimbing'.
     * @return string|null           Path salinan, atau null bila gagal.
     */
    public static function salin(?string $sumber, string $dir, string $disk = 'public'): ?string
    {
        if (! is_string($sumber) || trim($sumber) === '') {
            return null;
        }

        try {
            if (! Storage::disk($disk)->exists($sumber)) {
                return null;
            }

            $biner = Storage::disk($disk)->get($sumber);
        } catch (Throwable $e) {
            return null;
        }

        if (! is_string($biner) || $biner === '') {
            return null;
        }

        // Pertahankan ekstensi aslinya; PNG dipakai sebagai cadangan yang aman.
        $ekstensi = strtolower((string) pathinfo($sumber, PATHINFO_EXTENSION));
        if (! in_array($ekstensi, ['png', 'jpg', 'jpeg'], true)) {
            $ekstensi = 'png';
        }

        $path = trim($dir, '/') . '/' . Str::random(40) . '.' . $ekstensi;

        try {
            Storage::disk($disk)->put($path, $biner);
        } catch (Throwable $e) {
            return null;
        }

        return $path;
    }
}
