<?php

/** TARUH DI: tests/Feature/Pkl/AlurInformasiTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Informasi;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\HtmlSanitizer;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji menu Informasi & Panduan PKL sekaligus pembersih HTML
 * yang melindungi halaman itu dari penyisipan skrip berbahaya.
 */
class AlurInformasiTest extends TestCase
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

    private function dataInformasi(array $timpa = []): array
    {
        return array_merge([
            'judul'  => 'Panduan Pengisian Jurnal',
            'konten' => '<p>Isi jurnal setiap hari kerja.</p>',
            'tipe'   => 'panduan',
            'urutan' => 1,
        ], $timpa);
    }

    public function test_admin_melihat_daftar_informasi(): void
    {
        Informasi::create($this->dataInformasi());

        $this->actingAs($this->admin)
            ->get(route('admin.informasi.index'))
            ->assertOk()
            ->assertSee('Panduan Pengisian Jurnal');
    }

    public function test_admin_membuka_form_tambah_informasi(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.informasi.create'))
            ->assertOk();
    }

    public function test_admin_menambah_informasi_baru(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.informasi.store'), $this->dataInformasi())
            ->assertRedirect(route('admin.informasi.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('informasis', [
            'judul' => 'Panduan Pengisian Jurnal',
            'tipe'  => 'panduan',
        ]);
    }

    public function test_judul_dan_konten_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.informasi.create'))
            ->post(route('admin.informasi.store'), $this->dataInformasi([
                'judul'  => '',
                'konten' => '',
            ]))
            ->assertSessionHasErrors(['judul', 'konten']);
    }

    public function test_tipe_hanya_boleh_panduan_atau_faq(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.informasi.create'))
            ->post(route('admin.informasi.store'), $this->dataInformasi([
                'tipe' => 'pengumuman',
            ]))
            ->assertSessionHasErrors('tipe');
    }

    public function test_urutan_kosong_dianggap_nol(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.informasi.store'), $this->dataInformasi(['urutan' => null]));

        $this->assertDatabaseHas('informasis', [
            'judul'  => 'Panduan Pengisian Jurnal',
            'urutan' => 0,
        ]);
    }

    public function test_admin_memperbarui_informasi(): void
    {
        $informasi = Informasi::create($this->dataInformasi());

        $this->actingAs($this->admin)
            ->put(route('admin.informasi.update', $informasi), $this->dataInformasi([
                'judul' => 'Panduan Pengisian Jurnal Revisi',
                'tipe'  => 'faq',
            ]))
            ->assertRedirect(route('admin.informasi.index'));

        $informasi->refresh();

        $this->assertSame('Panduan Pengisian Jurnal Revisi', $informasi->judul);
        $this->assertSame('faq', $informasi->tipe);
    }

    public function test_admin_menghapus_informasi(): void
    {
        $informasi = Informasi::create($this->dataInformasi());

        $this->actingAs($this->admin)
            ->delete(route('admin.informasi.destroy', $informasi))
            ->assertRedirect(route('admin.informasi.index'));

        $this->assertDatabaseMissing('informasis', ['id' => $informasi->id]);
    }

    public function test_admin_melampirkan_berkas_pada_informasi(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.informasi.store'), $this->dataInformasi([
                'file' => UploadedFile::fake()->create('panduan.pdf', 100, 'application/pdf'),
            ]))
            ->assertRedirect(route('admin.informasi.index'));

        $informasi = Informasi::firstOrFail();

        $this->assertNotNull($informasi->file);
        Storage::disk('public')->assertExists($informasi->file);
    }

    public function test_mengganti_lampiran_menghapus_berkas_lama(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.informasi.store'), $this->dataInformasi([
                'file' => UploadedFile::fake()->create('lama.pdf', 100, 'application/pdf'),
            ]));

        $informasi = Informasi::firstOrFail();
        $berkasLama = $informasi->file;

        $this->actingAs($this->admin)
            ->put(route('admin.informasi.update', $informasi), $this->dataInformasi([
                'file' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
            ]));

        $informasi->refresh();

        $this->assertNotSame($berkasLama, $informasi->file);
        Storage::disk('public')->assertMissing($berkasLama);
        Storage::disk('public')->assertExists($informasi->file);
    }

    public function test_menghapus_informasi_ikut_menghapus_lampirannya(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.informasi.store'), $this->dataInformasi([
                'file' => UploadedFile::fake()->create('panduan.pdf', 100, 'application/pdf'),
            ]));

        $informasi = Informasi::firstOrFail();
        $berkas    = $informasi->file;

        $this->actingAs($this->admin)
            ->delete(route('admin.informasi.destroy', $informasi));

        Storage::disk('public')->assertMissing($berkas);
    }

    public function test_lampiran_menolak_jenis_berkas_berbahaya(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.informasi.create'))
            ->post(route('admin.informasi.store'), $this->dataInformasi([
                'file' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
            ]))
            ->assertSessionHasErrors('file');
    }

    public function test_siswa_dapat_membaca_halaman_informasi(): void
    {
        Informasi::create($this->dataInformasi());

        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->get(route('informasi.index'))
            ->assertOk()
            ->assertSee('Panduan Pengisian Jurnal');
    }

    public function test_guru_dapat_membaca_halaman_informasi(): void
    {
        Informasi::create($this->dataInformasi());

        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->get(route('informasi.index'))
            ->assertOk()
            ->assertSee('Panduan Pengisian Jurnal');
    }

    public function test_siswa_tidak_boleh_mengelola_informasi(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $this->actingAs($siswa)
            ->get(route('admin.informasi.index'))
            ->assertForbidden();
    }

    public function test_guru_tidak_boleh_menghapus_informasi(): void
    {
        $informasi = Informasi::create($this->dataInformasi());
        $guru      = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->delete(route('admin.informasi.destroy', $informasi))
            ->assertForbidden();

        $this->assertDatabaseHas('informasis', ['id' => $informasi->id]);
    }

    /*
    |----------------------------------------------------------------
    | Pengaman isi halaman: skrip berbahaya tidak boleh ikut tampil
    |----------------------------------------------------------------
    */

    public function test_halaman_informasi_tidak_menampilkan_tag_skrip(): void
    {
        Informasi::create($this->dataInformasi([
            'konten' => '<p>Halo</p><script>alert("dicuri")</script>',
        ]));

        $siswa = User::factory()->siswa()->create(['periode_id' => $this->periode->id]);

        $respons = $this->actingAs($siswa)->get(route('informasi.index'));

        $respons->assertOk();
        $this->assertStringNotContainsString('<script>alert', $respons->getContent());
    }

    public function test_pembersih_membuang_tag_skrip(): void
    {
        $hasil = HtmlSanitizer::bersihkan('<p>Aman</p><script>alert(1)</script>');

        $this->assertStringContainsString('Aman', $hasil);
        $this->assertStringNotContainsString('script', $hasil);
    }

    public function test_pembersih_membuang_atribut_kejadian(): void
    {
        $hasil = HtmlSanitizer::bersihkan('<p onclick="curi()">Teks</p>');

        $this->assertStringContainsString('Teks', $hasil);
        $this->assertStringNotContainsString('onclick', $hasil);
    }

    public function test_pembersih_memblokir_tautan_javascript(): void
    {
        $hasil = HtmlSanitizer::bersihkan('<a href="javascript:alert(1)">Klik</a>');

        $this->assertStringContainsString('Klik', $hasil);
        $this->assertStringNotContainsString('javascript:', $hasil);
    }

    public function test_pembersih_mengizinkan_tautan_biasa(): void
    {
        $hasil = HtmlSanitizer::bersihkan('<a href="https://sekolah.test/panduan">Panduan</a>');

        $this->assertStringContainsString('https://sekolah.test/panduan', $hasil);
    }

    public function test_pembersih_mempertahankan_format_dasar(): void
    {
        $hasil = HtmlSanitizer::bersihkan('<p><strong>Tebal</strong> dan <em>miring</em></p><ul><li>Poin</li></ul>');

        $this->assertStringContainsString('<strong>', $hasil);
        $this->assertStringContainsString('<em>', $hasil);
        $this->assertStringContainsString('<li>', $hasil);
    }

    public function test_pembersih_membuang_bingkai_dan_formulir(): void
    {
        $hasil = HtmlSanitizer::bersihkan('<iframe src="https://jahat.test"></iframe><form><input name="sandi"></form><p>Sisa</p>');

        $this->assertStringContainsString('Sisa', $hasil);
        $this->assertStringNotContainsString('iframe', $hasil);
        $this->assertStringNotContainsString('<form', $hasil);
        $this->assertStringNotContainsString('<input', $hasil);
    }

    public function test_pembersih_menambahkan_pengaman_pada_tautan_tab_baru(): void
    {
        $hasil = HtmlSanitizer::bersihkan('<a href="https://sekolah.test" target="_blank">Buka</a>');

        $this->assertStringContainsString('noopener', $hasil);
    }

    public function test_pembersih_mengembalikan_teks_kosong_untuk_masukan_kosong(): void
    {
        $this->assertSame('', HtmlSanitizer::bersihkan(null));
        $this->assertSame('', HtmlSanitizer::bersihkan('   '));
    }
}
