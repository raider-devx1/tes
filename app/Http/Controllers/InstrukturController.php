<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;

class InstrukturController extends Controller
{
    /**
     * Instruktur industri kini BUKAN akun login.
     * Halaman ini mengelola DATA INDUSTRI (perusahaan) sekaligus nama
     * pembimbing/instruktur industrinya sebagai teks biasa.
     */

    /** Validasi data industri + nama pembimbing (dipakai store & update). */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'nama_perusahaan'     => ['required', 'string', 'max:150'],
            'alamat'              => ['required', 'string', 'max:255'],
            'telepon'             => ['nullable', 'string', 'max:20'],
            'pembimbing_industri' => ['nullable', 'string', 'max:100'],
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $totalIndustri      = Perusahaan::count();
        $totalPembimbing    = Perusahaan::whereNotNull('pembimbing_industri')
            ->where('pembimbing_industri', '!=', '')->count();
        $industriAdaSiswa   = Perusahaan::whereHas('siswa')->count();
        $totalSiswaIndustri = User::where('role', 'siswa_pkl')
            ->whereNotNull('perusahaan_id')->count();

        $rekap = [
            'total'          => $totalIndustri,
            'pembimbing'     => $totalPembimbing,
            'ada_siswa'      => $industriAdaSiswa,
            'siswa_industri' => $totalSiswaIndustri,
        ];

        $industri = Perusahaan::query()
            ->withCount('siswa')
            ->when($q, function ($query) use ($q) {
                $query->where('nama_perusahaan', 'like', "%{$q}%")
                      ->orWhere('pembimbing_industri', 'like', "%{$q}%")
                      ->orWhere('alamat', 'like', "%{$q}%");
            })
            ->orderBy('nama_perusahaan')
            ->paginate(10)
            ->withQueryString();

        return view('admin.instruktur.index', compact('industri', 'q', 'rekap'));
    }

    public function create()
    {
        return view('admin.instruktur.create', ['perusahaan' => new Perusahaan()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Perusahaan::create($data);

        return redirect()->route('admin.instruktur.index')
            ->with('success', 'Data industri & pembimbingnya berhasil ditambahkan.');
    }

    public function edit(Perusahaan $perusahaan)
    {
        return view('admin.instruktur.edit', ['perusahaan' => $perusahaan]);
    }

    public function update(Request $request, Perusahaan $perusahaan)
    {
        $data = $this->validateData($request);

        $perusahaan->update($data);

        return redirect()->route('admin.instruktur.index')
            ->with('success', 'Data industri & pembimbingnya berhasil diperbarui.');
    }

    public function destroy(Perusahaan $perusahaan)
    {
        if ($perusahaan->siswa()->exists()
            || User::where('perusahaan_id', $perusahaan->id)->exists()) {
            return back()->with('error', 'Industri tidak bisa dihapus karena masih dipakai siswa.');
        }

        $perusahaan->delete();

        return back()->with('success', 'Data industri berhasil dihapus.');
    }
}
