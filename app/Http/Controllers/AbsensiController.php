<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        $rekap = [
            'Hadir' => $absensis->where('status', 'Hadir')->count(),
            'Izin'  => $absensis->where('status', 'Izin')->count(),
            'Sakit' => $absensis->where('status', 'Sakit')->count(),
            'Alpha' => $absensis->where('status', 'Alpha')->count(),
        ];

        $bulan = $request->bulan ?? date('Y-m');

        return view('siswa.absensi.index', compact('absensis', 'rekap', 'bulan'));
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: INSTRUKTUR INDUSTRI (mengisi absensi)
    |--------------------------------------------------------------------------
    */
    public function indexInstruktur(Request $request)
    {
        $tanggal = $request->tanggal ?: date('Y-m-d');

        $query = User::where('role', 'siswa_pkl')
            ->where('instruktur_id', Auth::id())
            ->where('status_pkl', 'aktif'); // hanya siswa aktif

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

        $absensis = Absensi::where('instruktur_id', Auth::id())
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        // ---- Kartu informasi kehadiran pada tanggal terpilih ----
        $rekap = [
            'Hadir' => $absensis->where('status', 'Hadir')->count(),
            'Izin'  => $absensis->where('status', 'Izin')->count(),
            'Sakit' => $absensis->where('status', 'Sakit')->count(),
            'Alpha' => $absensis->where('status', 'Alpha')->count(),
        ];

        return view('instruktur.absensi.index', compact('siswas', 'tanggal', 'absensis', 'rekap'));
    }

    public function storeInstruktur(Request $request)
    {
        $validated = $request->validate([
            'tanggal'              => ['required', 'date'],
            'absensi'              => ['required', 'array'],
            'absensi.*.status'     => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
            'absensi.*.jam_masuk'  => ['nullable', 'date_format:H:i'],
            'absensi.*.jam_pulang' => ['nullable', 'date_format:H:i'],
        ]);

        // Hanya siswa bimbingan instruktur yang login (cegah manipulasi siswa_id)
        $siswaValid = User::where('role', 'siswa_pkl')
            ->where('instruktur_id', Auth::id())
            ->pluck('id')
            ->flip();

        DB::transaction(function () use ($validated, $siswaValid) {
            foreach ($validated['absensi'] as $siswaId => $data) {
                if (! isset($siswaValid[$siswaId])) {
                    continue; // abaikan siswa yang bukan bimbingannya
                }

                Absensi::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tanggal' => $validated['tanggal']],
                    [
                        'instruktur_id' => Auth::id(),
                        'status'        => $data['status'],
                        'jam_masuk'     => $data['jam_masuk'] ?? null,
                        'jam_pulang'    => $data['jam_pulang'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success', 'Absensi berhasil disimpan!');
    }
}