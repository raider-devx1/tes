<?php

/** TARUH DI: tests/Feature/Pkl/AlurAdminGuruTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Menguji menu Admin > Kelola Guru Pembimbing.
 */
class AlurAdminGuruTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PeriodePkl $periode;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();

        $this->periode = PeriodePkl::factory()->aktif()->create();
        $this->admin   = User::factory()->admin()->create();
    }

    private function dataGuru(array $timpa = []): array
    {
        return array_merge([
            'name'                  => 'Pak Hendra Saputra',
            'nip'                   => '19850101001',
            'no_hp'                 => '081234567890',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $timpa);
    }

    public function test_admin_melihat_daftar_guru(): void
    {
        User::factory()->guru()->create(['name' => 'Bu Ratna Wijaya']);

        $this->actingAs($this->admin)
            ->get(route('admin.guru.index'))
            ->assertOk()
            ->assertSee('Bu Ratna Wijaya');
    }

    public function test_admin_membuka_form_tambah_guru(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.guru.create'))
            ->assertOk();
    }

    public function test_admin_menambah_akun_guru_baru(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.guru.store'), $this->dataGuru())
            ->assertRedirect(route('admin.guru.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nip'  => '19850101001',
            'role' => 'guru_pembimbing',
        ]);
    }

    public function test_password_guru_baru_tersimpan_dalam_bentuk_acak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.guru.store'), $this->dataGuru());

        $guru = User::where('nip', '19850101001')->firstOrFail();

        $this->assertNotSame('rahasia123', $guru->password);
        $this->assertTrue(Hash::check('rahasia123', $guru->password));
    }

    public function test_nama_dan_nip_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.guru.create'))
            ->post(route('admin.guru.store'), $this->dataGuru(['name' => '', 'nip' => '']))
            ->assertSessionHasErrors(['name', 'nip']);
    }

    public function test_nip_tidak_boleh_ganda(): void
    {
        User::factory()->guru()->create(['nip' => '19850101001']);

        $this->actingAs($this->admin)
            ->from(route('admin.guru.create'))
            ->post(route('admin.guru.store'), $this->dataGuru())
            ->assertSessionHasErrors('nip');

        $this->assertSame(1, User::where('nip', '19850101001')->count());
    }

    public function test_password_minimal_enam_karakter(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.guru.create'))
            ->post(route('admin.guru.store'), $this->dataGuru([
                'password'              => 'abc',
                'password_confirmation' => 'abc',
            ]))
            ->assertSessionHasErrors('password');
    }

    public function test_konfirmasi_password_harus_sama(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.guru.create'))
            ->post(route('admin.guru.store'), $this->dataGuru([
                'password_confirmation' => 'salah-ketik',
            ]))
            ->assertSessionHasErrors('password');
    }

    public function test_admin_mengubah_data_guru_tanpa_mengganti_password(): void
    {
        $guru = User::factory()->guru()->create([
            'name'     => 'Nama Lama',
            'nip'      => '19900202002',
            'password' => Hash::make('sandi-lama'),
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.guru.update', $guru), [
                'name'     => 'Nama Baru',
                'nip'      => '19900202002',
                'no_hp'    => '08111111111',
                'password' => '',
            ])
            ->assertRedirect(route('admin.guru.index'));

        $guru->refresh();

        $this->assertSame('Nama Baru', $guru->name);
        $this->assertTrue(Hash::check('sandi-lama', $guru->password));
    }

    public function test_admin_mengganti_password_guru(): void
    {
        $guru = User::factory()->guru()->create(['nip' => '19900202002']);

        $this->actingAs($this->admin)
            ->put(route('admin.guru.update', $guru), [
                'name'                  => $guru->name,
                'nip'                   => '19900202002',
                'password'              => 'sandi-baru123',
                'password_confirmation' => 'sandi-baru123',
            ])
            ->assertRedirect(route('admin.guru.index'));

        $this->assertTrue(Hash::check('sandi-baru123', $guru->fresh()->password));
    }

    public function test_admin_menghapus_akun_guru(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->delete(route('admin.guru.destroy', $guru))
            ->assertRedirect(route('admin.guru.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $guru->id]);
    }

    public function test_admin_menetapkan_guru_sebagai_wakasek(): void
    {
        $guru = User::factory()->guru()->create(['is_wakasek' => false]);

        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->put(route('admin.guru.jadikan-wakasek', $guru))
            ->assertRedirect(route('admin.guru.index'));

        $this->assertTrue((bool) $guru->fresh()->is_wakasek);
    }

    public function test_admin_membatalkan_status_wakasek(): void
    {
        $guru = User::factory()->guru()->create(['is_wakasek' => true]);

        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->put(route('admin.guru.batalkan-wakasek', $guru));

        $this->assertFalse((bool) $guru->fresh()->is_wakasek);
    }

    public function test_admin_memberi_akses_panel_admin_kepada_guru(): void
    {
        $guru = User::factory()->guru()->create(['is_admin' => false]);

        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->put(route('admin.guru.jadikan-admin', $guru));

        $this->assertTrue((bool) $guru->fresh()->is_admin);
    }

    public function test_admin_membatalkan_akses_panel_admin(): void
    {
        $guru = User::factory()->guru()->create(['is_admin' => true]);

        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->put(route('admin.guru.batalkan-admin', $guru));

        $this->assertFalse((bool) $guru->fresh()->is_admin);
    }

    public function test_siswa_tidak_bisa_dijadikan_wakasek(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->put(route('admin.guru.jadikan-wakasek', $siswa))
            ->assertNotFound();
    }

    public function test_hapus_semua_guru_melepas_bimbingan_dan_tanda_tangan(): void
    {
        $guru  = User::factory()->guru()->create();
        $siswa = User::factory()->siswa()->create([
            'guru_id'    => $guru->id,
            'periode_id' => $this->periode->id,
        ]);

        $jurnal = Jurnal::create([
            'siswa_id'      => $siswa->id,
            'hari_tanggal'  => now()->toDateString(),
            'status'        => 'disetujui',
            'disetujui_oleh' => $guru->id,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->delete(route('admin.guru.hapus-semua'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $guru->id]);
        $this->assertNull($siswa->fresh()->guru_id);
        $this->assertNull($jurnal->fresh()->disetujui_oleh);
    }

    public function test_hapus_semua_guru_saat_belum_ada_guru_memberi_pesan_jelas(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->delete(route('admin.guru.hapus-semua'))
            ->assertSessionHas('error');
    }

    public function test_admin_mengunduh_template_impor_guru(): void
    {
        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.guru.template'))
            ->assertOk();

        Excel::assertDownloaded('template-import-guru.xlsx');
    }

    public function test_admin_mengekspor_data_guru_ke_excel(): void
    {
        Excel::fake();
        User::factory()->guru()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.guru.export.excel'))
            ->assertOk();
    }

    public function test_admin_mengekspor_data_guru_ke_pdf(): void
    {
        User::factory()->guru()->create(['name' => 'Bu Ratna Wijaya']);

        $this->actingAs($this->admin)
            ->get(route('admin.guru.export.pdf'))
            ->assertOk();
    }

    public function test_impor_menolak_berkas_selain_excel(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->post(route('admin.guru.import'), [
                'file' => UploadedFile::fake()->create('daftar.txt', 5, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_guru_tidak_boleh_membuka_menu_kelola_guru(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->get(route('admin.guru.index'))
            ->assertForbidden();
    }

    public function test_siswa_tidak_boleh_menambah_akun_guru(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->post(route('admin.guru.store'), $this->dataGuru())
            ->assertForbidden();
    }
}
