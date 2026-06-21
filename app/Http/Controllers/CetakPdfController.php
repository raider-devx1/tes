<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\Pengaturan;
use App\Models\PeriodePkl;
use Carbon\Carbon;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakPdfController extends Controller
{
    /**
     * Tentukan siswa target + cek hak akses sesuai role.
     */
    private function resolveSiswa($siswaId = null): User
    {
        $user = auth()->user();

        // Siswa hanya boleh mencetak miliknya sendiri (param diabaikan)
        if ($user->role === 'siswa_pkl') {
            return $user;
        }

        abort_if(empty($siswaId), 404, 'Siswa tidak ditemukan.');
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($siswaId);

        if ($user->role === 'guru_pembimbing') {
            abort_unless($siswa->guru_id === $user->id, 403, 'Bukan siswa bimbingan Anda.');
        } elseif ($user->role === 'instruktur_industri') {
            abort_unless($siswa->instruktur_id === $user->id, 403, 'Bukan siswa bimbingan Anda.');
        }
        // admin: tanpa batasan

        return $siswa;
    }

    private function getPengaturan(): array
    {
        return Pengaturan::pluck('nilai', 'kunci')->toArray();
    }

    // ====== 1. JURNAL (FK: siswa_id) ======
    public function cetakJurnal($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);
        $jurnals = Jurnal::where('siswa_id', $siswa->id)->orderBy('hari_tanggal', 'asc')->get();
        $pengaturan = $this->getPengaturan();

        $pdf = Pdf::loadView('pdf.jurnal', compact('siswa', 'jurnals', 'pengaturan'))
                  ->setPaper('a4', 'portrait');
        return $pdf->stream('Jurnal_PKL_'.$siswa->name.'.pdf');
    }

    // ====== 2. CATATAN (FK: user_id) ======
    public function cetakCatatan($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);
        $catatan = CatatanKegiatan::where('user_id', $siswa->id)
            ->where('is_approved', true)
            ->get();

        $data = [
            'nama_siswa'      => $siswa->name,
            'kelas'           => $siswa->kelas ?? 'Belum Diatur',
            // FIX: kolom yang benar adalah nama_perusahaan
            'dunia_kerja'     => $siswa->perusahaan->nama_perusahaan ?? 'Belum Diatur',
            'nama_instruktur' => $siswa->instruktur->name ?? 'Belum Diatur',
            'nama_guru'       => $siswa->guru->name ?? 'Belum Diatur',
            'catatan'         => $catatan,
        ];

        $pdf = Pdf::loadView('pdf.catatan', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Catatan_Kegiatan_PKL_'.$siswa->name.'.pdf');
    }

    // ====== 3. OBSERVASI (FK: user_id) ======
    public function cetakObservasi($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);
        $observasi = Observasi::where('user_id', $siswa->id)->orderBy('hari_tanggal', 'asc')->get();

        $data = [
            'nama_siswa'       => $siswa->name,
            'kelas'            => $siswa->kelas ?? 'Belum Diatur',
            'dunia_kerja'      => $siswa->perusahaan->nama_perusahaan ?? 'Belum Diatur',
            'nama_instruktur'  => $siswa->instruktur->name ?? 'Belum Diatur',
            'nama_guru'        => $siswa->guru->name ?? 'Belum Diatur',
            'pekerjaan_projek' => $observasi->first()?->pekerjaan_projek ?? '-',
            'observasi'        => $observasi,
        ];

        $pdf = Pdf::loadView('pdf.observasi', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Lembar_Observasi_PKL_'.$siswa->name.'.pdf');
    }

    // ====== 4. NILAI (FK: user_id) ======
    public function cetakNilai($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);
        $nilai = Nilai::where('user_id', $siswa->id)->first();

        if (!$nilai) {
            return redirect()->back()->with('error', 'Cetak gagal, nilai siswa belum diinput oleh instruktur industri.');
        }

        // Rekap kehadiran otomatis dari tabel absensi
        $kehadiran = [
            'sakit' => Absensi::where('siswa_id', $siswa->id)->where('status', 'Sakit')->count(),
            'izin'  => Absensi::where('siswa_id', $siswa->id)->where('status', 'Izin')->count(),
            'alpha' => Absensi::where('siswa_id', $siswa->id)->where('status', 'Alpha')->count(),
        ];

        // Tanggal observasi terakhir (jika ada)
        $tanggalObservasi = optional(
            Observasi::where('user_id', $siswa->id)->orderBy('hari_tanggal', 'desc')->first()
        )->hari_tanggal;

        $pengaturan  = $this->getPengaturan();
        $tahunAjaran = optional(PeriodePkl::aktif())->tahun_ajaran ?? '2025/2026';

       $data = [
    'nama_sekolah'      => $pengaturan['nama_sekolah'] ?? 'UPTD SMKN 1 MAJENE',
    'tahun_ajaran'      => $tahunAjaran,
    'nama_siswa'        => $siswa->name,
    'kelas'             => $siswa->kelas ?? 'Belum Diatur',
    'program_keahlian'  => $siswa->jurusan ?? 'Belum Diatur',
    'dunia_kerja'       => $siswa->perusahaan->nama_perusahaan ?? 'Belum Diatur',
    'tanggal_observasi' => $tanggalObservasi,
    'nama_instruktur'   => $siswa->instruktur->name ?? 'Belum Diatur',
    'nama_guru'         => $siswa->guru->name ?? 'Belum Diatur',
    'nip_guru'          => $siswa->guru->nip ?? '-',                       // ← NIP guru pembimbing
    'tanggal_cetak'     => Carbon::now()->locale('id')->translatedFormat('d F Y'), // ← cth: 21 Juni 2026
    'nilai'             => $nilai,
    'kehadiran'         => $kehadiran,
];

        $pdf = Pdf::loadView('pdf.nilai', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Daftar_Nilai_PKL_'.$siswa->name.'.pdf');
    }
}