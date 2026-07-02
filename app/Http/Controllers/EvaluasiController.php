<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;

class EvaluasiController extends Controller
{
    public function observasi(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $kelas   = $request->get('kelas');
        $jurusan = $request->get('jurusan');
        $status  = $request->get('status'); // '1' = disetujui, '0' = belum

        $observasi = Observasi::query()
            ->with(['user', 'guru'])
            ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_approved', $status === '1'))
            ->orderByDesc('hari_tanggal')
            ->paginate(15)
            ->withQueryString();

        [$kelasList, $jurusanList] = $this->opsiFilter();

        return view('admin.evaluasi.observasi', compact(
            'observasi', 'q', 'kelas', 'jurusan', 'status', 'kelasList', 'jurusanList'
        ));
    }

    public function penilaian(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $kelas   = $request->get('kelas');
        $jurusan = $request->get('jurusan');
        $status  = $request->get('status'); // 'sudah' | 'belum'

        // ===== KARTU RINGKASAN (keseluruhan siswa PKL, tidak terpengaruh filter) =====
        $totalSiswa = User::where('role', 'siswa_pkl')->count();

        // Sudah dinilai = punya baris nilai DAN nilai_akhir sudah terisi
        $sudahDinilai = User::where('role', 'siswa_pkl')
            ->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir'))
            ->count();

        // Belum lengkap = belum punya baris nilai sama sekali,
        // ATAU punya baris nilai tapi salah satu komponen belum terisi
        $belumLengkap = User::where('role', 'siswa_pkl')
            ->where(fn ($u) =>
                $u->whereDoesntHave('nilai')
                  ->orWhereHas('nilai', fn ($n) =>
                      $n->whereNull('rata_rata')
                        ->orWhereNull('nilai_guru')
                        ->orWhereNull('nilai_laporan')
                        ->orWhereNull('nilai_akhir')))
            ->count();

        // Basis query = SISWA PKL (bukan tabel nilai),
        // supaya siswa yang BELUM dinilai pun ikut tampil.
        $siswa = User::query()
            ->where('role', 'siswa_pkl')
            ->with(['nilai', 'nilai.instruktur', 'nilai.guru'])
            ->when($q, fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas, fn ($query) => $query->where('kelas', $kelas))
            ->when($jurusan, fn ($query) => $query->where('jurusan', $jurusan))
            // Sudah dinilai = punya baris nilai DAN nilai_akhir terisi
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir')))
            // Belum dinilai = tidak punya baris nilai SAMA SEKALI,
            // ATAU punya baris nilai tapi nilai_akhir masih kosong
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('nilai_akhir'))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        [$kelasList, $jurusanList] = $this->opsiFilter();

        return view('admin.evaluasi.penilaian', compact(
            'siswa', 'q', 'kelas', 'jurusan', 'status', 'kelasList', 'jurusanList',
            'totalSiswa', 'sudahDinilai', 'belumLengkap'
        ));
    }

    private function opsiFilter(): array
    {
        $kelasList = User::where('role', 'siswa_pkl')
            ->whereNotNull('kelas')->where('kelas', '!=', '')
            ->distinct()->orderBy('kelas')->pluck('kelas');

        $jurusanList = User::where('role', 'siswa_pkl')
            ->whereNotNull('jurusan')->where('jurusan', '!=', '')
            ->distinct()->orderBy('jurusan')->pluck('jurusan');

        return [$kelasList, $jurusanList];
    }
}