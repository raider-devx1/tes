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

    // Tandai otomatis Alpha (logika controller, menggantikan scheduler).
    User::whereIn('id', $siswaIds)->get()
        ->each(fn ($s) => Absensi::sinkronkanAlpa($s));

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

    // Daftar usulan jam kerja industri yang menunggu validasi guru.
    $usulanJam = $siswas->where('status_jam_usulan', 'diajukan')->values();

    // Pengaturan jam global admin (referensi untuk guru).
    $jamAdmin = [
        'masuk'  => \App\Models\Pengaturan::ambil('absensi_jam_masuk', '08:00'),
        'pulang' => \App\Models\Pengaturan::ambil('absensi_jam_pulang', '16:00'),
    ];

    return view('guru.monitoring.absensi', compact('absensi', 'rekap', 'siswas', 'usulanJam', 'jamAdmin'));
}

/**
 * Guru membuka / menutup absensi siswa BIMBINGANNYA tanpa mengikuti jadwal jam.
 *  - mode "semua" : semua siswa bimbingan guru ini.
 *  - mode "nisn"  : satu siswa (dicocokkan NISN & harus bimbingannya).
 *  - aksi "buka"  : terbuka bebas waktu; "tutup" : kembali ikut jadwal.
 */
public function bukaAbsensi(Request $request)
{
    $mode = $request->input('mode') === 'nisn' ? 'nisn' : 'semua';
    $buka = $request->input('aksi') === 'buka';

    $base = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id());

    if ($mode === 'semua') {
        (clone $base)->update(['absensi_dibuka' => $buka]);

        return back()->with('success', $buka
            ? 'Absensi DIBUKA untuk semua siswa bimbingan Anda (bebas waktu).'
            : 'Absensi ditutup untuk semua siswa bimbingan Anda. Kembali mengikuti jadwal.');
    }

    $nisn = trim((string) $request->input('nisn', ''));
    if ($nisn === '') {
        return back()->with('error', 'NISN wajib diisi untuk membuka/menutup absensi per siswa.');
    }

    $siswa = (clone $base)->where('nisn', $nisn)->first();
    if (! $siswa) {
        return back()->with('error', "Siswa bimbingan dengan NISN {$nisn} tidak ditemukan.");
    }

    $siswa->absensi_dibuka = $buka;
    $siswa->save();

    return back()->with('success', $buka
        ? "Absensi untuk {$siswa->name} (NISN {$nisn}) DIBUKA (bebas waktu)."
        : "Absensi untuk {$siswa->name} (NISN {$nisn}) ditutup (kembali ikut jadwal).");
}

}