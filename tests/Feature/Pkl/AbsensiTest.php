<?php

namespace Tests\Feature\Pkl;

use App\Models\Absensi;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji aturan kehadiran.
 *
 * Absensi ganda pada tanggal yang sama pernah menjadi temuan audit: satu siswa
 * bisa punya dua baris untuk hari yang sama, sehingga rekap persentase
 * kehadiran menjadi tidak masuk akal (bisa melebihi 100%).
 */
class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        KonteksPeriode::lupakan();
    }

    public function test_satu_siswa_tidak_boleh_absen_dua_kali_di_tanggal_sama(): void
    {
        $siswa = User::factory()->siswa()->create();

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal'  => '2026-04-01',
            'status'   => 'Hadir',
        ]);

        $this->expectException(QueryException::class);

        Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal'  => '2026-04-01',
            'status'   => 'Izin',
        ]);
    }

    public function test_dua_siswa_berbeda_boleh_absen_di_tanggal_sama(): void
    {
        $a = User::factory()->siswa()->create();
        $b = User::factory()->siswa()->create();

        Absensi::create(['siswa_id' => $a->id, 'tanggal' => '2026-04-02', 'status' => 'Hadir']);
        Absensi::create(['siswa_id' => $b->id, 'tanggal' => '2026-04-02', 'status' => 'Hadir']);

        $this->assertSame(2, Absensi::whereDate('tanggal', '2026-04-02')->count());
    }

    public function test_siswa_yang_sama_boleh_absen_di_tanggal_berbeda(): void
    {
        $siswa = User::factory()->siswa()->create();

        Absensi::create(['siswa_id' => $siswa->id, 'tanggal' => '2026-04-03', 'status' => 'Hadir']);
        Absensi::create(['siswa_id' => $siswa->id, 'tanggal' => '2026-04-04', 'status' => 'Hadir']);

        $this->assertSame(2, Absensi::where('siswa_id', $siswa->id)->count());
    }

    public function test_absensi_ikut_terikat_ke_periode_berjalan(): void
    {
        $periode = PeriodePkl::factory()->aktif()->create();
        $siswa   = User::factory()->siswa()->create(['periode_id' => $periode->id]);

        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal'  => '2026-04-05',
            'status'   => 'Hadir',
        ]);

        $this->assertSame($periode->id, $absensi->fresh()->periode_id);
        $this->assertSame(1, Absensi::periodeBerjalan()->count());
    }

    public function test_absensi_tetap_utuh_setelah_siswa_diarsipkan(): void
    {
        $siswa = User::factory()->siswa()->create();

        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'tanggal'  => '2026-04-06',
            'status'   => 'Hadir',
        ]);

        $siswa->delete();

        $this->assertDatabaseHas('absensis', ['id' => $absensi->id]);
        $this->assertNotNull(Absensi::find($absensi->id)->siswa);
    }
}
