<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /*
|--------------------------------------------------------------------------
| ROLE: SISWA PKL (melihat rekap kehadiran sendiri)
|--------------------------------------------------------------------------
*/
public function indexSiswa(Request $request)
{
    $query = Absensi::where('siswa_id', Auth::id());

    // Filter opsional per bulan (format: YYYY-MM)
    if ($request->filled('bulan')) {
        $tanggal = \Carbon\Carbon::parse($request->bulan . '-01');
        $query->whereYear('tanggal', $tanggal->year)
              ->whereMonth('tanggal', $tanggal->month);
    }

    $absensis = $query->orderBy('tanggal', 'desc')->get();

    // Rekap ringkas
    $rekap = [
        'Hadir' => $absensis->where('status', 'Hadir')->count(),
        'Izin'  => $absensis->where('status', 'Izin')->count(),
        'Sakit' => $absensis->where('status', 'Sakit')->count(),
        'Alpha' => $absensis->where('status', 'Alpha')->count(),
    ];

    $bulan = $request->bulan ?? date('Y-m');

    return view('siswa.absensi.index', compact('absensis', 'rekap', 'bulan'));
}

   // ====== ROLE: INSTRUKTUR INDUSTRI (mengisi absensi) ======
public function indexInstruktur(Request $request)
{
    $tanggal = $request->tanggal ?: date('Y-m-d');

    $query = User::where('role', 'siswa_pkl')
        ->where('instruktur_id', Auth::id());

    // Filter pencarian: Nama / NISN
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%");
        });
    }

    // Filter dropdown: status kehadiran pada tanggal terpilih
    if ($request->filled('status')) {
        $siswaIdsByStatus = Absensi::where('instruktur_id', Auth::id())
            ->where('tanggal', $tanggal)
            ->where('status', $request->status)
            ->pluck('siswa_id');
        $query->whereIn('id', $siswaIdsByStatus);
    }

    $siswas = $query->orderBy('name')->paginate(15)->withQueryString();

    // Data absensi tanggal terpilih (untuk prefill dropdown & jam)
    $absensis = Absensi::where('instruktur_id', Auth::id())
        ->where('tanggal', $tanggal)
        ->get()
        ->keyBy('siswa_id');

    return view('instruktur.absensi.index', compact('siswas', 'tanggal', 'absensis'));
}

    public function storeInstruktur(Request $request)
    {
        foreach ($request->absensi as $siswa_id => $data) {
            Absensi::updateOrCreate(
                ['siswa_id' => $siswa_id, 'tanggal' => $request->tanggal],
                ['instruktur_id' => Auth::id(), 'status' => $data['status'], 'jam_masuk' => $data['jam_masuk'], 'jam_pulang' => $data['jam_pulang']]
            );
        }
        return redirect()->back()->with('success', 'Absensi disimpan!');
    }
}