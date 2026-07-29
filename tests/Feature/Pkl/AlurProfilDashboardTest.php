<?php

/** TARUH DI: tests/Feature/Pkl/AlurProfilDashboardTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Informasi;
use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji halaman Profil, pengalihan Dashboard tiap peran,
 * dan halaman depan publik.
 */
class AlurProfilDashboardTest extends TestCase
{
    use RefreshDatabase;

    private PeriodePkl $periode;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        KonteksPeriode::lupakan();

        $this->periode = PeriodePkl::factory()->aktif()->create();
    }

    /*
    |----------------------------------------------------------------
    | PROFIL
    |----------------------------------------------------------------
    */

    public function test_admin_membuka_halaman_profil(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_guru_membuka_halaman_profil(): void
    {
        $this->actingAs(User::factory()->guru()->create())
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_siswa_membuka_halaman_profil(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_pengguna_memperbarui_namanya(): void
    {
        $siswa = User::factory()->siswa()->create([
            'name'       => 'Nama Lama',
            'periode_id' => $this->periode->id,
        ]);

        $this->actingAs($siswa)
            ->patch(route('profile.update'), ['name' => 'Nama Baru'])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('Nama Baru', $siswa->fresh()->name);
    }

    public function test_nama_wajib_diisi_saat_memperbarui_profil(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_siswa_tidak_dapat_mengganti_emailnya_sendiri(): void
    {
        $siswa = User::factory()->siswa()->create([
            'email'      => 'siswa.asli@sekolah.test',
            'periode_id' => $this->periode->id,
        ]);

        $this->actingAs($siswa)
            ->patch(route('profile.update'), [
                'name'  => $siswa->name,
                'email' => 'email.baru@sekolah.test',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('siswa.asli@sekolah.test', $siswa->fresh()->email);
    }

    public function test_guru_tidak_dapat_mengganti_emailnya_sendiri(): void
    {
        $guru = User::factory()->guru()->create(['email' => 'guru.asli@sekolah.test']);

        $this->actingAs($guru)
            ->patch(route('profile.update'), [
                'name'  => $guru->name,
                'email' => 'email.baru@sekolah.test',
            ]);

        $this->assertSame('guru.asli@sekolah.test', $guru->fresh()->email);
    }

    public function test_admin_dapat_mengganti_emailnya(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin.asli@sekolah.test']);

        $this->actingAs($admin)
            ->patch(route('profile.update'), [
                'name'  => $admin->name,
                'email' => 'admin.baru@sekolah.test',
            ])
            ->assertRedirect(route('profile.edit'));

        $admin->refresh();

        $this->assertSame('admin.baru@sekolah.test', $admin->email);
        $this->assertNull($admin->email_verified_at);
    }

    public function test_email_admin_tidak_boleh_sama_dengan_pengguna_lain(): void
    {
        User::factory()->admin()->create(['email' => 'sudah.dipakai@sekolah.test']);
        $admin = User::factory()->admin()->create(['email' => 'admin.asli@sekolah.test']);

        $this->actingAs($admin)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name'  => $admin->name,
                'email' => 'sudah.dipakai@sekolah.test',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_foto_profil_tersimpan_saat_diunggah(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->patch(route('profile.update'), [
                'name' => $siswa->name,
                'foto' => UploadedFile::fake()->image('wajah.jpg', 900, 900),
            ])
            ->assertRedirect(route('profile.edit'));

        $siswa->refresh();

        $this->assertNotNull($siswa->foto);
        Storage::disk('public')->assertExists($siswa->foto);
    }

    public function test_mengganti_foto_profil_menghapus_foto_lama(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)->patch(route('profile.update'), [
            'name' => $siswa->name,
            'foto' => UploadedFile::fake()->image('lama.jpg', 900, 900),
        ]);

        $fotoLama = $siswa->fresh()->foto;

        $this->actingAs($siswa)->patch(route('profile.update'), [
            'name' => $siswa->name,
            'foto' => UploadedFile::fake()->image('baru.jpg', 900, 900),
        ]);

        $siswa->refresh();

        $this->assertNotSame($fotoLama, $siswa->foto);
        Storage::disk('public')->assertMissing($fotoLama);
        Storage::disk('public')->assertExists($siswa->foto);
    }

    public function test_memperbarui_profil_tanpa_foto_tidak_menghapus_foto_lama(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)->patch(route('profile.update'), [
            'name' => $siswa->name,
            'foto' => UploadedFile::fake()->image('wajah.jpg', 900, 900),
        ]);

        $foto = $siswa->fresh()->foto;

        $this->actingAs($siswa)->patch(route('profile.update'), ['name' => 'Nama Diubah']);

        $siswa->refresh();

        $this->assertSame('Nama Diubah', $siswa->name);
        $this->assertSame($foto, $siswa->foto);
        Storage::disk('public')->assertExists($foto);
    }

    public function test_berkas_bukan_gambar_ditolak_sebagai_foto_profil(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $siswa->name,
                'foto' => UploadedFile::fake()->create('berkas.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('foto');
    }

    public function test_tamu_tidak_bisa_membuka_halaman_profil(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    /*
    |----------------------------------------------------------------
    | DASHBOARD
    |----------------------------------------------------------------
    */

    public function test_admin_diarahkan_ke_dashboard_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_guru_diarahkan_ke_dashboard_guru(): void
    {
        $this->actingAs(User::factory()->guru()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('guru.dashboard'));
    }

    public function test_siswa_diarahkan_ke_dashboard_siswa(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->get(route('dashboard'))
            ->assertRedirect(route('siswa.dashboard'));
    }

    public function test_dashboard_admin_menampilkan_rekap(): void
    {
        $admin = User::factory()->admin()->create();

        User::factory()->siswa()->create([
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('totalSiswa')
            ->assertViewHas('kehadiran')
            ->assertViewHas('periodeList');
    }

    public function test_dashboard_admin_memberi_peringatan_bila_belum_ada_periode_aktif(): void
    {
        $admin = User::factory()->admin()->create();

        $this->periode->update(['is_active' => false]);
        KonteksPeriode::lupakan();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('peringatanPeriode');
    }

    public function test_dashboard_admin_dapat_dipilih_per_periode(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['periode' => $this->periode->id]))
            ->assertOk()
            ->assertViewHas('periodeId', $this->periode->id);
    }

    public function test_dashboard_guru_menghitung_siswa_bimbingan(): void
    {
        $guru = User::factory()->guru()->create();

        User::factory()->count(3)->siswa()->create([
            'guru_id'    => $guru->id,
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);

        User::factory()->siswa()->create([
            'guru_id'    => $guru->id,
            'periode_id' => $this->periode->id,
            'status_pkl' => 'selesai',
        ]);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertViewHas('siswaBimbingan', 4)
            ->assertViewHas('siswaAktif', 3)
            ->assertViewHas('siswaSelesai', 1);
    }

    public function test_dashboard_guru_tidak_menghitung_bimbingan_guru_lain(): void
    {
        $guru     = User::factory()->guru()->create();
        $guruLain = User::factory()->guru()->create();

        User::factory()->siswa()->create([
            'guru_id'    => $guruLain->id,
            'periode_id' => $this->periode->id,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertViewHas('siswaBimbingan', 0);
    }

    public function test_dashboard_siswa_merangkum_jurnalnya(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        Jurnal::create([
            'siswa_id'           => $siswa->id,
            'hari_tanggal'       => now()->toDateString(),
            'status'             => 'disetujui',
            'status_persetujuan' => 'disetujui',
        ]);

        Jurnal::create([
            'siswa_id'           => $siswa->id,
            'hari_tanggal'       => now()->subDay()->toDateString(),
            'status'             => 'draft',
            'status_persetujuan' => 'pending',
        ]);

        $this->actingAs($siswa)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertViewHas('jumlahJurnal', 2)
            ->assertViewHas('jurnalDisetujui', 1);
    }

    public function test_dashboard_siswa_tidak_menghitung_jurnal_siswa_lain(): void
    {
        $siswa     = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);
        $siswaLain = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        Jurnal::create([
            'siswa_id'     => $siswaLain->id,
            'hari_tanggal' => now()->toDateString(),
            'status'       => 'draft',
        ]);

        $this->actingAs($siswa)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertViewHas('jumlahJurnal', 0);
    }

    /*
    |----------------------------------------------------------------
    | HALAMAN DEPAN PUBLIK
    |----------------------------------------------------------------
    */

    public function test_halaman_depan_dapat_dibuka_tanpa_login(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_halaman_depan_menampilkan_daftar_tanya_jawab(): void
    {
        Informasi::create([
            'judul'  => 'Apakah PKL wajib diikuti',
            'konten' => '<p>Ya, PKL wajib bagi seluruh siswa kelas dua belas.</p>',
            'tipe'   => 'faq',
            'urutan' => 1,
        ]);

        Informasi::create([
            'judul'  => 'Panduan Yang Bukan Tanya Jawab',
            'konten' => '<p>Isi panduan.</p>',
            'tipe'   => 'panduan',
            'urutan' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Apakah PKL wajib diikuti')
            ->assertDontSee('Panduan Yang Bukan Tanya Jawab');
    }
}
