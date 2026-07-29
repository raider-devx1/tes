<?php

/** TARUH DI: tests/Feature/Pkl/AlurCetakPdfTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\PeriodePkl;
use App\Models\Perusahaan;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji seluruh tombol Cetak PDF beserta pembatasan
 * siapa yang boleh mencetak berkas milik siapa.
 */
class AlurCetakPdfTest extends TestCase
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
        $this->guru     = User::factory()->guru()->create(['name' => 'Bu Ratna Wijaya']);
        $this->guruLain = User::factory()->guru()->create();

        $industri = Perusahaan::create([
            'nama_perusahaan'     => 'PT Nusantara Digital',
            'alamat'              => 'Jalan Merdeka Nomor 10',
            'pembimbing_industri' => 'Bapak Sultan',
        ]);

        $this->siswa = User::factory()->siswa()->create([
            'name'          => 'Dewi Anggraini',
            'guru_id'       => $this->guru->id,
            'perusahaan_id' => $industri->id,
            'periode_id'    => $this->periode->id,
            'status_pkl'    => 'aktif',
        ]);

        $this->siswaLain = User::factory()->siswa()->create([
            'name'       => 'Bagus Pratama',
            'guru_id'    => $this->guruLain->id,
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);
    }

    private function buatJurnal(User $siswa): Jurnal
    {
        $jurnal = Jurnal::create([
            'siswa_id'     => $siswa->id,
            'hari_tanggal' => now()->toDateString(),
            'status'       => 'disetujui',
        ]);

        $jurnal->items()->create(['unit_kerja' => 'Merakit perangkat jaringan']);

        return $jurnal;
    }

    private function buatAbsensi(User $siswa): Absensi
    {
        return Absensi::create([
            'siswa_id'        => $siswa->id,
            'tanggal'         => now()->toDateString(),
            'status'          => 'Hadir',
            'jam_masuk'       => '08:00:00',
            'jam_pulang'      => '16:00:00',
            'status_validasi' => 'disetujui',
        ]);
    }

    private function buatCatatan(User $siswa): CatatanKegiatan
    {
        return CatatanKegiatan::create([
            'user_id'              => $siswa->id,
            'nama_pekerjaan'       => 'Instalasi jaringan kantor',
            'perencanaan_kegiatan' => 'Menyiapkan alat',
            'pelaksanaan_kegiatan' => 'Memasang kabel',
            'status'               => 'disetujui',
            'is_approved'          => true,
        ]);
    }

    private function buatObservasi(User $siswa): Observasi
    {
        $observasi = Observasi::create([
            'user_id'      => $siswa->id,
            'guru_id'      => $siswa->guru_id,
            'hari_tanggal' => now()->toDateString(),
            'status'       => 'tervalidasi',
        ]);

        $observasi->items()->create([
            'permasalahan' => 'Kabel kurang panjang',
            'solusi'       => 'Menyambung dengan konektor',
        ]);

        return $observasi;
    }

    private function buatNilai(User $siswa): Nilai
    {
        return Nilai::create([
            'user_id'                 => $siswa->id,
            'guru_id'                 => $siswa->guru_id,
            'skor_soft_skill'         => 80,
            'deskripsi_soft_skill'    => 'Sopan',
            'skor_hard_skill'         => 85,
            'deskripsi_hard_skill'    => 'Terampil',
            'skor_pengembangan'       => 80,
            'deskripsi_pengembangan'  => 'Baik',
            'skor_kewirausahaan'      => 75,
            'deskripsi_kewirausahaan' => 'Cukup',
            'skor_laporan'            => 90,
            'deskripsi_laporan'       => 'Rapi',
            'skor_presentasi'         => 80,
            'deskripsi_presentasi'    => 'Percaya diri',
            'nilai_akhir'             => 81.67,
            'nilai_guru'              => 81.67,
            'nilai_laporan'           => 90,
        ]);
    }

    /* ============ CETAK SATUAN OLEH SISWA ============ */

    public function test_siswa_mencetak_jurnalnya_sendiri(): void
    {
        $this->buatJurnal($this->siswa);

        $this->actingAs($this->siswa)
            ->get(route('cetak.jurnal'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_siswa_tetap_mendapat_jurnalnya_sendiri_walau_menyebut_id_lain(): void
    {
        $this->buatJurnal($this->siswa);
        $this->buatJurnal($this->siswaLain);

        $respons = $this->actingAs($this->siswa)
            ->get(route('cetak.jurnal', $this->siswaLain->id))
            ->assertOk();

        $this->assertStringContainsString(
            'Dewi Anggraini',
            $respons->headers->get('content-disposition')
        );
    }

    public function test_siswa_mencetak_absensinya_sendiri(): void
    {
        $this->buatAbsensi($this->siswa);

        $this->actingAs($this->siswa)
            ->get(route('cetak.absensi'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_siswa_mencetak_catatan_kegiatannya(): void
    {
        $this->buatCatatan($this->siswa);

        $this->actingAs($this->siswa)
            ->get(route('cetak.catatan'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_siswa_mencetak_lembar_observasinya(): void
    {
        $this->buatObservasi($this->siswa);

        $this->actingAs($this->siswa)
            ->get(route('cetak.observasi'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_siswa_mencetak_daftar_nilainya(): void
    {
        $this->buatNilai($this->siswa);

        $this->actingAs($this->siswa)
            ->get(route('cetak.nilai'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_cetak_nilai_ditolak_halus_bila_nilai_belum_diisi(): void
    {
        $this->actingAs($this->siswa)
            ->from(route('siswa.nilai.index'))
            ->get(route('cetak.nilai'))
            ->assertRedirect(route('siswa.nilai.index'))
            ->assertSessionHas('error');
    }

    /* ============ CETAK SATUAN OLEH GURU ============ */

    public function test_guru_mencetak_jurnal_siswa_bimbingannya(): void
    {
        $this->buatJurnal($this->siswa);

        $this->actingAs($this->guru)
            ->get(route('cetak.jurnal', $this->siswa->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guru_ditolak_saat_mencetak_jurnal_siswa_guru_lain(): void
    {
        $this->buatJurnal($this->siswaLain);

        $this->actingAs($this->guru)
            ->get(route('cetak.jurnal', $this->siswaLain->id))
            ->assertForbidden();
    }

    public function test_guru_ditolak_saat_mencetak_absensi_siswa_guru_lain(): void
    {
        $this->buatAbsensi($this->siswaLain);

        $this->actingAs($this->guru)
            ->get(route('cetak.absensi', $this->siswaLain->id))
            ->assertForbidden();
    }

    public function test_guru_ditolak_saat_mencetak_observasi_siswa_guru_lain(): void
    {
        $this->actingAs($this->guru)
            ->get(route('cetak.observasi', $this->siswaLain->id))
            ->assertForbidden();
    }

    public function test_guru_mencetak_lembar_nilai_versi_guru(): void
    {
        $this->buatNilai($this->siswa);

        $this->actingAs($this->guru)
            ->get(route('cetak.nilai.guru', $this->siswa->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guru_mencetak_template_penilaian_kosong(): void
    {
        $this->actingAs($this->guru)
            ->get(route('cetak.nilai.template', $this->siswa->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    /* ============ CETAK SATUAN OLEH ADMIN ============ */

    public function test_admin_mencetak_jurnal_siswa_mana_pun(): void
    {
        $this->buatJurnal($this->siswaLain);

        $this->actingAs($this->admin)
            ->get(route('cetak.jurnal', $this->siswaLain->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_wajib_menyebut_siswa_saat_mencetak(): void
    {
        $this->actingAs($this->admin)
            ->get(route('cetak.jurnal'))
            ->assertNotFound();
    }

    public function test_admin_mencetak_jurnal_siswa_yang_sudah_diarsipkan(): void
    {
        $this->buatJurnal($this->siswa);
        $this->siswa->delete();

        $this->actingAs($this->admin)
            ->get(route('cetak.jurnal', $this->siswa->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_mencetak_siswa_yang_tidak_ada_menghasilkan_tidak_ditemukan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('cetak.jurnal', 999999))
            ->assertNotFound();
    }

    /* ============ PENYARINGAN PER BARIS ============ */

    public function test_mencetak_satu_baris_jurnal_tertentu(): void
    {
        $jurnal = $this->buatJurnal($this->siswa);

        $this->actingAs($this->siswa)
            ->get(route('cetak.jurnal', ['jurnal_id' => $jurnal->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_jurnal_yang_dipilih_tidak_ada_menghasilkan_tidak_ditemukan(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('cetak.jurnal', ['jurnal_id' => 999999]))
            ->assertNotFound();
    }

    public function test_mencetak_satu_baris_absensi_tertentu(): void
    {
        $absensi = $this->buatAbsensi($this->siswa);

        $this->actingAs($this->siswa)
            ->get(route('cetak.absensi', ['absensi_id' => $absensi->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_absensi_yang_dipilih_tidak_ada_menghasilkan_tidak_ditemukan(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('cetak.absensi', ['absensi_id' => 999999]))
            ->assertNotFound();
    }

    public function test_mencetak_absensi_satu_bulan_yang_kosong_menghasilkan_tidak_ditemukan(): void
    {
        $this->actingAs($this->siswa)
            ->get(route('cetak.absensi', ['bulan' => '2000-01']))
            ->assertNotFound();
    }

    /* ============ CETAK SEMUA ============ */

    public function test_admin_mencetak_seluruh_jurnal_sekaligus(): void
    {
        $this->buatJurnal($this->siswa);
        $this->buatJurnal($this->siswaLain);

        $this->actingAs($this->admin)
            ->get(route('cetak.jurnal.semua'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_mencetak_seluruh_absensi_sekaligus(): void
    {
        $this->buatAbsensi($this->siswa);

        $this->actingAs($this->admin)
            ->get(route('cetak.absensi.semua'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_mencetak_seluruh_catatan_yang_sudah_disetujui(): void
    {
        $this->buatCatatan($this->siswa);

        $this->actingAs($this->admin)
            ->get(route('cetak.catatan.semua'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_mencetak_seluruh_lembar_observasi(): void
    {
        $this->buatObservasi($this->siswa);

        $this->actingAs($this->admin)
            ->get(route('cetak.observasi.semua'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_mencetak_seluruh_daftar_nilai(): void
    {
        $this->buatNilai($this->siswa);

        $this->actingAs($this->admin)
            ->get(route('cetak.nilai.semua'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guru_mencetak_seluruh_jurnal_siswa_bimbingannya(): void
    {
        $this->buatJurnal($this->siswa);

        $this->actingAs($this->guru)
            ->get(route('cetak.jurnal.semua'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guru_tidak_mendapat_jurnal_siswa_guru_lain_pada_cetak_semua(): void
    {
        $this->buatJurnal($this->siswaLain);

        $this->actingAs($this->guru)
            ->get(route('cetak.jurnal.semua'))
            ->assertNotFound();
    }

    public function test_cetak_semua_pada_data_kosong_menghasilkan_tidak_ditemukan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('cetak.catatan.semua'))
            ->assertNotFound();
    }

    /**
     * @param string $namaRute
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('ruteCetakSemua')]
    public function test_siswa_tidak_boleh_memakai_tombol_cetak_semua(string $namaRute): void
    {
        $this->actingAs($this->siswa)
            ->get(route($namaRute))
            ->assertForbidden();
    }

    public static function ruteCetakSemua(): array
    {
        return [
            'seluruh jurnal'      => ['cetak.jurnal.semua'],
            'seluruh catatan'     => ['cetak.catatan.semua'],
            'seluruh observasi'   => ['cetak.observasi.semua'],
            'seluruh nilai'       => ['cetak.nilai.semua'],
            'seluruh nilai guru'  => ['cetak.nilai.guru.semua'],
            'seluruh absensi'     => ['cetak.absensi.semua'],
        ];
    }

    public function test_tamu_tidak_bisa_mencetak_apa_pun(): void
    {
        $this->get(route('cetak.jurnal'))->assertRedirect(route('login'));
    }
}
