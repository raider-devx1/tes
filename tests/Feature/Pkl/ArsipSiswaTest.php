<?php

namespace Tests\Feature\Pkl;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji pengarsipan siswa (soft delete).
 *
 * Ini pengaman terpenting di seluruh aplikasi. Tabel jurnals, absensis,
 * nilais, dokumens, observasis, dan catatan_kegiatans semuanya memakai
 * onDelete('cascade') ke tabel users. Tanpa soft delete, SATU klik hapus
 * siswa akan memusnahkan seluruh riwayat PKL-nya secara permanen.
 */
class ArsipSiswaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();
    }

    public function test_mengarsipkan_siswa_tidak_menghapus_riwayat_pkl(): void
    {
        $siswa = User::factory()->siswa()->create();

        $jurnal = Jurnal::create([
            'siswa_id'     => $siswa->id,
            'hari_tanggal' => '2026-02-10',
            'status'       => 'disetujui',
        ]);

        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal'  => '2026-02-10',
            'status'   => 'Hadir',
        ]);

        $siswa->delete();

        // Siswa hanya ditandai, bukan dihapus sungguhan.
        $this->assertSoftDeleted('users', ['id' => $siswa->id]);

        // Cascade TIDAK berjalan, riwayat tetap utuh.
        $this->assertDatabaseHas('jurnals',  ['id' => $jurnal->id]);
        $this->assertDatabaseHas('absensis', ['id' => $absensi->id]);
    }

    /**
     * Relasi memakai withTrashed() supaya laporan angkatan lama tetap bisa
     * dicetak. Kalau ini hilang, PDF akan gagal dengan error "Attempt to read
     * property on null" begitu siswanya diarsipkan.
     */
    public function test_relasi_masih_bisa_membaca_siswa_yang_sudah_diarsipkan(): void
    {
        $siswa = User::factory()->siswa()->create(['name' => 'Budi Santoso']);

        $jurnal = Jurnal::create([
            'siswa_id'     => $siswa->id,
            'hari_tanggal' => '2026-02-11',
            'status'       => 'disetujui',
        ]);

        $siswa->delete();

        $jurnalSegar = Jurnal::find($jurnal->id);

        $this->assertNotNull(
            $jurnalSegar->siswa,
            'Relasi kehilangan withTrashed(); laporan siswa terarsip akan error.'
        );
        $this->assertSame('Budi Santoso', $jurnalSegar->siswa->name);
    }

    /**
     * Pintu masuk cetak PDF per siswa memakai withTrashed()->findOrFail().
     * Tanpa itu, mencetak berkas siswa yang sudah diarsipkan menghasilkan 404 --
     * padahal mencetak berkas angkatan lama justru alasan utama arsip dibuat.
     */
    public function test_siswa_terarsip_masih_bisa_ditemukan_untuk_cetak_pdf(): void
    {
        $siswa = User::factory()->siswa()->create();
        $siswa->delete();

        $ditemukan = User::siswa()->withTrashed()->find($siswa->id);

        $this->assertNotNull($ditemukan, 'Cetak PDF siswa terarsip akan menghasilkan 404.');
        $this->assertTrue($ditemukan->trashed());

        // Sebaliknya, query biasa memang harus mengecualikannya.
        $this->assertNull(User::siswa()->find($siswa->id));
    }

    public function test_siswa_dapat_dipulihkan_dari_arsip(): void
    {
        $siswa = User::factory()->siswa()->create();
        $siswa->delete();

        $this->assertSoftDeleted('users', ['id' => $siswa->id]);

        User::withTrashed()->find($siswa->id)->restore();

        $this->assertNotSoftDeleted('users', ['id' => $siswa->id]);
        $this->assertNotNull(User::siswa()->find($siswa->id));
    }

    /**
     * NISN bersifat unique. Karena siswa terarsip masih menempati barisnya,
     * NISN yang sama tidak boleh dipakai ulang -- kalau dipaksa, pemulihan
     * arsip akan gagal di kemudian hari.
     */
    public function test_nisn_siswa_terarsip_masih_tercatat_di_database(): void
    {
        $siswa = User::factory()->siswa()->create(['nisn' => '1234567890']);
        $siswa->delete();

        $this->assertDatabaseHas('users', ['nisn' => '1234567890']);
    }
}
