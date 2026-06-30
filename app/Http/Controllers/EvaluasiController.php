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

        $nilai = Nilai::query()
            ->with(['user', 'instruktur', 'guru'])
            ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
            ->when($status === 'sudah', fn ($query) => $query->whereNotNull('nilai_akhir'))
            ->when($status === 'belum', fn ($query) => $query->whereNull('nilai_akhir'))
            ->orderByDesc('nilai_akhir')
            ->paginate(15)
            ->withQueryString();

        [$kelasList, $jurusanList] = $this->opsiFilter();

        return view('admin.evaluasi.penilaian', compact(
            'nilai', 'q', 'kelas', 'jurusan', 'status', 'kelasList', 'jurusanList'
        ));
    }

    public function rekap()
    {
        $totalSiswa   = User::where('role', 'siswa_pkl')->count();
        $sudahDinilai = Nilai::whereNotNull('nilai_akhir')->count();

        $belumLengkap = Nilai::where(function ($query) {
            $query->whereNull('rata_rata')
                  ->orWhereNull('nilai_guru')
                  ->orWhereNull('nilai_laporan');
        })->count();

        $rataKomponen = [
            'Soft Skill'    => round((float) Nilai::avg('soft_skill'), 2),
            'Hard Skill'    => round((float) Nilai::avg('hard_skill'), 2),
            'Pengembangan'  => round((float) Nilai::avg('pengembangan_hard_skill'), 2),
            'Kewirausahaan' => round((float) Nilai::avg('kewirausahaan'), 2),
        ];

        $statNilaiAkhir = [
            'rata'      => round((float) Nilai::whereNotNull('nilai_akhir')->avg('nilai_akhir'), 2),
            'tertinggi' => round((float) Nilai::max('nilai_akhir'), 2),
            'terendah'  => round((float) Nilai::whereNotNull('nilai_akhir')->min('nilai_akhir'), 2),
        ];

        $distribusi = [
            'A (>=85)'  => Nilai::where('nilai_akhir', '>=', 85)->count(),
            'B (70-84)' => Nilai::where('nilai_akhir', '>=', 70)->where('nilai_akhir', '<', 85)->count(),
            'C (60-69)' => Nilai::where('nilai_akhir', '>=', 60)->where('nilai_akhir', '<', 70)->count(),
            'D (<60)'   => Nilai::whereNotNull('nilai_akhir')->where('nilai_akhir', '<', 60)->count(),
        ];

        $peringkat = Nilai::with('user')
            ->whereNotNull('nilai_akhir')
            ->orderByDesc('nilai_akhir')
            ->take(10)
            ->get();

        return view('admin.evaluasi.rekap', compact(
            'totalSiswa', 'sudahDinilai', 'belumLengkap',
            'rataKomponen', 'statNilaiAkhir', 'distribusi', 'peringkat'
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