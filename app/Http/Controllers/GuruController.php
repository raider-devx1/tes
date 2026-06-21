<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jurnal;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    // Daftar siswa bimbingan
    public function index()
    {
        $siswas = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->get();
        return view('guru.siswa.index', compact('siswas'));
    }

    // (Opsional) detail 1 siswa — boleh tetap dipakai atau dihapus
    public function detailSiswa($id)
    {
        $siswa = User::findOrFail($id);
        if ($siswa->guru_id !== Auth::id()) {
            abort(403, 'Akses Ditolak: Bukan siswa bimbingan Anda.');
        }
        $jurnals  = Jurnal::where('siswa_id', $id)->orderBy('hari_tanggal', 'desc')->get();
        $absensis = Absensi::where('siswa_id', $id)->orderBy('tanggal', 'desc')->get();
        return view('guru.siswa.detail', compact('siswa', 'jurnals', 'absensis'));
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
            ->pluck('id');

        $jurnals = Jurnal::with('siswa')
            ->whereIn('siswa_id', $siswaIds)
            ->when($request->filled('siswa_id'), fn ($q) => $q->where('siswa_id', $request->siswa_id))
            ->when($request->filled('status'),   fn ($q) => $q->where('status_persetujuan', $request->status))
            ->orderByDesc('hari_tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'total'     => Jurnal::whereIn('siswa_id', $siswaIds)->count(),
            'disetujui' => Jurnal::whereIn('siswa_id', $siswaIds)->where('status_persetujuan', 'disetujui')->count(),
            'pending'   => Jurnal::whereIn('siswa_id', $siswaIds)->where('status_persetujuan', 'pending')->count(),
            'revisi'    => Jurnal::whereIn('siswa_id', $siswaIds)->where('status_persetujuan', 'revisi')->count(),
        ];

        $siswas = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->orderBy('name')->get();

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
            ->pluck('id');

        $absensi = Absensi::with('siswa')
            ->whereIn('siswa_id', $siswaIds)
            ->when($request->filled('siswa_id'), fn ($q) => $q->where('siswa_id', $request->siswa_id))
            ->when($request->filled('status'),   fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('tanggal'),  fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'Hadir' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Hadir')->count(),
            'Izin'  => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Izin')->count(),
            'Sakit' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Sakit')->count(),
            'Alpha' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Alpha')->count(),
        ];

        $siswas = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->orderBy('name')->get();

        return view('guru.monitoring.absensi', compact('absensi', 'rekap', 'siswas'));
    }
}