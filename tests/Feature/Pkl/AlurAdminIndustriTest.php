<?php

/** TARUH DI: tests/Feature/Pkl/AlurAdminIndustriTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Perusahaan;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Menguji menu Admin > Industri & Pembimbing Industri.
 */
class AlurAdminIndustriTest extends TestCase
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

    private function dataIndustri(array $timpa = []): array
    {
        return array_merge([
            'nama_perusahaan'     => 'PT Nusantara Digital',
            'alamat'              => 'Jalan Merdeka Nomor 10, Makassar',
            'telepon'             => '04111234567',
            'pembimbing_industri' => 'Bapak Sultan Alamsyah',
        ], $timpa);
    }

    private function buatIndustri(array $timpa = []): Perusahaan
    {
        return Perusahaan::create($this->dataIndustri($timpa));
    }

    public function test_admin_melihat_daftar_industri(): void
    {
        $this->buatIndustri();

        $this->actingAs($this->admin)
            ->get(route('admin.instruktur.index'))
            ->assertOk()
            ->assertSee('PT Nusantara Digital');
    }

    public function test_admin_membuka_form_tambah_industri(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.instruktur.create'))
            ->assertOk();
    }

    public function test_admin_menambah_data_industri(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.instruktur.store'), $this->dataIndustri())
            ->assertRedirect(route('admin.instruktur.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('perusahaans', [
            'nama_perusahaan'     => 'PT Nusantara Digital',
            'pembimbing_industri' => 'Bapak Sultan Alamsyah',
        ]);
    }

    public function test_nama_perusahaan_dan_alamat_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.instruktur.create'))
            ->post(route('admin.instruktur.store'), $this->dataIndustri([
                'nama_perusahaan' => '',
                'alamat'          => '',
            ]))
            ->assertSessionHasErrors(['nama_perusahaan', 'alamat']);
    }

    public function test_nama_pembimbing_industri_boleh_dikosongkan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.instruktur.store'), $this->dataIndustri([
                'pembimbing_industri' => '',
            ]))
            ->assertRedirect(route('admin.instruktur.index'));

        $this->assertDatabaseCount('perusahaans', 1);
    }

    public function test_admin_membuka_form_ubah_industri(): void
    {
        $industri = $this->buatIndustri();

        $this->actingAs($this->admin)
            ->get(route('admin.instruktur.edit', $industri))
            ->assertOk()
            ->assertSee('PT Nusantara Digital');
    }

    public function test_admin_memperbarui_data_industri(): void
    {
        $industri = $this->buatIndustri();

        $this->actingAs($this->admin)
            ->put(route('admin.instruktur.update', $industri), $this->dataIndustri([
                'nama_perusahaan'     => 'PT Nusantara Digital Mandiri',
                'pembimbing_industri' => 'Ibu Ayu Lestari',
            ]))
            ->assertRedirect(route('admin.instruktur.index'));

        $industri->refresh();

        $this->assertSame('PT Nusantara Digital Mandiri', $industri->nama_perusahaan);
        $this->assertSame('Ibu Ayu Lestari', $industri->pembimbing_industri);
    }

    public function test_admin_menghapus_industri_yang_belum_dipakai(): void
    {
        $industri = $this->buatIndustri();

        $this->actingAs($this->admin)
            ->from(route('admin.instruktur.index'))
            ->delete(route('admin.instruktur.destroy', $industri))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('perusahaans', ['id' => $industri->id]);
    }

    public function test_industri_yang_masih_dipakai_siswa_tidak_bisa_dihapus(): void
    {
        $industri = $this->buatIndustri();

        User::factory()->siswa()->create([
            'perusahaan_id' => $industri->id,
            'periode_id'    => $this->periode->id,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.instruktur.index'))
            ->delete(route('admin.instruktur.destroy', $industri))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('perusahaans', ['id' => $industri->id]);
    }

    public function test_hapus_semua_industri_mengosongkan_kolom_industri_siswa(): void
    {
        $industri = $this->buatIndustri();

        $siswa = User::factory()->siswa()->create([
            'perusahaan_id' => $industri->id,
            'periode_id'    => $this->periode->id,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.instruktur.index'))
            ->delete(route('admin.instruktur.hapus-semua'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('perusahaans', 0);
        $this->assertNull($siswa->fresh()->perusahaan_id);
    }

    public function test_hapus_semua_industri_saat_data_kosong_memberi_pesan_jelas(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.instruktur.index'))
            ->delete(route('admin.instruktur.hapus-semua'))
            ->assertSessionHas('error');
    }

    public function test_pencarian_industri_menyaring_hasil(): void
    {
        $this->buatIndustri(['nama_perusahaan' => 'PT Bengkel Jaya']);
        $this->buatIndustri(['nama_perusahaan' => 'CV Sinar Terang']);

        $this->actingAs($this->admin)
            ->get(route('admin.instruktur.index', ['q' => 'Bengkel']))
            ->assertOk()
            ->assertSee('PT Bengkel Jaya')
            ->assertDontSee('CV Sinar Terang');
    }

    public function test_admin_mengunduh_template_impor_industri(): void
    {
        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.instruktur.template'))
            ->assertOk();

        Excel::assertDownloaded('template-import-industri.xlsx');
    }

    public function test_admin_mengekspor_industri_ke_excel(): void
    {
        Excel::fake();
        $this->buatIndustri();

        $this->actingAs($this->admin)
            ->get(route('admin.instruktur.export.excel'))
            ->assertOk();
    }

    public function test_admin_mengekspor_industri_ke_pdf(): void
    {
        $this->buatIndustri();

        $this->actingAs($this->admin)
            ->get(route('admin.instruktur.export.pdf'))
            ->assertOk();
    }

    public function test_impor_industri_menolak_berkas_selain_excel(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.instruktur.index'))
            ->post(route('admin.instruktur.import'), [
                'file' => UploadedFile::fake()->create('industri.txt', 5, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_guru_tidak_boleh_membuka_menu_industri(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->get(route('admin.instruktur.index'))
            ->assertForbidden();
    }
}
