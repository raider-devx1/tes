<?php

namespace Tests\Feature\Pkl;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji trait MilikPeriodePkl: pengisian periode_id secara OTOMATIS.
 *
 * Tanpa ini, data transaksi tidak terikat ke angkatan mana pun. Akibatnya
 * rekap tahun ketiga akan mencampur jurnal tiga angkatan sekaligus, dan tidak
 * ada cara memisahkannya lagi setelah data terlanjur menumpuk.
 */
class PeriodeOtomatisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();
    }

    public function test_periode_diambil_dari_siswa_pemilik_data(): void
    {
        $periodeSiswa = PeriodePkl::factory()->create();
        PeriodePkl::factory()->aktif()->create(); // periode aktif BERBEDA

        $siswa = User::factory()->siswa()->create(['periode_id' => $periodeSiswa->id]);

        $jurnal = Jurnal::create([
            'siswa_id'     => $siswa->id,
            'hari_tanggal' => '2026-03-01',
            'status'       => 'draft',
        ]);

        // Periode SISWA lebih diutamakan daripada periode aktif. Ini penting
        // saat admin menginput data susulan setelah periode berganti.
        $this->assertSame($periodeSiswa->id, $jurnal->fresh()->periode_id);
    }

    public function test_periode_jatuh_ke_periode_aktif_bila_siswa_belum_punya(): void
    {
        $aktif = PeriodePkl::factory()->aktif()->create();
        $siswa = User::factory()->siswa()->create(['periode_id' => null]);

        $jurnal = Jurnal::create([
            'siswa_id'     => $siswa->id,
            'hari_tanggal' => '2026-03-02',
            'status'       => 'draft',
        ]);

        $this->assertSame($aktif->id, $jurnal->fresh()->periode_id);
    }

    public function test_periode_yang_diisi_manual_tidak_ditimpa(): void
    {
        $aktif  = PeriodePkl::factory()->aktif()->create();
        $manual = PeriodePkl::factory()->create();
        $siswa  = User::factory()->siswa()->create(['periode_id' => $aktif->id]);

        $jurnal = Jurnal::create([
            'siswa_id'     => $siswa->id,
            'periode_id'   => $manual->id, // diisi tangan, mis. saat impor data lama
            'hari_tanggal' => '2026-03-03',
            'status'       => 'draft',
        ]);

        $this->assertSame($manual->id, $jurnal->fresh()->periode_id);
    }

    public function test_penyimpanan_tidak_gagal_walau_belum_ada_periode_sama_sekali(): void
    {
        $siswa = User::factory()->siswa()->create(['periode_id' => null]);

        $jurnal = Jurnal::create([
            'siswa_id'     => $siswa->id,
            'hari_tanggal' => '2026-03-04',
            'status'       => 'draft',
        ]);

        // Dibiarkan kosong, TIDAK menggagalkan penyimpanan.
        // Data tetap tersimpan dan periodenya bisa diisi belakangan.
        $this->assertNull($jurnal->fresh()->periode_id);
        $this->assertDatabaseHas('jurnals', ['id' => $jurnal->id]);
    }

    /**
     * Trait dipakai enam model dengan nama kolom pemilik yang BERBEDA:
     * 'siswa_id' untuk jurnal/absensi/dokumen, 'user_id' untuk
     * nilai/catatan/observasi. Tes ini menjaga pemetaan itu tetap benar.
     */
    public function test_berlaku_untuk_model_dengan_kolom_pemilik_berbeda(): void
    {
        $periode = PeriodePkl::factory()->aktif()->create();
        $siswa   = User::factory()->siswa()->create(['periode_id' => $periode->id]);

        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal'  => '2026-03-05',
            'status'   => 'Hadir',
        ]);

        // Kolom nama_pekerjaan wajib diisi (NOT NULL di migrasi).
        $catatan = CatatanKegiatan::create([
            'user_id'               => $siswa->id,
            'nama_pekerjaan'        => 'Merakit jaringan LAN',
            'perencanaan_kegiatan'  => 'Menyiapkan kabel dan switch',
            'pelaksanaan_kegiatan'  => 'Memasang dan menguji koneksi',
            'status'                => 'draft',
        ]);

        $this->assertSame($periode->id, $absensi->fresh()->periode_id, 'Kolom siswa_id gagal dipetakan.');
        $this->assertSame($periode->id, $catatan->fresh()->periode_id, 'Kolom user_id gagal dipetakan.');
    }

    /**
     * CATATAN PENTING soal penulisan query.
     *
     * Model ini punya DUA anggota bernama mirip:
     *   - relasi   periode()      -> menunjuk ke satu PeriodePkl
     *   - scope    scopePeriode() -> menyaring query per angkatan
     *
     * Karena relasinya adalah method sungguhan, PHP akan memilih relasi itu
     * bila dipanggil langsung dari nama kelas, sehingga Jurnal::periode(1)
     * menghasilkan error "cannot be called statically".
     *
     * Cara aman: awali dengan query(), atau rangkai setelah scope lain.
     * Seluruh controller di aplikasi ini sudah memakai pola yang benar.
     */
    public function test_scope_periode_menyaring_data_per_angkatan(): void
    {
        $lama = PeriodePkl::factory()->create();
        $baru = PeriodePkl::factory()->aktif()->create();

        $siswaLama = User::factory()->siswa()->create(['periode_id' => $lama->id]);
        $siswaBaru = User::factory()->siswa()->create(['periode_id' => $baru->id]);

        Jurnal::create(['siswa_id' => $siswaLama->id, 'hari_tanggal' => '2026-01-01', 'status' => 'draft']);
        Jurnal::create(['siswa_id' => $siswaBaru->id, 'hari_tanggal' => '2026-01-02', 'status' => 'draft']);

        $this->assertSame(2, Jurnal::count());
        $this->assertSame(1, Jurnal::query()->periode($baru->id)->count());
        $this->assertSame(1, Jurnal::query()->periode($lama->id)->count());
        $this->assertSame(1, Jurnal::periodeBerjalan()->count());
    }

    /**
     * Mengunci jebakan penamaan di atas agar tidak terlupakan.
     * Bila suatu saat relasi periode() dihapus atau diganti nama, tes ini
     * akan gagal dan mengingatkan bahwa pola pemanggilan boleh disederhanakan.
     */
    public function test_scope_periode_tidak_boleh_dipanggil_langsung_dari_nama_kelas(): void
    {
        $this->expectException(\Error::class);

        Jurnal::periode(1);
    }
}
