<?php

/** TARUH DI: tests/Feature/Pkl/AlurEvaluasiAdminTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji menu Admin > Evaluasi (Lembar Observasi & Penilaian)
 * serta halaman validasi milik Wakasek.
 */
class AlurEvaluasiAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $guru;
    private User $siswa;
    private PeriodePkl $periode;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        KonteksPeriode::lupakan();

        $this->periode = PeriodePkl::factory()->aktif()->create();
        $this->admin   = User::factory()->admin()->create();
        $this->guru    = User::factory()->guru()->create(['name' => 'Bu Ratna Wijaya']);

        $this->siswa = User::factory()->siswa()->create([
            'name'       => 'Dewi Anggraini',
            'nisn'       => '1234500001',
            'guru_id'    => $this->guru->id,
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);
    }

    private function dataObservasi(array $timpa = []): array
    {
        return array_merge([
            'user_id'          => $this->siswa->id,
            'hari_tanggal'     => now()->toDateString(),
            'pekerjaan_projek' => 'Pemasangan jaringan kantor',
            'items'            => [
                ['permasalahan' => 'Kabel kurang panjang', 'solusi' => 'Menyambung dengan konektor'],
            ],
        ], $timpa);
    }

    private function dataPenilaian(array $timpa = []): array
    {
        return array_merge([
            'user_id'                 => $this->siswa->id,
            'skor_soft_skill'         => 80,
            'deskripsi_soft_skill'    => 'Sopan dan disiplin',
            'skor_hard_skill'         => 90,
            'deskripsi_hard_skill'    => 'Terampil memasang jaringan',
            'skor_pengembangan'       => 70,
            'deskripsi_pengembangan'  => 'Mau belajar hal baru',
            'skor_kewirausahaan'      => 60,
            'deskripsi_kewirausahaan' => 'Perlu ditingkatkan',
            'skor_laporan'            => 100,
            'deskripsi_laporan'       => 'Laporan rapi dan lengkap',
            'skor_presentasi'         => 80,
            'deskripsi_presentasi'    => 'Percaya diri saat presentasi',
            'catatan_guru'            => 'Pertahankan prestasinya',
        ], $timpa);
    }

    private function buatObservasi(string $status = 'draft'): Observasi
    {
        $observasi = Observasi::create([
            'user_id'      => $this->siswa->id,
            'guru_id'      => $this->guru->id,
            'hari_tanggal' => now()->toDateString(),
            'status'       => $status,
        ]);

        $observasi->items()->create([
            'permasalahan' => 'Kabel kurang panjang',
            'solusi'       => 'Menyambung dengan konektor',
        ]);

        return $observasi;
    }

    /*
    |----------------------------------------------------------------
    | LEMBAR OBSERVASI (ADMIN)
    |----------------------------------------------------------------
    */

    public function test_admin_melihat_daftar_lembar_observasi(): void
    {
        $this->buatObservasi();

        $this->actingAs($this->admin)
            ->get(route('admin.evaluasi.observasi'))
            ->assertOk()
            ->assertSee('Dewi Anggraini');
    }

    public function test_admin_menambah_lembar_observasi(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.observasi.store'), $this->dataObservasi())
            ->assertRedirect(route('admin.evaluasi.observasi'))
            ->assertSessionHas('success');

        $observasi = Observasi::firstOrFail();

        $this->assertSame('draft', $observasi->status);
        $this->assertSame($this->guru->id, (int) $observasi->guru_id);
        $this->assertCount(1, $observasi->items);
    }

    public function test_observasi_wajib_punya_minimal_satu_poin(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.evaluasi.observasi'))
            ->post(route('admin.evaluasi.observasi.store'), $this->dataObservasi(['items' => []]))
            ->assertSessionHasErrors('items');
    }

    public function test_permasalahan_dan_solusi_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.evaluasi.observasi'))
            ->post(route('admin.evaluasi.observasi.store'), $this->dataObservasi([
                'items' => [['permasalahan' => '', 'solusi' => '']],
            ]))
            ->assertSessionHasErrors(['items.0.permasalahan', 'items.0.solusi']);
    }

    public function test_observasi_baru_terikat_periode_aktif(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.observasi.store'), $this->dataObservasi());

        $this->assertSame($this->periode->id, (int) Observasi::firstOrFail()->periode_id);
    }

    public function test_mengubah_observasi_membatalkan_validasi_sebelumnya(): void
    {
        $observasi = $this->buatObservasi('tervalidasi');
        $observasi->update([
            'validated_by_guru_id' => $this->guru->id,
            'validated_at'         => now(),
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.evaluasi.observasi.update', $observasi), $this->dataObservasi([
                'pekerjaan_projek' => 'Pekerjaan yang sudah diperbarui',
            ]))
            ->assertRedirect(route('admin.evaluasi.observasi'));

        $observasi->refresh();

        $this->assertSame('draft', $observasi->status);
        $this->assertNull($observasi->validated_by_guru_id);
        $this->assertNull($observasi->validated_at);
        $this->assertSame('Pekerjaan yang sudah diperbarui', $observasi->pekerjaan_projek);
    }

    public function test_mengubah_observasi_mengganti_seluruh_poinnya(): void
    {
        $observasi = $this->buatObservasi();

        $this->actingAs($this->admin)
            ->put(route('admin.evaluasi.observasi.update', $observasi), $this->dataObservasi([
                'items' => [
                    ['permasalahan' => 'Masalah baru A', 'solusi' => 'Solusi A'],
                    ['permasalahan' => 'Masalah baru B', 'solusi' => 'Solusi B'],
                ],
            ]));

        $observasi->refresh()->load('items');

        $this->assertCount(2, $observasi->items);
        $this->assertSame('Masalah baru A', $observasi->items->first()->permasalahan);
    }

    public function test_validasi_observasi_wajib_melampirkan_dua_foto(): void
    {
        $observasi = $this->buatObservasi();

        $this->actingAs($this->admin)
            ->from(route('admin.evaluasi.observasi'))
            ->put(route('admin.evaluasi.observasi.validasi', $observasi), [])
            ->assertSessionHasErrors(['foto_dokumentasi', 'foto_lembar_observasi']);

        $this->assertSame('draft', $observasi->fresh()->status);
    }

    public function test_admin_memvalidasi_lembar_observasi(): void
    {
        $observasi = $this->buatObservasi();

        $this->actingAs($this->admin)
            ->put(route('admin.evaluasi.observasi.validasi', $observasi), [
                'foto_dokumentasi'      => UploadedFile::fake()->image('dokumentasi.jpg', 800, 600),
                'foto_lembar_observasi' => UploadedFile::fake()->image('lembar.jpg', 800, 600),
            ])
            ->assertRedirect(route('admin.evaluasi.observasi'))
            ->assertSessionHas('success');

        $observasi->refresh();

        $this->assertSame('tervalidasi', $observasi->status);
        $this->assertNotNull($observasi->foto_dokumentasi);
        $this->assertNotNull($observasi->foto_lembar_observasi);
        $this->assertNotNull($observasi->validated_at);

        Storage::disk('public')->assertExists($observasi->foto_dokumentasi);
        Storage::disk('public')->assertExists($observasi->foto_lembar_observasi);
    }

    public function test_admin_membatalkan_validasi_observasi(): void
    {
        $observasi = $this->buatObservasi('tervalidasi');
        $observasi->update([
            'validated_by_guru_id' => $this->guru->id,
            'validated_at'         => now(),
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.evaluasi.observasi.batal', $observasi))
            ->assertRedirect(route('admin.evaluasi.observasi'));

        $observasi->refresh();

        $this->assertSame('draft', $observasi->status);
        $this->assertNull($observasi->validated_at);
    }

    public function test_admin_menghapus_lembar_observasi(): void
    {
        $observasi = $this->buatObservasi();

        $this->actingAs($this->admin)
            ->delete(route('admin.evaluasi.observasi.destroy', $observasi))
            ->assertRedirect(route('admin.evaluasi.observasi'));

        $this->assertDatabaseMissing('observasis', ['id' => $observasi->id]);
    }

    /*
    |----------------------------------------------------------------
    | PENILAIAN (ADMIN)
    |----------------------------------------------------------------
    */

    public function test_admin_melihat_halaman_penilaian(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.evaluasi.penilaian'))
            ->assertOk()
            ->assertSee('Dewi Anggraini');
    }

    public function test_admin_menyimpan_penilaian_dan_nilai_akhir_dihitung_otomatis(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian())
            ->assertRedirect(route('admin.evaluasi.penilaian'))
            ->assertSessionHas('success');

        $nilai = Nilai::firstOrFail();

        // (80 + 90 + 70 + 60 + 100 + 80) / 6 = 80
        $this->assertEquals(80.0, (float) $nilai->nilai_akhir);
        $this->assertEquals(80.0, (float) $nilai->nilai_guru);
        $this->assertEquals(100.0, (float) $nilai->nilai_laporan);
    }

    public function test_penilaian_baru_terikat_periode_aktif(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian());

        $this->assertSame($this->periode->id, (int) Nilai::firstOrFail()->periode_id);
    }

    public function test_skor_di_luar_rentang_nol_sampai_seratus_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.evaluasi.penilaian'))
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian([
                'skor_soft_skill' => 150,
            ]))
            ->assertSessionHasErrors('skor_soft_skill');
    }

    public function test_deskripsi_setiap_komponen_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.evaluasi.penilaian'))
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian([
                'deskripsi_soft_skill' => '',
            ]))
            ->assertSessionHasErrors('deskripsi_soft_skill');
    }

    public function test_menyimpan_penilaian_dua_kali_tidak_menggandakan_data(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian());

        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian([
                'skor_soft_skill' => 100,
            ]));

        $this->assertSame(1, Nilai::where('user_id', $this->siswa->id)->count());
    }

    public function test_admin_memperbarui_penilaian(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian());

        $nilai = Nilai::firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.evaluasi.penilaian.update', $nilai), $this->dataPenilaian([
                'skor_kewirausahaan' => 90,
            ]))
            ->assertRedirect(route('admin.evaluasi.penilaian'));

        // (80 + 90 + 70 + 90 + 100 + 80) / 6 = 85
        $this->assertEquals(85.0, (float) $nilai->fresh()->nilai_akhir);
    }

    public function test_admin_mengunggah_foto_lembar_penilaian_instruktur(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian([
                'foto_lembar_instruktur' => UploadedFile::fake()->image('lembar.jpg', 800, 600),
            ]));

        $nilai = Nilai::firstOrFail();

        $this->assertNotNull($nilai->foto_lembar_instruktur);
        Storage::disk('public')->assertExists($nilai->foto_lembar_instruktur);
    }

    public function test_admin_menghapus_penilaian(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian());

        $nilai = Nilai::firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('admin.evaluasi.penilaian.destroy', $nilai))
            ->assertRedirect(route('admin.evaluasi.penilaian'));

        $this->assertDatabaseMissing('nilais', ['id' => $nilai->id]);
    }

    /*
    |----------------------------------------------------------------
    | HALAMAN WAKASEK
    |----------------------------------------------------------------
    */

    public function test_wakasek_melihat_daftar_observasi_yang_diajukan(): void
    {
        $this->buatObservasi('diajukan');
        $wakasek = User::factory()->wakasek()->create();

        $this->actingAs($wakasek)
            ->get(route('guru.wakasek.observasi'))
            ->assertOk()
            ->assertSee('Dewi Anggraini');
    }

    public function test_guru_biasa_tidak_boleh_membuka_halaman_wakasek(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.wakasek.observasi'))
            ->assertForbidden();
    }

    public function test_wakasek_memvalidasi_observasi_yang_diajukan(): void
    {
        $observasi = $this->buatObservasi('diajukan');
        $wakasek   = User::factory()->wakasek()->create();

        $this->actingAs($wakasek)
            ->from(route('guru.wakasek.observasi'))
            ->put(route('guru.wakasek.observasi.validasi', $observasi->id))
            ->assertSessionHas('success');

        $observasi->refresh();

        $this->assertSame('tervalidasi', $observasi->status);
        $this->assertSame($wakasek->id, (int) $observasi->validated_by_guru_id);
    }

    public function test_wakasek_tidak_bisa_memvalidasi_observasi_yang_masih_draft(): void
    {
        $observasi = $this->buatObservasi('draft');
        $wakasek   = User::factory()->wakasek()->create();

        $this->actingAs($wakasek)
            ->from(route('guru.wakasek.observasi'))
            ->put(route('guru.wakasek.observasi.validasi', $observasi->id))
            ->assertNotFound();
    }

    public function test_wakasek_membatalkan_validasi_observasi(): void
    {
        $observasi = $this->buatObservasi('tervalidasi');
        $wakasek   = User::factory()->wakasek()->create();

        $this->actingAs($wakasek)
            ->from(route('guru.wakasek.observasi'))
            ->put(route('guru.wakasek.observasi.batal', $observasi->id))
            ->assertSessionHas('success');

        $observasi->refresh();

        $this->assertSame('diajukan', $observasi->status);
        $this->assertNull($observasi->validated_by_guru_id);
    }

    public function test_siswa_tidak_boleh_membuka_menu_evaluasi_admin(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('admin.evaluasi.penilaian'))
            ->assertForbidden();
    }

    public function test_guru_tidak_boleh_menghapus_penilaian_lewat_menu_admin(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.evaluasi.penilaian.store'), $this->dataPenilaian());

        $nilai = Nilai::firstOrFail();

        $this->actingAs($this->guru)
            ->delete(route('admin.evaluasi.penilaian.destroy', $nilai))
            ->assertForbidden();

        $this->assertDatabaseHas('nilais', ['id' => $nilai->id]);
    }
}
