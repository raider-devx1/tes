<?php

namespace Tests\Feature\Pkl;

use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SMOKE TEST: "apakah semua halaman masih terbuka?"
 *
 * Inilah tes yang paling menjawab pertanyaan "apakah fitur saya berfungsi".
 * Tes ini benar-benar merender setiap halaman utama, sehingga akan GAGAL bila
 * ada salah ketik di Blade, variabel yang lupa dikirim controller, relasi null
 * yang tidak dijaga, atau kelas yang tidak ditemukan.
 *
 * Cakupannya sengaja luas tapi dangkal: memastikan tidak ada yang error,
 * bukan memeriksa isi tampilan. Justru itu yang membuatnya awet -- desain
 * boleh berubah tanpa membuat tes ini ikut merah.
 */
class SmokeHalamanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();
    }

    /**
     * Menyiapkan data minimum yang wajar: satu periode aktif, satu guru,
     * dan satu siswa bimbingan yang terhubung ke keduanya.
     *
     * @return array{periode: PeriodePkl, guru: User, siswa: User, admin: User}
     */
    private function siapkanData(): array
    {
        $periode = PeriodePkl::factory()->aktif()->create();
        $admin   = User::factory()->admin()->create();
        $guru    = User::factory()->guru()->create();

        $siswa = User::factory()->siswa()->create([
            'periode_id' => $periode->id,
            'guru_id'    => $guru->id,
        ]);

        return compact('periode', 'guru', 'siswa', 'admin');
    }

    public static function halamanAdmin(): array
    {
        return [
            'monitoring jurnal'  => ['/admin/monitoring/jurnal'],
            'monitoring catatan' => ['/admin/monitoring/catatan'],
            'monitoring absensi' => ['/admin/monitoring/absensi'],
            'evaluasi observasi' => ['/admin/evaluasi/observasi'],
            'evaluasi penilaian' => ['/admin/evaluasi/penilaian'],
        ];
    }

    public static function halamanGuru(): array
    {
        return [
            'dashboard'          => ['/guru/dashboard'],
            'daftar siswa'       => ['/guru/siswa'],
            'monitoring jurnal'  => ['/guru/monitoring/jurnal'],
            'monitoring absensi' => ['/guru/monitoring/absensi'],
            'catatan'            => ['/guru/catatan'],
            'observasi'          => ['/guru/observasi'],
            'nilai'              => ['/guru/nilai'],
            'dokumen'            => ['/guru/dokumen'],
        ];
    }

    public static function halamanSiswa(): array
    {
        return [
            'dashboard' => ['/siswa/dashboard'],
            'jurnal'    => ['/siswa/jurnal'],
            'catatan'   => ['/siswa/catatan'],
            'absensi'   => ['/siswa/absensi'],
            'observasi' => ['/siswa/observasi'],
            'nilai'     => ['/siswa/nilai'],
            'dokumen'   => ['/siswa/dokumen'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanAdmin')]
    public function test_halaman_admin_terbuka_tanpa_error(string $url): void
    {
        $data = $this->siapkanData();

        $this->actingAs($data['admin'])->get($url)->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanGuru')]
    public function test_halaman_guru_terbuka_tanpa_error(string $url): void
    {
        $data = $this->siapkanData();

        $this->actingAs($data['guru'])->get($url)->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanSiswa')]
    public function test_halaman_siswa_terbuka_tanpa_error(string $url): void
    {
        $data = $this->siapkanData();

        $this->actingAs($data['siswa'])->get($url)->assertOk();
    }

    /**
     * Halaman guru harus tetap terbuka walau sekolah BELUM menandai satu pun
     * periode sebagai aktif -- kondisi yang pasti terjadi saat sistem baru
     * dipasang, sebelum admin sempat mengisi apa pun.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('halamanGuru')]
    public function test_halaman_guru_tetap_terbuka_tanpa_periode_aktif(string $url): void
    {
        $guru  = User::factory()->guru()->create();
        $siswa = User::factory()->siswa()->create(['guru_id' => $guru->id]);

        $this->assertNull(KonteksPeriode::id());

        $this->actingAs($guru)->get($url)->assertOk();
    }

    /**
     * Halaman guru harus tetap terbuka walau ada siswa bimbingan yang sudah
     * diarsipkan. Ini menjaga agar relasi ber-withTrashed() tidak membuat
     * tampilan error karena mengakses properti pada data yang hilang.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('halamanGuru')]
    public function test_halaman_guru_tetap_terbuka_walau_ada_siswa_terarsip(string $url): void
    {
        $data = $this->siapkanData();

        $data['siswa']->delete();

        $this->actingAs($data['guru'])->get($url)->assertOk();
    }
}
