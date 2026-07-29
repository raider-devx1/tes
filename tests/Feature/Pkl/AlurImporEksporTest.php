<?php

/** TARUH DI: tests/Feature/Pkl/AlurImporEksporTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Pengaturan;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Menguji ekspor/impor data siswa, tindakan massal per periode,
 * serta perkakas pendukung (pengaturan & pemadat gambar).
 */
class AlurImporEksporTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PeriodePkl $periode;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        KonteksPeriode::lupakan();

        $this->periode = PeriodePkl::factory()->aktif()->create();
        $this->admin   = User::factory()->admin()->create();
    }

    /*
    |----------------------------------------------------------------
    | EKSPOR & IMPOR DATA SISWA
    |----------------------------------------------------------------
    */

    public function test_admin_mengunduh_template_impor_siswa(): void
    {
        Excel::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.siswa.template'))
            ->assertOk();

        Excel::assertDownloaded('template-import-siswa.xlsx');
    }

    public function test_admin_mengekspor_data_siswa_ke_excel(): void
    {
        Excel::fake();

        User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.siswa.export.excel'))
            ->assertOk();
    }

    public function test_admin_mengekspor_data_siswa_ke_pdf(): void
    {
        User::factory()->siswa()->create([
            'name'       => 'Dewi Anggraini',
            'periode_id' => $this->periode->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.siswa.export.pdf'))
            ->assertOk();
    }

    public function test_ekspor_siswa_dapat_disaring_menurut_status(): void
    {
        User::factory()->siswa()->create([
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.siswa.export.pdf', ['status' => 'aktif']))
            ->assertOk();
    }

    public function test_impor_siswa_menolak_berkas_selain_excel(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->post(route('admin.siswa.import'), [
                'file' => UploadedFile::fake()->create('daftar.txt', 5, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_impor_siswa_wajib_menyertakan_berkas(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->post(route('admin.siswa.import'), [])
            ->assertSessionHasErrors('file');
    }

    public function test_guru_tidak_boleh_mengekspor_data_siswa(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->get(route('admin.siswa.export.excel'))
            ->assertForbidden();
    }

    /*
    |----------------------------------------------------------------
    | TINDAKAN MASSAL PER PERIODE
    |----------------------------------------------------------------
    */

    public function test_admin_mengarsipkan_seluruh_siswa_satu_periode(): void
    {
        $siswa = User::factory()->count(3)->siswa()->create([
            'periode_id' => $this->periode->id,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->delete(route('admin.siswa.hapus-periode'), [
                'periode_id' => $this->periode->id,
            ])
            ->assertSessionHas('success');

        foreach ($siswa as $satu) {
            $this->assertSoftDeleted('users', ['id' => $satu->id]);
        }
    }

    public function test_mengarsipkan_periode_kosong_memberi_pesan_jelas(): void
    {
        $periodeLain = PeriodePkl::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->delete(route('admin.siswa.hapus-periode'), [
                'periode_id' => $periodeLain->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_mengarsipkan_periode_wajib_memilih_periode(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->delete(route('admin.siswa.hapus-periode'), [])
            ->assertSessionHasErrors('periode_id');
    }

    public function test_arsip_hanya_mengenai_periode_yang_dipilih(): void
    {
        $periodeLain = PeriodePkl::factory()->create();

        $siswaTarget = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);
        $siswaAman   = User::factory()->siswa()->create(['periode_id' => $periodeLain->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->delete(route('admin.siswa.hapus-periode'), [
                'periode_id' => $this->periode->id,
            ]);

        $this->assertSoftDeleted('users', ['id' => $siswaTarget->id]);
        $this->assertDatabaseHas('users', ['id' => $siswaAman->id, 'deleted_at' => null]);
    }

    public function test_admin_mengubah_status_pkl_seluruh_siswa_satu_periode(): void
    {
        $siswa = User::factory()->count(2)->siswa()->create([
            'periode_id' => $this->periode->id,
            'status_pkl' => 'belum',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->post(route('admin.periode.update-status-siswa'), [
                'periode_id' => $this->periode->id,
                'status_pkl' => 'aktif',
            ])
            ->assertSessionHas('success');

        foreach ($siswa as $satu) {
            $this->assertSame('aktif', $satu->fresh()->status_pkl);
        }
    }

    public function test_status_pkl_massal_harus_pilihan_yang_dikenal(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->post(route('admin.periode.update-status-siswa'), [
                'periode_id' => $this->periode->id,
                'status_pkl' => 'libur',
            ])
            ->assertSessionHasErrors('status_pkl');
    }

    public function test_ubah_status_massal_pada_periode_kosong_memberi_pesan_jelas(): void
    {
        $periodeLain = PeriodePkl::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.siswa.index'))
            ->post(route('admin.periode.update-status-siswa'), [
                'periode_id' => $periodeLain->id,
                'status_pkl' => 'aktif',
            ])
            ->assertSessionHas('error');
    }

    public function test_guru_tidak_boleh_mengubah_status_massal(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->post(route('admin.periode.update-status-siswa'), [
                'periode_id' => $this->periode->id,
                'status_pkl' => 'aktif',
            ])
            ->assertForbidden();
    }

    /*
    |----------------------------------------------------------------
    | PERKAKAS PENDUKUNG: PENGATURAN
    |----------------------------------------------------------------
    */

    public function test_pengaturan_mengembalikan_nilai_bawaan_bila_kunci_belum_ada(): void
    {
        $this->assertSame('08:00', Pengaturan::ambil('absensi_jam_masuk', '08:00'));
        $this->assertNull(Pengaturan::ambil('kunci_yang_tidak_ada'));
    }

    public function test_pengaturan_menyimpan_dan_membaca_kembali_nilai(): void
    {
        Pengaturan::simpan('absensi_jam_masuk', '07:15');

        $this->assertSame('07:15', Pengaturan::ambil('absensi_jam_masuk', '08:00'));
        $this->assertDatabaseHas('pengaturans', [
            'kunci' => 'absensi_jam_masuk',
            'nilai' => '07:15',
        ]);
    }

    public function test_menyimpan_kunci_yang_sama_tidak_menggandakan_baris(): void
    {
        Pengaturan::simpan('absensi_jam_masuk', '07:15');
        Pengaturan::simpan('absensi_jam_masuk', '08:45');

        $this->assertSame(1, Pengaturan::where('kunci', 'absensi_jam_masuk')->count());
        $this->assertSame('08:45', Pengaturan::ambil('absensi_jam_masuk'));
    }

    public function test_pengaturan_dapat_dibaca_sebagai_satu_kumpulan(): void
    {
        Pengaturan::simpan('absensi_jam_masuk', '07:15');
        Pengaturan::simpan('absensi_jam_pulang', '15:45');

        $semua = Pengaturan::semua();

        $this->assertSame('07:15', $semua['absensi_jam_masuk']);
        $this->assertSame('15:45', $semua['absensi_jam_pulang']);
    }

    /*
    |----------------------------------------------------------------
    | PERKAKAS PENDUKUNG: PEMADAT GAMBAR
    |----------------------------------------------------------------
    */

    public function test_pemadat_gambar_menyimpan_berkas_ke_folder_tujuan(): void
    {
        $path = ImageCompressor::store(
            UploadedFile::fake()->image('foto.jpg', 800, 600),
            'uji-pemadatan'
        );

        $this->assertNotNull($path);
        $this->assertStringStartsWith('uji-pemadatan/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_pemadat_gambar_memperkecil_gambar_yang_terlalu_besar(): void
    {
        $path = ImageCompressor::store(
            UploadedFile::fake()->image('besar.jpg', 3000, 2000),
            'uji-pemadatan'
        );

        $this->assertNotNull($path);

        [$lebar, $tinggi] = getimagesize(Storage::disk('public')->path($path));

        $this->assertLessThanOrEqual(1280, $lebar);
        $this->assertLessThanOrEqual(1280, $tinggi);
    }

    public function test_pemadat_gambar_tidak_membesarkan_gambar_kecil(): void
    {
        $path = ImageCompressor::store(
            UploadedFile::fake()->image('kecil.jpg', 400, 300),
            'uji-pemadatan'
        );

        [$lebar, $tinggi] = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame(400, $lebar);
        $this->assertSame(300, $tinggi);
    }

    public function test_pemadat_gambar_mengembalikan_kosong_bila_tidak_ada_berkas(): void
    {
        $this->assertNull(ImageCompressor::store(null, 'uji-pemadatan'));
    }

    public function test_pemadat_gambar_menghasilkan_nama_berkas_yang_berbeda(): void
    {
        $pertama = ImageCompressor::store(UploadedFile::fake()->image('a.jpg', 500, 500), 'uji-pemadatan');
        $kedua   = ImageCompressor::store(UploadedFile::fake()->image('a.jpg', 500, 500), 'uji-pemadatan');

        $this->assertNotSame($pertama, $kedua);
        Storage::disk('public')->assertExists($pertama);
        Storage::disk('public')->assertExists($kedua);
    }
}
