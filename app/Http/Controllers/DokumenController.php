<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Http\Request;

class DokumenController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $siswa = User::query()
            ->where('role', 'siswa_pkl')
            ->with('dokumen')
            ->when($q, fn ($query) => $query->where(fn ($w) =>
                $w->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'totalSiswa'      => User::where('role', 'siswa_pkl')->count(),
            'laporan'         => Dokumen::whereNotNull('laporan_akhir')->count(),
            'suratTugas'      => Dokumen::whereNotNull('surat_tugas')->count(),
            'suratPenerimaan' => Dokumen::whereNotNull('surat_penerimaan')->count(),
            'lengkap'         => Dokumen::whereNotNull('laporan_akhir')
                                    ->whereNotNull('surat_tugas')
                                    ->whereNotNull('surat_penerimaan')->count(),
        ];

        return view('admin.dokumen.index', compact('siswa', 'q', 'rekap'));
    }
}