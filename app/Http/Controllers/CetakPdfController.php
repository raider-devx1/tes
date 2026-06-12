<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\Pengaturan;
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
            'dunia_kerja'     => $siswa->perusahaan->nama ?? 'Belum Diatur',
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
            'dunia_kerja'      => $siswa->perusahaan->nama ?? 'Belum Diatur',
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

        $data = [
            'nama_siswa'      => $siswa->name,
            'kelas'           => $siswa->kelas ?? 'Belum Diatur',
            'dunia_kerja'     => $siswa->perusahaan->nama ?? 'Belum Diatur',
            'nama_instruktur' => $siswa->instruktur->name ?? 'Belum Diatur',
            'nama_guru'       => $siswa->guru->name ?? 'Belum Diatur',
            'nilai'           => $nilai,
        ];

        $pdf = Pdf::loadView('pdf.nilai', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Lembar_Penilaian_PKL_'.$siswa->name.'.pdf');
    }
}