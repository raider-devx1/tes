<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    
   // Daftar siswa bimbingan (tabel lengkap + filter + pagination)
public function index(Request $request)
{
    $query = User::where('role', 'siswa_pkl')
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif') // hanya siswa yang sedang aktif PKL
        ->with(['perusahaan', 'periode']);

    // Filter pencarian teks: nama, NISN, kelas, jurusan
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%")
                ->orWhere('kelas', 'like', "%{$q}%")
                ->orWhere('jurusan', 'like', "%{$q}%");
        });
    }

    // Filter dropdown: Periode PKL
    if ($request->filled('periode_id')) {
        $query->where('periode_id', $request->periode_id);
    }

    $siswas = $query->orderBy('name')->paginate(15)->withQueryString();

    $periodes = PeriodePkl::orderByDesc('tahun_ajaran')->orderBy('nama')->get();

    // Rekap seluruh siswa bimbingan (tidak terpengaruh filter/pagination)
    $rekapQuery = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id());

    $rekap = [
        'total'   => (clone $rekapQuery)->count(),
        'aktif'   => (clone $rekapQuery)->where('status_pkl', 'aktif')->count(),
        'belum'   => (clone $rekapQuery)->where('status_pkl', 'belum')->count(),
        'selesai' => (clone $rekapQuery)->where('status_pkl', 'selesai')->count(),
    ];

    return view('guru.siswa.index', compact('siswas', 'periodes', 'rekap'));
}

    

    /*
    |--------------------------------------------------------------------------
    | MONITORING 1: LIHAT JURNAL (hanya-baca, semua siswa bimbingan)
    |--------------------------------------------------------------------------
    */
  public function monitoringJurnal(Request $request)
{
    $siswaIds = User::where('role', 'siswa_pkl')
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->pluck('id');

    $jurnals = Jurnal::with(['siswa', 'items'])
        ->whereIn('siswa_id', $siswaIds)
        ->when($request->filled('q'), function ($query) use ($request) {
            $q = $request->q;
            $query->whereHas('siswa', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        })
        ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
        ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('hari_tanggal', $request->tanggal))
        ->orderByDesc('hari_tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'total'     => Jurnal::whereIn('siswa_id', $siswaIds)->count(),
        'disetujui' => Jurnal::whereIn('siswa_id', $siswaIds)->where('status', 'disetujui')->count(),
        'diajukan'  => Jurnal::whereIn('siswa_id', $siswaIds)->where('status', 'diajukan')->count(),
        'draft'     => Jurnal::whereIn('siswa_id', $siswaIds)->where('status', 'draft')->count(),
    ];

    $siswas = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->where('status_pkl', 'aktif')->orderBy('name')->get();

    return view('guru.monitoring.jurnal', compact('jurnals', 'rekap', 'siswas'));
}


    /*
    |--------------------------------------------------------------------------
    | MONITORING 2: ABSENSI (hanya-baca, semua siswa bimbingan)
    |--------------------------------------------------------------------------
    */
  public function monitoringAbsensi(Request $request)
{
    $siswaIds = User::where('role', 'siswa_pkl')
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->pluck('id');

    $absensi = Absensi::with('siswa')
        ->whereIn('siswa_id', $siswaIds)
        ->when($request->filled('q'), function ($query) use ($request) {
            $q = $request->q;
            $query->whereHas('siswa', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        })
        ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
        ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal', $request->tanggal))
        ->orderByDesc('tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'Hadir' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Hadir')->count(),
        'Izin'  => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Izin')->count(),
        'Sakit' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Sakit')->count(),
        'Alpha' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Alpha')->count(),
    ];

    $siswas = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->where('status_pkl', 'aktif')->orderBy('name')->get();

    return view('guru.monitoring.absensi', compact('absensi', 'rekap', 'siswas'));
}

}