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

    $query = CatatanKegiatan::with(['user.perusahaan', 'user.instruktur', 'user.guru'])
        ->where('user_id', $siswa->id);

    // Jika ada catatan_id → cetak SATU data (baris yang dipilih) saja
    if (request()->filled('catatan_id')) {
        $query->where('id', request('catatan_id'));
    } else {
        // Tanpa catatan_id → cetak semua yang sudah disetujui (milik siswa ini)
        $query->where('is_approved', true);
    }

    $catatan = $query->orderBy('created_at', 'asc')->get();

    $data = [
        'tanggal_cetak' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
        'catatan'       => $catatan,
    ];

    $pdf = Pdf::loadView('pdf.catatan', $data)->setPaper('a4', 'portrait');
    return $pdf->stream('Catatan_Kegiatan_PKL_'.$siswa->name.'.pdf');
}

// ====== 2b. CATATAN - CETAK SEMUA (semua siswa bimbingan, 1 catatan 1 halaman) ======
public function cetakCatatanSemua()
{
    $user = auth()->user();

    if (!in_array($user->role, ['instruktur_industri', 'guru_pembimbing', 'admin'])) {
        abort(403, 'Akses ditolak.');
    }

    $catatan = CatatanKegiatan::with(['user.perusahaan', 'user.instruktur', 'user.guru'])
        ->where('is_approved', true)
        ->whereHas('user', function ($u) use ($user) {
            $u->where('role', 'siswa_pkl')->where('status_pkl', 'aktif');

            if ($user->role === 'instruktur_industri') {
                $u->where('instruktur_id', $user->id);
            } elseif ($user->role === 'guru_pembimbing') {
                $u->where('guru_id', $user->id);
            }
        })
        ->orderBy('user_id')
        ->orderBy('created_at', 'asc')
        ->get();

    abort_if($catatan->isEmpty(), 404, 'Belum ada catatan yang disetujui untuk dicetak.');

    $data = [
        'tanggal_cetak' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
        'catatan'       => $catatan,
    ];

    $pdf = Pdf::loadView('pdf.catatan', $data)->setPaper('a4', 'portrait');
    return $pdf->stream('Catatan_Kegiatan_PKL_Semua.pdf');
}

   
    // ====== 3. OBSERVASI (FK: user_id) ======
// ====== 3. OBSERVASI (FK: user_id) ======
public function cetakObservasi($siswa_id = null)
{
    $siswa = $this->resolveSiswa($siswa_id);

    $query = Observasi::where('user_id', $siswa->id)->with('items');

    // Jika ada observasi_id → cetak SATU observasi (beserta semua poinnya)
    if (request()->filled('observasi_id')) {
        $query->where('id', request('observasi_id'));
    }

    $observasi = $query->orderBy('hari_tanggal', 'asc')->get();

    // Gabungkan semua poin (permasalahan & solusi) jadi satu daftar berurutan
    $rows = collect();
    foreach ($observasi as $obs) {
        foreach ($obs->items as $poin) {
            $rows->push((object) [
                'permasalahan' => $poin->permasalahan,
                'solusi'       => $poin->solusi,
                'is_approved'  => $obs->is_approved,
            ]);
        }
    }

    $data = [
        'nama_siswa'       => $siswa->name,
        'kelas'            => $siswa->kelas ?? 'Belum Diatur',
        'dunia_kerja'      => $siswa->perusahaan->nama_perusahaan ?? 'Belum Diatur',
        'nama_instruktur'  => $siswa->instruktur->name ?? 'Belum Diatur',
        'nama_guru'        => $siswa->guru->name ?? 'Belum Diatur',
        'pekerjaan_projek' => $observasi->first()?->pekerjaan_projek ?? '-',
        'rows'             => $rows,
    ];

    $pdf = Pdf::loadView('pdf.observasi', $data)->setPaper('a4', 'portrait');
    return $pdf->stream('Lembar_Observasi_PKL_'.$siswa->name.'.pdf');
}

   // ====== 4. NILAI (FK: user_id) ======
/** Bangun paket data untuk 1 lembar nilai siswa (dipakai cetak satuan & cetak semua). */
private function buildNilaiData(User $siswa): ?array
{
    $nilai = Nilai::where('user_id', $siswa->id)->first();

    if (!$nilai) {
        return null;
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

    return [
        'nama_sekolah'      => $pengaturan['nama_sekolah'] ?? 'UPTD SMKN 1 MAJENE',
        'tahun_ajaran'      => $tahunAjaran,
        'nama_siswa'        => $siswa->name,
        'kelas'             => $siswa->kelas ?? 'Belum Diatur',
        'program_keahlian'  => $siswa->jurusan ?? 'Belum Diatur',
        'dunia_kerja'       => $siswa->perusahaan->nama_perusahaan ?? 'Belum Diatur',
        'tanggal_observasi' => $tanggalObservasi,
        'nama_instruktur'   => $siswa->instruktur->name ?? 'Belum Diatur',
        'nama_guru'         => $siswa->guru->name ?? 'Belum Diatur',
        'nip_guru'          => $siswa->guru->nip ?? '-',
        'tanggal_cetak'     => Carbon::now()->locale('id')->translatedFormat('d F Y'),
        'nilai'             => $nilai,
        'kehadiran'         => $kehadiran,
    ];
}

public function cetakNilai($siswa_id = null)
{
    $siswa = $this->resolveSiswa($siswa_id);
    $data  = $this->buildNilaiData($siswa);

    if (!$data) {
        return redirect()->back()->with('error', 'Cetak gagal, nilai siswa belum diinput oleh instruktur industri.');
    }

    // Cetak satuan = daftar berisi 1 siswa
    $pdf = Pdf::loadView('pdf.nilai', ['lembar' => [$data]])->setPaper('a4', 'portrait');
    return $pdf->stream('Daftar_Nilai_PKL_'.$siswa->name.'.pdf');
}

// ====== 4b. NILAI - CETAK SEMUA (template per-siswa, 1 siswa 1 halaman) ======
public function cetakNilaiSemua()
{
    $user = auth()->user();

   $query = User::where('role', 'siswa_pkl');

if ($user->role === 'instruktur_industri') {
    $query->where('instruktur_id', $user->id)->where('status_pkl', 'aktif');
} elseif ($user->role === 'guru_pembimbing') {
    $query->where('guru_id', $user->id)->where('status_pkl', 'aktif');
} elseif ($user->role !== 'admin') {
    abort(403, 'Akses ditolak.');
}

    $siswas = $query->orderBy('name')->get();

    $lembar = [];
    foreach ($siswas as $siswa) {
        $data = $this->buildNilaiData($siswa);
        if ($data && $data['nilai']->rata_rata !== null) {
            $lembar[] = $data;
        }
    }

    abort_if(empty($lembar), 404, 'Belum ada nilai siswa yang bisa dicetak.');

    // Cetak semua = daftar berisi banyak siswa, tetap pakai template pdf.nilai
    $pdf = Pdf::loadView('pdf.nilai', ['lembar' => $lembar])->setPaper('a4', 'portrait');
    return $pdf->stream('Daftar_Nilai_PKL_Semua.pdf');
}

}