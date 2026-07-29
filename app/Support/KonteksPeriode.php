<?php

namespace App\Support;

use App\Models\PeriodePkl;

/**
 * SUMBER KEBENARAN TUNGGAL: "periode PKL mana yang sedang dilihat?"
 *
 * Sebelum kelas ini ada, setiap controller memutuskan sendiri -- atau justru
 * tidak memutuskan sama sekali -- angkatan mana yang ditampilkan. Akibatnya
 * di tahun ketiga daftar Monitoring, Evaluasi, dan Dokumen menampilkan
 * gabungan tiga angkatan sekaligus.
 *
 * Sekarang seluruh aplikasi bertanya ke satu tempat yang sama:
 *
 *     KonteksPeriode::id()
 *
 * Dipakai lewat dua scope yang saling terhubung:
 *   - User::siswaBerjalan()      -> siswa angkatan berjalan, bukan arsip
 *   - Jurnal::periodeBerjalan()  -> transaksi angkatan berjalan
 *
 * CATATAN PENTING soal perilaku aman:
 * Bila sekolah BELUM menandai satu pun periode sebagai aktif, id() bernilai
 * null dan scope periode() mengabaikan penyaringan -- seluruh data tampil apa
 * adanya. Ini disengaja. Halaman yang tiba-tiba kosong jauh lebih berbahaya
 * bagi pengguna awam daripada halaman yang menampilkan semua data. Gunakan
 * KonteksPeriode::ada() bila sebuah halaman perlu memperingatkan admin.
 */
class KonteksPeriode
{
    /** Cache selama satu request. false = belum pernah dihitung. */
    private static int|null|false $cacheId = false;

    /**
     * ID periode yang sedang berjalan, atau null bila belum ada yang aktif.
     *
     * Hasilnya di-cache supaya halaman dengan puluhan query (Monitoring punya
     * 19) tidak memukul tabel periode_pkls berulang kali. Ini berpengaruh
     * nyata di shared hosting.
     */
    public static function id(): ?int
    {
        if (self::$cacheId === false) {
            self::$cacheId = PeriodePkl::aktif()?->id;
        }

        return self::$cacheId;
    }

    /** Objek periode berjalan, bila view butuh nama / tahun ajarannya. */
    public static function aktif(): ?PeriodePkl
    {
        return PeriodePkl::aktif();
    }

    /** True bila sekolah sudah menandai satu periode sebagai aktif. */
    public static function ada(): bool
    {
        return self::id() !== null;
    }

    /**
     * Kosongkan cache.
     *
     * WAJIB dipanggil bila admin mengaktifkan periode lain di dalam request
     * yang sama; kalau tidak, sisa request itu masih memakai id yang lama.
     */
    public static function lupakan(): void
    {
        self::$cacheId = false;
    }
}
