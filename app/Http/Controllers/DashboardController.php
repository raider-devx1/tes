<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Observasi;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /** Satu pintu masuk dashboard, dialihkan sesuai peran. */
    public function index()
    {
        return match (Auth::user()->role) {
            User::ROLE_ADMIN      => $this->admin(),
            User::ROLE_GURU       => $this->guru(),
            User::ROLE_SISWA      => $this->siswa(),
            User::ROLE_INSTRUKTUR => $this->instruktur(),
            default               => abort(403),
        };
    }

    private function admin()
    {
        $stat = [
            'siswa'      => User::where('role', User::ROLE_SISWA)->count(),
            'guru'       => User::where('role', User::ROLE_GURU)->count(),
            'instruktur' => User::where('role', User::ROLE_INSTRUKTUR)->count(),
            'perusahaan' => Perusahaan::count(),
            'jurnal_pending'   => Jurnal::pending()->count(),
            'jurnal_disetujui' => Jurnal::disetujui()->count(),
        ];

        // Grafik aktivitas: jumlah jurnal 7 hari terakhir
        $grafik = Jurnal::selectRaw('DATE(hari_tanggal) as tgl, COUNT(*) as total')
            ->where('hari_tanggal', '>=', now()->subDays(6)->toDateString())
            ->groupBy('tgl')->orderBy('tgl')->pluck('total', 'tgl');

        return view('dashboard.admin', compact('stat', 'grafik'));
    }

    private function guru()
    {
        $siswaIds = User::where('guru_id', Auth::id())->pluck('id');
        $stat = [
            'siswa'            => $siswaIds->count(),
            'observasi'        => Observasi::where('guru_id', Auth::id())->count(),
            'jurnal_disetujui' => Jurnal::whereIn('siswa_id', $siswaIds)->disetujui()->count(),
        ];
        $siswas = User::where('guru_id', Auth::id())->withCount('jurnals')->get();

        return view('dashboard.guru', compact('stat', 'siswas'));
    }

    private function siswa()
    {
        $id = Auth::id();
        $stat = [
            'jurnal'           => Jurnal::where('siswa_id', $id)->count(),
            'jurnal_disetujui' => Jurnal::where('siswa_id', $id)->disetujui()->count(),
            'jurnal_pending'   => Jurnal::where('siswa_id', $id)->pending()->count(),
            'hadir'            => Absensi::where('siswa_id', $id)->where('status', 'hadir')->count(),
        ];
        $jurnalTerakhir = Jurnal::where('siswa_id', $id)->latest('hari_tanggal')->take(5)->get();

        return view('dashboard.siswa', compact('stat', 'jurnalTerakhir'));
    }

    private function instruktur()
    {
        $siswaIds = User::where('instruktur_id', Auth::id())->pluck('id');
        $stat = [
            'siswa'          => $siswaIds->count(),
            'jurnal_pending' => Jurnal::whereIn('siswa_id', $siswaIds)->pending()->count(),
            'hadir_hari_ini' => Absensi::whereIn('siswa_id', $siswaIds)->whereDate('tanggal', today())->where('status', 'hadir')->count(),
        ];
        $siswas = User::where('instruktur_id', Auth::id())->get();

        return view('dashboard.instruktur', compact('stat', 'siswas'));
    }
}
