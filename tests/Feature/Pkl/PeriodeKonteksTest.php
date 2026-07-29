<?php

namespace Tests\Feature\Pkl;

use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji "otak" penentu angkatan berjalan: App\Support\KonteksPeriode.
 *
 * Seluruh aplikasi bergantung pada kelas ini untuk menjawab pertanyaan
 * "periode mana yang sedang dilihat?". Kalau kelas ini salah, angka di
 * dashboard, daftar guru, dan rekap PDF ikut salah tanpa memunculkan error.
 */
class PeriodeKonteksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cache bersifat static (bertahan antar tes dalam satu proses).
        // Wajib dikosongkan agar tes tidak saling mempengaruhi.
        KonteksPeriode::lupakan();
    }

    public function test_mengembalikan_id_periode_yang_aktif(): void
    {
        $periode = PeriodePkl::factory()->aktif()->create();

        $this->assertSame($periode->id, KonteksPeriode::id());
        $this->assertTrue(KonteksPeriode::ada());
    }

    public function test_mengembalikan_null_bila_belum_ada_periode_aktif(): void
    {
        PeriodePkl::factory()->create(); // is_active = false

        $this->assertNull(KonteksPeriode::id());
        $this->assertFalse(KonteksPeriode::ada());
    }

    /**
     * INI TES REGRESI PALING PENTING.
     *
     * Dulu cache tidak pernah dikosongkan, sehingga setelah admin mengaktifkan
     * periode lain, sisa request masih memakai ID periode yang LAMA. Tidak ada
     * error, hanya angka yang diam-diam salah -- bug termahal untuk dilacak.
     *
     * Sekarang PeriodePkl::saved() otomatis memanggil KonteksPeriode::lupakan().
     */
    public function test_cache_otomatis_dikosongkan_saat_periode_lain_diaktifkan(): void
    {
        $lama = PeriodePkl::factory()->aktif()->create();

        // Paksa cache terisi lebih dulu.
        $this->assertSame($lama->id, KonteksPeriode::id());

        // Admin mengaktifkan periode baru DI DALAM request yang sama.
        $baru = PeriodePkl::factory()->aktif()->create();

        $this->assertSame(
            $baru->id,
            KonteksPeriode::id(),
            'Cache periode tidak dikosongkan saat periode baru diaktifkan.'
        );
    }

    public function test_cache_dikosongkan_saat_periode_aktif_dihapus(): void
    {
        $periode = PeriodePkl::factory()->aktif()->create();
        $this->assertSame($periode->id, KonteksPeriode::id());

        $periode->delete();

        $this->assertNull(
            KonteksPeriode::id(),
            'Cache periode tidak dikosongkan saat periode aktif dihapus.'
        );
    }

    /**
     * Dulu ada DUA mekanisme cache: satu di KonteksPeriode, satu lagi di trait
     * MilikPeriodePkl. Properti static di dalam trait DISALIN ke setiap kelas
     * yang memakainya, sehingga sebenarnya ada 6 cache terpisah (Jurnal,
     * Absensi, Nilai, Dokumen, Observasi, CatatanKegiatan).
     *
     * Sekarang trait hanya meneruskan ke KonteksPeriode. Tes ini memastikan
     * tidak ada yang diam-diam menghidupkan kembali cache kedua.
     */
    public function test_trait_dan_konteks_periode_memakai_sumber_yang_sama(): void
    {
        $lama = PeriodePkl::factory()->aktif()->create();

        $this->assertSame($lama->id, Jurnal::periodeAktifId());

        $baru = PeriodePkl::factory()->aktif()->create();

        $this->assertSame(
            KonteksPeriode::id(),
            Jurnal::periodeAktifId(),
            'Trait memakai cache sendiri yang tidak ikut dikosongkan.'
        );
        $this->assertSame($baru->id, Jurnal::periodeAktifId());
    }

    /**
     * Perilaku aman yang disengaja: bila sekolah belum menandai satu pun
     * periode sebagai aktif, penyaringan DIABAIKAN sehingga halaman tetap
     * berisi. Halaman yang tiba-tiba kosong jauh lebih membingungkan guru
     * daripada halaman yang menampilkan seluruh angkatan.
     */
    public function test_tanpa_periode_aktif_daftar_siswa_tidak_menjadi_kosong(): void
    {
        User::factory()->siswa()->count(3)->create();

        $this->assertNull(KonteksPeriode::id());
        $this->assertCount(3, User::siswaBerjalan()->get());
    }
}
