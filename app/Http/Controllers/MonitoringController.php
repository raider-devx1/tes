<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function jurnal(Request $request)
    {
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');

        $jurnal = Jurnal::query()
            ->with('siswa')
            ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
                $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($status, fn ($query) => $query->where('status_persetujuan', $status))
            ->orderByDesc('hari_tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'total'     => Jurnal::count(),
            'disetujui' => Jurnal::where('status_persetujuan', 'disetujui')->count(),
            'pending'   => Jurnal::where('status_persetujuan', 'pending')->count(),
            'revisi'    => Jurnal::where('status_persetujuan', 'revisi')->count(),
        ];

        return view('admin.monitoring.jurnal', compact('jurnal', 'q', 'status', 'rekap'));
    }

    public function catatan(Request $request)
    {
        $q = trim($request->get('q', ''));
        $approved = $request->get('approved', '');

        $catatan = CatatanKegiatan::query()
            ->with('user')
            ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($approved !== '', fn ($query) => $query->where('is_approved', $approved === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.monitoring.catatan', compact('catatan', 'q', 'approved'));
    }

    public function absensi(Request $request)
    {
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');
        $tanggal = $request->get('tanggal', '');

        $absensi = Absensi::query()
            ->with('siswa')
            ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
                $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($tanggal, fn ($query) => $query->whereDate('tanggal', $tanggal))
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'Hadir' => Absensi::where('status', 'Hadir')->count(),
            'Izin'  => Absensi::where('status', 'Izin')->count(),
            'Sakit' => Absensi::where('status', 'Sakit')->count(),
            'Alpha' => Absensi::where('status', 'Alpha')->count(),
        ];

        return view('admin.monitoring.absensi', compact('absensi', 'q', 'status', 'tanggal', 'rekap'));
    }
}