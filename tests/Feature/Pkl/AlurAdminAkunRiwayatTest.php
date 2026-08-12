<?php

/** TARUH DI: tests/Feature/Pkl/AlurAdminAkunRiwayatTest.php */

namespace Tests\Feature\Pkl;

use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Menguji menu Pengaturan > Kelola Akun Admin.
 */
class AlurAdminAkunRiwayatTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();

        PeriodePkl::factory()->aktif()->create();

        $this->admin = User::factory()->admin()->create([
            'name' => 'Admin Utama',
            'nip'  => '19700101000',
        ]);
    }

    private function dataAdmin(array $timpa = []): array
    {
        return array_merge([
            'name'                  => 'Admin Kedua',
            'nip'                   => '19800505005',
            'email'                 => 'admin.kedua@sekolah.test',
            'no_hp'                 => '081200000000',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $timpa);
    }

    public function test_admin_melihat_daftar_akun_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.akun-admin.index'))
            ->assertOk()
            ->assertSee('Admin Utama');
    }

    public function test_admin_membuka_form_tambah_akun_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.akun-admin.create'))
            ->assertOk();
    }

    public function test_admin_menambah_akun_admin_baru(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.akun-admin.store'), $this->dataAdmin())
            ->assertRedirect(route('admin.akun-admin.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nip'  => '19800505005',
            'role' => 'admin',
        ]);
    }

    public function test_nip_akun_admin_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.akun-admin.create'))
            ->post(route('admin.akun-admin.store'), $this->dataAdmin(['nip' => '']))
            ->assertSessionHasErrors('nip');
    }

    public function test_nip_akun_admin_tidak_boleh_ganda(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.akun-admin.create'))
            ->post(route('admin.akun-admin.store'), $this->dataAdmin(['nip' => '19700101000']))
            ->assertSessionHasErrors('nip');
    }

    public function test_email_akun_admin_boleh_dikosongkan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.akun-admin.store'), $this->dataAdmin(['email' => '']))
            ->assertRedirect(route('admin.akun-admin.index'));

        $this->assertDatabaseHas('users', ['nip' => '19800505005']);
    }

    public function test_email_akun_admin_tidak_boleh_ganda(): void
    {
        User::factory()->admin()->create([
            'nip'   => '19990909009',
            'email' => 'sudah.dipakai@sekolah.test',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.akun-admin.create'))
            ->post(route('admin.akun-admin.store'), $this->dataAdmin([
                'email' => 'sudah.dipakai@sekolah.test',
            ]))
            ->assertSessionHasErrors('email');
    }

    public function test_password_akun_admin_minimal_enam_karakter(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.akun-admin.create'))
            ->post(route('admin.akun-admin.store'), $this->dataAdmin([
                'password'              => '123',
                'password_confirmation' => '123',
            ]))
            ->assertSessionHasErrors('password');
    }

    public function test_admin_memperbarui_akun_admin_tanpa_mengganti_password(): void
    {
        $lain = User::factory()->admin()->create([
            'name'     => 'Nama Lama',
            'nip'      => '19990909009',
            'password' => Hash::make('sandi-lama'),
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.akun-admin.update', $lain), [
                'name'     => 'Nama Baru',
                'nip'      => '19990909009',
                'password' => '',
            ])
            ->assertRedirect(route('admin.akun-admin.index'));

        $lain->refresh();

        $this->assertSame('Nama Baru', $lain->name);
        $this->assertTrue(Hash::check('sandi-lama', $lain->password));
    }

    public function test_admin_tidak_dapat_menghapus_akunnya_sendiri(): void
    {
        User::factory()->admin()->create(['nip' => '19990909009']);

        $this->actingAs($this->admin)
            ->from(route('admin.akun-admin.index'))
            ->delete(route('admin.akun-admin.destroy', $this->admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id'         => $this->admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_akun_admin_terakhir_tidak_boleh_dihapus(): void
    {
        $satuSatunya = User::where('role', 'admin')->count();
        $this->assertSame(1, $satuSatunya);

        $this->actingAs($this->admin)
            ->from(route('admin.akun-admin.index'))
            ->delete(route('admin.akun-admin.destroy', $this->admin))
            ->assertSessionHas('error');

        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    public function test_admin_dapat_menghapus_akun_admin_lain(): void
    {
        $lain = User::factory()->admin()->create(['nip' => '19990909009']);

        $this->actingAs($this->admin)
            ->from(route('admin.akun-admin.index'))
            ->delete(route('admin.akun-admin.destroy', $lain))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $lain->id]);
    }

    public function test_guru_tidak_boleh_membuka_kelola_akun_admin(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->get(route('admin.akun-admin.index'))
            ->assertForbidden();
    }

}
