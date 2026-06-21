<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /** Opsi dropdown filter kelas & jurusan (diambil dari siswa PKL). */
    private function opsiFilter(): array
    {
        $base = User::where('role', 'siswa_pkl');

        return [
            'kelasList'   => (clone $base)->whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
            'jurusanList' => (clone $base)->whereNotNull('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
        ];
    }

    public function jurnal(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $status  = $request->get('status', '');
        $kelas   = $request->get('kelas', '');
        $jurusan = $request->get('jurusan', '');

        $jurnal = Jurnal::query()
            ->with('siswa')
            ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
                $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
            ->when($status,  fn ($query) => $query->where('status_persetujuan', $status))
            ->orderByDesc('hari_tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'total'     => Jurnal::count(),
            'disetujui' => Jurnal::where('status_persetujuan', 'disetujui')->count(),
            'pending'   => Jurnal::where('status_persetujuan', 'pending')->count(),
            'revisi'    => Jurnal::where('status_persetujuan', 'revisi')->count(),
        ];

        return view('admin.monitoring.jurnal', array_merge(
            compact('jurnal', 'q', 'status', 'kelas', 'jurusan', 'rekap'),
            $this->opsiFilter()
        ));
    }

    public function catatan(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $approved = $request->get('approved', '');
        $kelas    = $request->get('kelas', '');
        $jurusan  = $request->get('jurusan', '');

        $catatan = CatatanKegiatan::query()
            ->with('user')
            ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas,   fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
            ->when($approved !== '', fn ($query) => $query->where('is_approved', $approved === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.monitoring.catatan', array_merge(
            compact('catatan', 'q', 'approved', 'kelas', 'jurusan'),
            $this->opsiFilter()
        ));
    }

    public function absensi(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $status  = $request->get('status', '');
        $tanggal = $request->get('tanggal', '');
        $kelas   = $request->get('kelas', '');
        $jurusan = $request->get('jurusan', '');

        $absensi = Absensi::query()
            ->with('siswa')
            ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
                $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
            ->when($status,  fn ($query) => $query->where('status', $status))
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

        return view('admin.monitoring.absensi', array_merge(
            compact('absensi', 'q', 'status', 'tanggal', 'kelas', 'jurusan', 'rekap'),
            $this->opsiFilter()
        ));
    }
}