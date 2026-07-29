<?php

/** TARUH DI: tests/Feature/Pkl/AlurMonitoringAdminTest.php */

namespace Tests\Feature\Pkl;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Pengaturan;
use App\Models\PeriodePkl;
use App\Models\User;
use App\Support\KonteksPeriode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji menu Admin > Monitoring (Jurnal, Catatan Kegiatan, Absensi).
 */
class AlurMonitoringAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $siswa;
    private PeriodePkl $periode;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        KonteksPeriode::lupakan();

        $this->periode = PeriodePkl::factory()->aktif()->create();
        $this->admin   = User::factory()->admin()->create();

        $this->siswa = User::factory()->siswa()->create([
            'name'       => 'Dewi Anggraini',
            'nisn'       => '1234500001',
            'periode_id' => $this->periode->id,
            'status_pkl' => 'aktif',
        ]);
    }

    /*
    |----------------------------------------------------------------
    | JURNAL
    |----------------------------------------------------------------
    */

    public function test_admin_melihat_halaman_monitoring_jurnal(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.monitoring.jurnal'))
            ->assertOk()
            ->assertSee('Dewi Anggraini');
    }

    public function test_admin_menambah_jurnal_beserta_poin_pekerjaan(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.jurnal'))
            ->post(route('admin.monitoring.jurnal.store'), [
                'siswa_id'     => $this->siswa->id,
                'hari_tanggal' => now()->toDateString(),
                'status'       => 'draft',
                'items'        => [
                    ['unit_kerja' => 'Merakit perangkat jaringan'],
                    ['unit_kerja' => 'Membuat laporan harian'],
                ],
            ])
            ->assertSessionHas('success');

        $jurnal = Jurnal::firstOrFail();

        $this->assertSame($this->siswa->id, (int) $jurnal->siswa_id);
        $this->assertCount(2, $jurnal->items);
    }

    public function test_jurnal_baru_otomatis_terikat_periode_aktif(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.monitoring.jurnal.store'), [
                'siswa_id'     => $this->siswa->id,
                'hari_tanggal' => now()->toDateString(),
                'status'       => 'draft',
                'items'        => [['unit_kerja' => 'Pekerjaan uji']],
            ]);

        $this->assertSame($this->periode->id, (int) Jurnal::firstOrFail()->periode_id);
    }

    public function test_jurnal_wajib_punya_minimal_satu_poin_pekerjaan(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.jurnal'))
            ->post(route('admin.monitoring.jurnal.store'), [
                'siswa_id'     => $this->siswa->id,
                'hari_tanggal' => now()->toDateString(),
                'status'       => 'draft',
                'items'        => [],
            ])
            ->assertSessionHasErrors('items');
    }

    public function test_status_jurnal_harus_pilihan_yang_dikenal(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.jurnal'))
            ->post(route('admin.monitoring.jurnal.store'), [
                'siswa_id'     => $this->siswa->id,
                'hari_tanggal' => now()->toDateString(),
                'status'       => 'entah',
                'items'        => [['unit_kerja' => 'Pekerjaan uji']],
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_menyetujui_jurnal_mencatat_penanda_tangan(): void
    {
        $jurnal = Jurnal::create([
            'siswa_id'     => $this->siswa->id,
            'hari_tanggal' => now()->toDateString(),
            'status'       => 'diajukan',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.jurnal'))
            ->put(route('admin.monitoring.jurnal.update', $jurnal), [
                'siswa_id'     => $this->siswa->id,
                'hari_tanggal' => now()->toDateString(),
                'status'       => 'disetujui',
                'items'        => [['unit_kerja' => 'Pekerjaan uji']],
            ])
            ->assertSessionHas('success');

        $jurnal->refresh();

        $this->assertSame('disetujui', $jurnal->status);
        $this->assertSame($this->admin->id, (int) $jurnal->validated_by_guru_id);
        $this->assertNotNull($jurnal->validated_at);
    }

    public function test_mengembalikan_jurnal_ke_draft_menghapus_tanda_validasi(): void
    {
        $jurnal = Jurnal::create([
            'siswa_id'             => $this->siswa->id,
            'hari_tanggal'         => now()->toDateString(),
            'status'               => 'disetujui',
            'validated_by_guru_id' => $this->admin->id,
            'validated_at'         => now(),
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.jurnal'))
            ->put(route('admin.monitoring.jurnal.update', $jurnal), [
                'siswa_id'     => $this->siswa->id,
                'hari_tanggal' => now()->toDateString(),
                'status'       => 'draft',
                'items'        => [['unit_kerja' => 'Pekerjaan uji']],
            ]);

        $jurnal->refresh();

        $this->assertSame('draft', $jurnal->status);
        $this->assertNull($jurnal->validated_by_guru_id);
        $this->assertNull($jurnal->validated_at);
    }

    public function test_menghapus_jurnal_ikut_menghapus_poin_pekerjaannya(): void
    {
        $jurnal = Jurnal::create([
            'siswa_id'     => $this->siswa->id,
            'hari_tanggal' => now()->toDateString(),
            'status'       => 'draft',
        ]);
        $jurnal->items()->create(['unit_kerja' => 'Pekerjaan uji']);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.jurnal'))
            ->delete(route('admin.monitoring.jurnal.destroy', $jurnal))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('jurnals', ['id' => $jurnal->id]);
        $this->assertDatabaseCount('jurnal_items', 0);
    }

    public function test_monitoring_jurnal_dapat_disaring_menurut_status(): void
    {
        Jurnal::create([
            'siswa_id'     => $this->siswa->id,
            'hari_tanggal' => now()->toDateString(),
            'status'       => 'disetujui',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.monitoring.jurnal', ['status' => 'draft']))
            ->assertOk();
    }

    /*
    |----------------------------------------------------------------
    | CATATAN KEGIATAN
    |----------------------------------------------------------------
    */

    public function test_admin_melihat_halaman_monitoring_catatan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.monitoring.catatan'))
            ->assertOk();
    }

    public function test_admin_menambah_catatan_kegiatan(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.catatan'))
            ->post(route('admin.monitoring.catatan.store'), [
                'user_id'              => $this->siswa->id,
                'nama_pekerjaan'       => 'Instalasi jaringan kantor',
                'perencanaan_kegiatan' => 'Menyiapkan alat dan bahan',
                'pelaksanaan_kegiatan' => 'Memasang kabel dan perangkat',
                'status'               => 'draft',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('catatan_kegiatans', [
            'user_id'        => $this->siswa->id,
            'nama_pekerjaan' => 'Instalasi jaringan kantor',
        ]);
    }

    public function test_nama_pekerjaan_pada_catatan_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.catatan'))
            ->post(route('admin.monitoring.catatan.store'), [
                'user_id'        => $this->siswa->id,
                'nama_pekerjaan' => '',
                'status'         => 'draft',
            ])
            ->assertSessionHasErrors('nama_pekerjaan');
    }

    public function test_admin_memperbarui_catatan_kegiatan(): void
    {
        $catatan = CatatanKegiatan::create([
            'user_id'              => $this->siswa->id,
            'nama_pekerjaan'       => 'Judul Lama',
            // Tiga kolom ini NOT NULL di migrasi catatan_kegiatans.
            'perencanaan_kegiatan' => 'Rencana lama',
            'pelaksanaan_kegiatan' => 'Pelaksanaan lama',
            'status'               => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.catatan'))
            ->put(route('admin.monitoring.catatan.update', $catatan), [
                'user_id'        => $this->siswa->id,
                'nama_pekerjaan' => 'Judul Baru',
                'status'         => 'disetujui',
            ])
            ->assertSessionHas('success');

        $catatan->refresh();

        $this->assertSame('Judul Baru', $catatan->nama_pekerjaan);
        $this->assertSame('disetujui', $catatan->status);
        $this->assertSame($this->admin->id, (int) $catatan->validated_by_guru_id);
    }

    public function test_admin_menghapus_catatan_kegiatan(): void
    {
        $catatan = CatatanKegiatan::create([
            'user_id'              => $this->siswa->id,
            'nama_pekerjaan'       => 'Akan dihapus',
            'perencanaan_kegiatan' => 'Rencana lama',
            'pelaksanaan_kegiatan' => 'Pelaksanaan lama',
            'status'               => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.catatan'))
            ->delete(route('admin.monitoring.catatan.destroy', $catatan))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('catatan_kegiatans', ['id' => $catatan->id]);
    }

    /*
    |----------------------------------------------------------------
    | ABSENSI
    |----------------------------------------------------------------
    */

    public function test_admin_melihat_halaman_monitoring_absensi(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.monitoring.absensi'))
            ->assertOk();
    }

    public function test_admin_menyimpan_absensi_siswa(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.store'), [
                'siswa_id'        => $this->siswa->id,
                'tanggal'         => now()->toDateString(),
                'status'          => 'Hadir',
                'jam_masuk'       => '08:00',
                'jam_pulang'      => '16:00',
                'status_validasi' => 'disetujui',
            ])
            ->assertSessionHas('success');

        $absensi = Absensi::firstOrFail();

        $this->assertSame('Hadir', $absensi->status);
        $this->assertSame($this->admin->id, (int) $absensi->validated_by_guru_id);
    }

    public function test_absensi_tanggal_sama_diperbarui_bukan_digandakan(): void
    {
        $tanggal = now()->toDateString();

        foreach (['Hadir', 'Sakit'] as $status) {
            $this->actingAs($this->admin)
                ->from(route('admin.monitoring.absensi'))
                ->post(route('admin.monitoring.absensi.store'), [
                    'siswa_id'        => $this->siswa->id,
                    'tanggal'         => $tanggal,
                    'status'          => $status,
                    'status_validasi' => 'draft',
                ]);
        }

        $this->assertSame(1, Absensi::where('siswa_id', $this->siswa->id)->count());
        $this->assertSame('Sakit', Absensi::firstOrFail()->status);
    }

    public function test_status_absensi_harus_pilihan_yang_dikenal(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.store'), [
                'siswa_id'        => $this->siswa->id,
                'tanggal'         => now()->toDateString(),
                'status'          => 'Bolos',
                'status_validasi' => 'draft',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_admin_memperbarui_absensi(): void
    {
        $absensi = Absensi::create([
            'siswa_id'        => $this->siswa->id,
            'tanggal'         => now()->toDateString(),
            'status'          => 'Hadir',
            'status_validasi' => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->put(route('admin.monitoring.absensi.update', $absensi), [
                'siswa_id'        => $this->siswa->id,
                'tanggal'         => now()->toDateString(),
                'status'          => 'Izin',
                'status_validasi' => 'disetujui',
            ])
            ->assertSessionHas('success');

        $this->assertSame('Izin', $absensi->fresh()->status);
    }

    public function test_admin_menghapus_absensi_beserta_foto_buktinya(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.store'), [
                'siswa_id'        => $this->siswa->id,
                'tanggal'         => now()->toDateString(),
                'status'          => 'Hadir',
                'status_validasi' => 'draft',
                'foto_bukti'      => UploadedFile::fake()->image('bukti.jpg', 800, 600),
            ]);

        $absensi = Absensi::firstOrFail();
        $foto    = $absensi->foto_bukti;

        $this->assertNotNull($foto);
        Storage::disk('public')->assertExists($foto);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->delete(route('admin.monitoring.absensi.destroy', $absensi))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('absensis', ['id' => $absensi->id]);
        Storage::disk('public')->assertMissing($foto);
    }

    public function test_admin_menyimpan_pengaturan_jam_absensi(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.pengaturan'), [
                'absensi_jam_masuk'    => '07:30',
                'absensi_jam_pulang'   => '15:30',
                'absensi_durasi_menit' => 45,
            ])
            ->assertSessionHas('success');

        $this->assertSame('07:30', Pengaturan::ambil('absensi_jam_masuk'));
        $this->assertSame('15:30', Pengaturan::ambil('absensi_jam_pulang'));
        $this->assertSame('45', (string) Pengaturan::ambil('absensi_durasi_menit'));
    }

    public function test_format_jam_absensi_yang_salah_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.pengaturan'), [
                'absensi_jam_masuk'    => '7 pagi',
                'absensi_jam_pulang'   => '15:30',
                'absensi_durasi_menit' => 30,
            ])
            ->assertSessionHasErrors('absensi_jam_masuk');
    }

    public function test_durasi_absensi_di_luar_batas_wajar_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.pengaturan'), [
                'absensi_jam_masuk'    => '07:30',
                'absensi_jam_pulang'   => '15:30',
                'absensi_durasi_menit' => 5000,
            ])
            ->assertSessionHasErrors('absensi_durasi_menit');
    }

    public function test_admin_membuka_absensi_masuk_untuk_semua_siswa(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.buka'), [
                'mode'   => 'semua',
                'aksi'   => 'buka',
                'target' => 'masuk',
            ])
            ->assertSessionHas('success');

        $this->assertSame('1', (string) Pengaturan::ambil('absensi_paksa_buka_masuk'));
    }

    public function test_menutup_absensi_semua_mengembalikan_pembukaan_per_siswa(): void
    {
        $this->siswa->update([
            'absensi_dibuka'        => true,
            'absensi_dibuka_masuk'  => true,
            'absensi_dibuka_pulang' => true,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.buka'), [
                'mode'   => 'semua',
                'aksi'   => 'tutup',
                'target' => 'semua',
            ])
            ->assertSessionHas('success');

        $this->siswa->refresh();

        $this->assertFalse((bool) $this->siswa->absensi_dibuka);
        $this->assertFalse((bool) $this->siswa->absensi_dibuka_masuk);
        $this->assertFalse((bool) $this->siswa->absensi_dibuka_pulang);
    }

    public function test_membuka_absensi_per_nisn_tanpa_nisn_memberi_pesan_jelas(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.buka'), [
                'mode' => 'nisn',
                'aksi' => 'buka',
                'nisn' => '',
            ])
            ->assertSessionHas('error');
    }

    public function test_membuka_absensi_dengan_nisn_tidak_dikenal_memberi_pesan_jelas(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.buka'), [
                'mode' => 'nisn',
                'aksi' => 'buka',
                'nisn' => '0000000000',
            ])
            ->assertSessionHas('error');
    }

    public function test_admin_membuka_absensi_untuk_satu_siswa_lewat_nisn(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->post(route('admin.monitoring.absensi.buka'), [
                'mode'   => 'nisn',
                'aksi'   => 'buka',
                'target' => 'masuk',
                'nisn'   => '1234500001',
            ])
            ->assertSessionHas('success');

        $this->assertTrue((bool) $this->siswa->fresh()->absensi_dibuka_masuk);
    }

    public function test_admin_mengubah_jam_kerja_industri_siswa(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->put(route('admin.monitoring.absensi.jam.update', $this->siswa->id), [
                'jam_masuk_industri'  => '07:15',
                'jam_pulang_industri' => '17:00',
            ])
            ->assertSessionHas('success');

        $this->siswa->refresh();

        $this->assertSame('07:15:00', $this->siswa->jam_masuk_industri);
        $this->assertSame('17:00:00', $this->siswa->jam_pulang_industri);
        $this->assertSame('disetujui', $this->siswa->status_jam_usulan);
    }

    public function test_format_jam_kerja_industri_yang_salah_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->put(route('admin.monitoring.absensi.jam.update', $this->siswa->id), [
                'jam_masuk_industri'  => 'pagi',
                'jam_pulang_industri' => '17:00',
            ])
            ->assertSessionHasErrors('jam_masuk_industri');
    }

    public function test_admin_menyetujui_usulan_jam_kerja_siswa(): void
    {
        $this->siswa->update([
            'jam_masuk_usulan'  => '09:00',
            'jam_pulang_usulan' => '15:00',
            'status_jam_usulan' => 'diajukan',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->put(route('admin.monitoring.absensi.jam.validasi', $this->siswa->id), [
                'aksi' => 'setuju',
            ])
            ->assertSessionHas('success');

        $this->siswa->refresh();

        $this->assertSame('09:00:00', $this->siswa->jam_masuk_industri);
        $this->assertSame('disetujui', $this->siswa->status_jam_usulan);
        $this->assertNull($this->siswa->jam_masuk_usulan);
    }

    public function test_admin_menolak_usulan_jam_kerja_siswa(): void
    {
        $this->siswa->update([
            'jam_masuk_usulan'  => '09:00',
            'jam_pulang_usulan' => '15:00',
            'status_jam_usulan' => 'diajukan',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.monitoring.absensi'))
            ->put(route('admin.monitoring.absensi.jam.validasi', $this->siswa->id), [
                'aksi' => 'tolak',
            ])
            ->assertSessionHas('success');

        $this->siswa->refresh();

        $this->assertSame('none', $this->siswa->status_jam_usulan);
        $this->assertNull($this->siswa->jam_masuk_usulan);
    }

    /*
    |----------------------------------------------------------------
    | HAK AKSES
    |----------------------------------------------------------------
    */

    public function test_guru_tidak_boleh_membuka_monitoring_admin(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)
            ->get(route('admin.monitoring.absensi'))
            ->assertForbidden();
    }

    public function test_siswa_tidak_boleh_menyimpan_absensi_lewat_menu_admin(): void
    {
        $this->actingAs($this->siswa)
            ->post(route('admin.monitoring.absensi.store'), [
                'siswa_id'        => $this->siswa->id,
                'tanggal'         => now()->toDateString(),
                'status'          => 'Hadir',
                'status_validasi' => 'disetujui',
            ])
            ->assertForbidden();
    }
}
