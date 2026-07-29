<?php

namespace Tests\Feature\Pkl;

use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji scope siswaBerjalan() pada model User.
 *
 * Scope ini dipakai di 38 tempat dan menjadi satu-satunya penjaga agar daftar
 * siswa tidak bercampur antar angkatan. Bila ia bocor, guru akan melihat siswa
 * lulusan tahun lalu di daftar bimbingannya.
 *
 * CATATAN soal periode_id:
 * siswaBerjalan() menyaring berdasarkan periode aktif. Karena itu setiap siswa
 * dalam tes ini WAJIB diberi periode_id secara eksplisit. Siswa tanpa periode
 * memang sengaja tidak ikut tersaring, dan itu perilaku yang benar.
 */
class ScopePeriodeSiswaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();
    }

    public function test_hanya_menampilkan_siswa_angkatan_berjalan(): void
    {
        $lama = PeriodePkl::factory()->create();
        $baru = PeriodePkl::factory()->aktif()->create();

        $siswaLama = User::factory()->siswa()->create(['periode_id' => $lama->id]);
        $siswaBaru = User::factory()->siswa()->create(['periode_id' => $baru->id]);

        $hasil = User::siswaBerjalan()->pluck('id');

        $this->assertTrue($hasil->contains($siswaBaru->id), 'Siswa angkatan berjalan hilang dari daftar.');
        $this->assertFalse($hasil->contains($siswaLama->id), 'Siswa angkatan lama bocor ke daftar berjalan.');
    }

    public function test_tidak_menampilkan_guru_atau_admin(): void
    {
        $periode = PeriodePkl::factory()->aktif()->create();

        User::factory()->admin()->create(['periode_id' => $periode->id]);
        User::factory()->guru()->create(['periode_id' => $periode->id]);
        $siswa = User::factory()->siswa()->create(['periode_id' => $periode->id]);

        $hasil = User::siswaBerjalan()->pluck('id');

        // Sengaja memberi periode_id pada guru dan admin juga, supaya yang
        // benar-benar diuji adalah penyaringan PERAN, bukan penyaringan periode.
        $this->assertCount(1, $hasil);
        $this->assertTrue($hasil->contains($siswa->id));
    }

    public function test_siswa_yang_diarsipkan_tidak_muncul(): void
    {
        $periode = PeriodePkl::factory()->aktif()->create();

        $aktif    = User::factory()->siswa()->create(['periode_id' => $periode->id]);
        $terarsip = User::factory()->siswa()->create(['periode_id' => $periode->id]);
        $terarsip->delete();

        $hasil = User::siswaBerjalan()->pluck('id');

        $this->assertTrue($hasil->contains($aktif->id));
        $this->assertFalse($hasil->contains($terarsip->id), 'Siswa terarsip masih muncul di daftar aktif.');
    }

    /**
     * Mengunci efek samping yang pernah terjadi: relasi siswa() pada model
     * Jurnal memakai withTrashed() agar arsip tetap bisa dicetak. Tanpa
     * penjagaan, withTrashed() itu ikut terbawa ke whereHas dan membuat data
     * siswa terarsip bocor ke halaman guru.
     */
    public function test_where_has_dengan_siswa_berjalan_tidak_membocorkan_siswa_terarsip(): void
    {
        $periode = PeriodePkl::factory()->aktif()->create();

        $aktif    = User::factory()->siswa()->create(['periode_id' => $periode->id]);
        $terarsip = User::factory()->siswa()->create(['periode_id' => $periode->id]);

        Jurnal::create(['siswa_id' => $aktif->id,    'hari_tanggal' => '2026-02-01', 'status' => 'draft']);
        Jurnal::create(['siswa_id' => $terarsip->id, 'hari_tanggal' => '2026-02-02', 'status' => 'draft']);

        $terarsip->delete();

        $hasil = Jurnal::whereHas('siswa', function ($q) {
            $q->siswaBerjalan();
        })->pluck('siswa_id');

        $this->assertCount(1, $hasil, 'Jurnal milik siswa terarsip ikut terbawa.');
        $this->assertTrue($hasil->contains($aktif->id));
    }

    public function test_scope_berjalan_mengikuti_pergantian_periode(): void
    {
        $periodeSatu = PeriodePkl::factory()->aktif()->create();
        $periodeDua  = PeriodePkl::factory()->create();

        $siswaSatu = User::factory()->siswa()->create(['periode_id' => $periodeSatu->id]);
        $siswaDua  = User::factory()->siswa()->create(['periode_id' => $periodeDua->id]);

        $this->assertTrue(User::siswaBerjalan()->pluck('id')->contains($siswaSatu->id));

        // Admin mengaktifkan angkatan berikutnya.
        $periodeDua->update(['is_active' => true]);

        $hasil = User::siswaBerjalan()->pluck('id');

        // Daftar HARUS ikut berpindah tanpa perlu membersihkan cache manual.
        $this->assertTrue($hasil->contains($siswaDua->id), 'Daftar tidak ikut berpindah ke angkatan baru.');
        $this->assertFalse($hasil->contains($siswaSatu->id), 'Angkatan lama masih tertinggal di daftar.');
    }
}
