<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /** Halaman depan publik (welcome) beserta daftar FAQ. */
    public function welcome()
    {
        $faq = Informasi::where('tipe', 'faq')
            ->orderBy('urutan')
            ->orderByDesc('created_at')
            ->get();

        return view('welcome', compact('faq'));
    }

    /** Arahkan pengguna ke dashboard sesuai role-nya. */
    public function index()
    {
        $role = auth()->user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'guru_pembimbing') {
            return redirect()->route('guru.dashboard');
        }
        if ($role === 'siswa_pkl') {
            return redirect()->route('siswa.dashboard');
        }

        return abort(403);
    }

    /** Dashboard guru pembimbing + statistik bimbingan. */
    public function guru()
    {
        $guruId = Auth::id();

        $stats = User::siswaBerjalan()
            ->where('guru_id', $guruId)
            ->selectRaw("
                COUNT(*) as bimbingan,
                SUM(CASE WHEN status_pkl = 'aktif' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN status_pkl = 'belum' THEN 1 ELSE 0 END) as belum,
                SUM(CASE WHEN status_pkl = 'selesai' THEN 1 ELSE 0 END) as selesai
            ")
            ->first();

        return view('guru.dashboard', [
            'siswaBimbingan' => $stats->bimbingan ?? 0,
            'siswaAktif'     => $stats->aktif ?? 0,
            'siswaBelum'     => $stats->belum ?? 0,
            'siswaSelesai'   => $stats->selesai ?? 0,
        ]);
    }

    /** Dashboard siswa PKL + ringkasan jurnal. */
    public function siswa()
    {
        $jumlahJurnal    = Jurnal::where('siswa_id', Auth::id())->count();
        $jurnalDisetujui = Jurnal::where('siswa_id', Auth::id())
            ->where('status_persetujuan', 'disetujui')
            ->count();

        return view('siswa.dashboard', compact('jumlahJurnal', 'jurnalDisetujui'));
    }
}
