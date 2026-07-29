<?php

namespace Tests\Feature\Pkl;

use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji pemisahan hak akses antar peran (middleware CheckRole).
 *
 * Ini pengaman paling depan. Kalau bocor, siswa bisa membuka panel admin dan
 * mengubah nilainya sendiri. Tes ini sengaja hanya memeriksa jalur PENOLAKAN,
 * karena penolakan terjadi di middleware -- tidak menyentuh controller maupun
 * tampilan, sehingga hasilnya stabil dan tidak mudah rusak oleh perubahan UI.
 */
class HakAksesRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();
    }

    /** Halaman admin yang mewakili tiap kelompok fitur. */
    public static function halamanAdmin(): array
    {
        return [
            'monitoring jurnal'  => ['/admin/monitoring/jurnal'],
            'monitoring absensi' => ['/admin/monitoring/absensi'],
            'evaluasi observasi' => ['/admin/evaluasi/observasi'],
            'evaluasi penilaian' => ['/admin/evaluasi/penilaian'],
        ];
    }

    public static function halamanGuru(): array
    {
        return [
            'daftar siswa'      => ['/guru/siswa'],
            'monitoring jurnal' => ['/guru/monitoring/jurnal'],
            'catatan'           => ['/guru/catatan'],
            'nilai'             => ['/guru/nilai'],
        ];
    }

    public static function halamanSiswa(): array
    {
        return [
            'jurnal'  => ['/siswa/jurnal'],
            'absensi' => ['/siswa/absensi'],
            'catatan' => ['/siswa/catatan'],
            'nilai'   => ['/siswa/nilai'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanAdmin')]
    public function test_siswa_tidak_boleh_membuka_halaman_admin(string $url): void
    {
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)->get($url)->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanAdmin')]
    public function test_guru_biasa_tidak_boleh_membuka_halaman_admin(string $url): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get($url)->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanGuru')]
    public function test_siswa_tidak_boleh_membuka_halaman_guru(string $url): void
    {
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)->get($url)->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanSiswa')]
    public function test_guru_tidak_boleh_membuka_halaman_siswa(string $url): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get($url)->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('halamanAdmin')]
    public function test_tamu_belum_login_dialihkan_ke_halaman_masuk(string $url): void
    {
        $this->get($url)->assertRedirect('/login');
    }

    /**
     * Guru pembimbing yang ditetapkan admin BOLEH masuk panel admin.
     * Diuji lewat middleware langsung agar hasilnya tidak bergantung pada
     * tampilan halaman.
     */
    public function test_guru_merangkap_admin_lolos_pemeriksaan_peran(): void
    {
        $guru = User::factory()->guruMerangkapAdmin()->create();

        $request = \Illuminate\Http\Request::create('/admin/monitoring/jurnal', 'GET');
        $request->setUserResolver(fn () => $guru);

        $lolos = (new \App\Http\Middleware\CheckRole())->handle(
            $request,
            fn () => new \Illuminate\Http\Response('ok'),
            'admin'
        );

        $this->assertSame(200, $lolos->getStatusCode());
    }

    public function test_guru_biasa_ditolak_pemeriksaan_peran(): void
    {
        $guru = User::factory()->guru()->create();

        $request = \Illuminate\Http\Request::create('/admin/monitoring/jurnal', 'GET');
        $request->setUserResolver(fn () => $guru);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new \App\Http\Middleware\CheckRole())->handle(
            $request,
            fn () => new \Illuminate\Http\Response('ok'),
            'admin'
        );
    }
}
