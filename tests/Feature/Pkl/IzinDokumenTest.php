<?php

/** TARUH DI: tests/Feature/Pkl/IzinDokumenTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Dokumen;
use App\Models\Pengaturan;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji hak akses berkas PKL: siapa boleh melihat & mengunduh apa,
 * ditambah pengelolaan dokumen dan Surat Tugas oleh admin.
 */
class IzinDokumenTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $guru;
    private User $guruLain;
    private User $siswa;
    private User $siswaLain;
    private PeriodePkl $periode;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        KonteksPeriode::lupakan();

        $this->periode  = PeriodePkl::factory()->aktif()->create();
        $this->admin    = User::factory()->admin()->create();
        $this->guru     = User::factory()->guru()->create();
        $this->guruLain = User::factory()->guru()->create();

        $this->siswa = User::factory()->siswa()->create([
            'name'       => 'Dewi Anggraini',
            'guru_id'    => $this->guru->id,
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);

        $this->siswaLain = User::factory()->siswa()->create([
            'name'       => 'Bagus Pratama',
            'guru_id'    => $this->guruLain->id,
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);
    }

    private function siapkanBerkas(User $siswa, string $jenis): string
    {
        $path = 'dokumen_pkl/' . $jenis . '-' . $siswa->id . '.pdf';
        Storage::disk('public')->put($path, 'isi berkas percobaan');

        $dokumen = Dokumen::firstOrNew(['siswa_id' => $siswa->id]);
        $dokumen->{$jenis} = $path;
        $dokumen->save();

        return $path;
    }

    private function siapkanSuratTugas(): string
    {
        $path = 'dokumen_pkl/surat-tugas.pdf';
        Storage::disk('public')->put($path, 'isi surat tugas');
        Pengaturan::simpan('surat_tugas', $path);

        return $path;
    }

    public function test_admin_boleh_semua_aksi_pada_semua_jenis_berkas(): void
    {
        foreach (['surat_tugas', 'surat_penerimaan', 'laporan_akhir'] as $jenis) {
            foreach (['lihat', 'download'] as $aksi) {
                $this->assertTrue(
                    Dokumen::boleh($aksi, $jenis, $this->admin, $this->siswa),
                    "Admin seharusnya boleh {$aksi} {$jenis}"
                );
            }
        }
    }

    public function test_siswa_boleh_melihat_berkasnya_sendiri(): void
    {
        $this->assertTrue(Dokumen::boleh('lihat', 'surat_penerimaan', $this->siswa, $this->siswa));
        $this->assertTrue(Dokumen::boleh('lihat', 'laporan_akhir', $this->siswa, $this->siswa));
    }

    public function test_siswa_tidak_boleh_menyentuh_berkas_siswa_lain(): void
    {
        $this->assertFalse(Dokumen::boleh('lihat', 'laporan_akhir', $this->siswa, $this->siswaLain));
        $this->assertFalse(Dokumen::boleh('download', 'laporan_akhir', $this->siswa, $this->siswaLain));
    }

    public function test_siswa_tidak_diberi_hak_mengunduh_berkas_resmi(): void
    {
        $this->assertFalse(Dokumen::boleh('download', 'surat_penerimaan', $this->siswa, $this->siswa));
        $this->assertFalse(Dokumen::boleh('download', 'laporan_akhir', $this->siswa, $this->siswa));
    }

    public function test_guru_boleh_mengakses_berkas_siswa_bimbingannya(): void
    {
        $this->assertTrue(Dokumen::boleh('lihat', 'laporan_akhir', $this->guru, $this->siswa));
        $this->assertTrue(Dokumen::boleh('download', 'laporan_akhir', $this->guru, $this->siswa));
    }

    public function test_guru_tidak_boleh_mengakses_berkas_siswa_guru_lain(): void
    {
        $this->assertFalse(Dokumen::boleh('lihat', 'laporan_akhir', $this->guru, $this->siswaLain));
        $this->assertFalse(Dokumen::boleh('download', 'laporan_akhir', $this->guru, $this->siswaLain));
    }

    public function test_guru_kehilangan_akses_bila_siswa_tidak_lagi_aktif(): void
    {
        $this->siswa->update(['status_pkl' => 'selesai']);

        $this->assertFalse(Dokumen::boleh('lihat', 'laporan_akhir', $this->guru, $this->siswa->fresh()));
    }

    public function test_hanya_admin_yang_boleh_mengunggah_surat_tugas(): void
    {
        $this->assertTrue(Dokumen::boleh('upload', 'surat_tugas', $this->admin, $this->siswa));
        $this->assertFalse(Dokumen::boleh('upload', 'surat_tugas', $this->guru, $this->siswa));
        $this->assertFalse(Dokumen::boleh('upload', 'surat_tugas', $this->siswa, $this->siswa));
    }

    public function test_jenis_berkas_yang_tidak_dikenal_selalu_ditolak(): void
    {
        $this->assertFalse(Dokumen::boleh('lihat', 'berkas_hantu', $this->admin, $this->siswa));
    }

    public function test_siswa_melihat_laporan_akhirnya_sendiri(): void
    {
        $this->siapkanBerkas($this->siswa, 'laporan_akhir');

        $this->actingAs($this->siswa)
            ->get(route('dokumen.lihat', [$this->siswa->id, 'laporan_akhir']))
            ->assertOk();
    }

    public function test_siswa_ditolak_saat_membuka_berkas_siswa_lain(): void
    {
        $this->siapkanBerkas($this->siswaLain, 'laporan_akhir');

        $this->actingAs($this->siswa)
            ->get(route('dokumen.lihat', [$this->siswaLain->id, 'laporan_akhir']))
            ->assertForbidden();
    }

    public function test_siswa_ditolak_saat_mengunduh_laporan_akhirnya(): void
    {
        $this->siapkanBerkas($this->siswa, 'laporan_akhir');

        $this->actingAs($this->siswa)
            ->get(route('dokumen.download', [$this->siswa->id, 'laporan_akhir']))
            ->assertForbidden();
    }

    public function test_guru_mengunduh_laporan_akhir_siswa_bimbingannya(): void
    {
        $this->siapkanBerkas($this->siswa, 'laporan_akhir');

        $this->actingAs($this->guru)
            ->get(route('dokumen.download', [$this->siswa->id, 'laporan_akhir']))
            ->assertOk();
    }

    public function test_guru_ditolak_saat_mengunduh_laporan_siswa_guru_lain(): void
    {
        $this->siapkanBerkas($this->siswaLain, 'laporan_akhir');

        $this->actingAs($this->guru)
            ->get(route('dokumen.download', [$this->siswaLain->id, 'laporan_akhir']))
            ->assertForbidden();
    }

    public function test_admin_mengunduh_surat_penerimaan_siswa_mana_pun(): void
    {
        $this->siapkanBerkas($this->siswaLain, 'surat_penerimaan');

        $this->actingAs($this->admin)
            ->get(route('dokumen.download', [$this->siswaLain->id, 'surat_penerimaan']))
            ->assertOk();
    }

    public function test_berkas_yang_belum_diunggah_menghasilkan_halaman_tidak_ditemukan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dokumen.lihat', [$this->siswa->id, 'laporan_akhir']))
            ->assertNotFound();
    }

    public function test_jenis_berkas_tidak_dikenal_menghasilkan_halaman_tidak_ditemukan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dokumen.lihat', [$this->siswa->id, 'berkas_hantu']))
            ->assertNotFound();
    }

    public function test_surat_tugas_tidak_bisa_diakses_lewat_alamat_per_siswa(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dokumen.lihat', [$this->siswa->id, 'surat_tugas']))
            ->assertNotFound();
    }

    public function test_admin_mengunggah_surat_tugas(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.dokumen.surat-tugas.index'))
            ->post(route('admin.dokumen.surat-tugas'), [
                'surat_tugas' => UploadedFile::fake()->create('surat-tugas.pdf', 200, 'application/pdf'),
            ])
            ->assertSessionHas('success');

        $path = Pengaturan::ambil('surat_tugas');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_mengunggah_surat_tugas_baru_menghapus_yang_lama(): void
    {
        $lama = $this->siapkanSuratTugas();

        $this->actingAs($this->admin)
            ->from(route('admin.dokumen.surat-tugas.index'))
            ->post(route('admin.dokumen.surat-tugas'), [
                'surat_tugas' => UploadedFile::fake()->create('baru.pdf', 200, 'application/pdf'),
            ]);

        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists(Pengaturan::ambil('surat_tugas'));
    }

    public function test_surat_tugas_hanya_menerima_berkas_pdf(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.dokumen.surat-tugas.index'))
            ->post(route('admin.dokumen.surat-tugas'), [
                'surat_tugas' => UploadedFile::fake()->image('bukan-pdf.jpg'),
            ])
            ->assertSessionHasErrors('surat_tugas');
    }

    public function test_guru_tidak_boleh_mengunggah_surat_tugas(): void
    {
        $this->actingAs($this->guru)
            ->post(route('admin.dokumen.surat-tugas'), [
                'surat_tugas' => UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf'),
            ])
            ->assertForbidden();
    }

    public function test_siswa_boleh_melihat_surat_tugas(): void
    {
        $this->siapkanSuratTugas();

        $this->actingAs($this->siswa)
            ->get(route('dokumen.surat-tugas.lihat'))
            ->assertOk();
    }

    public function test_siswa_boleh_mengunduh_surat_tugas(): void
    {
        $this->siapkanSuratTugas();

        $this->actingAs($this->siswa)
            ->get(route('dokumen.surat-tugas.download'))
            ->assertOk();
    }

    public function test_guru_boleh_mengunduh_surat_tugas(): void
    {
        $this->siapkanSuratTugas();

        $this->actingAs($this->guru)
            ->get(route('dokumen.surat-tugas.download'))
            ->assertOk();
    }

    public function test_surat_tugas_yang_belum_diunggah_menghasilkan_tidak_ditemukan(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('dokumen.surat-tugas.lihat'))
            ->assertNotFound();
    }

    public function test_admin_melihat_rekap_dokumen_seluruh_siswa(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dokumen.index'))
            ->assertOk()
            ->assertSee('Dewi Anggraini');
    }

    public function test_admin_mengunggah_dokumen_milik_siswa(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.dokumen.index'))
            ->post(route('admin.dokumen.store', $this->siswa->id), [
                'surat_penerimaan' => UploadedFile::fake()->create('penerimaan.pdf', 200, 'application/pdf'),
                'laporan_akhir'    => UploadedFile::fake()->create('laporan.pdf', 400, 'application/pdf'),
            ])
            ->assertSessionHas('success');

        $dokumen = Dokumen::where('siswa_id', $this->siswa->id)->firstOrFail();

        $this->assertNotNull($dokumen->surat_penerimaan);
        $this->assertNotNull($dokumen->laporan_akhir);
        Storage::disk('public')->assertExists($dokumen->laporan_akhir);
    }

    public function test_laporan_akhir_terlalu_besar_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.dokumen.index'))
            ->post(route('admin.dokumen.store', $this->siswa->id), [
                'laporan_akhir' => UploadedFile::fake()->create('laporan.pdf', 6000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('laporan_akhir');
    }

    public function test_admin_menghapus_salah_satu_dokumen_siswa(): void
    {
        $path = $this->siapkanBerkas($this->siswa, 'laporan_akhir');

        $this->actingAs($this->admin)
            ->from(route('admin.dokumen.index'))
            ->delete(route('admin.dokumen.destroy', [$this->siswa->id, 'laporan_akhir']))
            ->assertSessionHas('success');

        Storage::disk('public')->assertMissing($path);
        $this->assertNull(Dokumen::where('siswa_id', $this->siswa->id)->first()->laporan_akhir);
    }

    public function test_admin_tidak_bisa_menghapus_jenis_dokumen_yang_tidak_dikenal(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.dokumen.index'))
            ->delete(route('admin.dokumen.destroy', [$this->siswa->id, 'berkas_hantu']))
            ->assertNotFound();
    }

    public function test_guru_melihat_daftar_dokumen_siswa_bimbingannya_saja(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.dokumen.index'))
            ->assertOk()
            ->assertSee('Dewi Anggraini')
            ->assertDontSee('Bagus Pratama');
    }

    public function test_siswa_mengunggah_dokumennya_sendiri(): void
    {
        $this->actingAs($this->siswa)
            ->from(route('siswa.dokumen.index'))
            ->post(route('siswa.dokumen.store'), [
                'laporan_akhir' => UploadedFile::fake()->create('laporan.pdf', 400, 'application/pdf'),
            ])
            ->assertSessionHas('success');

        $this->assertNotNull(Dokumen::where('siswa_id', $this->siswa->id)->firstOrFail()->laporan_akhir);
    }

    public function test_siswa_tidak_boleh_membuka_pengelolaan_dokumen_admin(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('admin.dokumen.index'))
            ->assertForbidden();
    }
}
