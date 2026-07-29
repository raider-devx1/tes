<?php

namespace App\Http\Controllers;

use App\Exports\InstrukturExport;
use App\Exports\InstrukturTemplateExport;
use App\Imports\InstrukturImport;
use App\Models\Perusahaan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
        $industriAdaSiswa   = Perusahaan::whereHas('siswa', fn ($s) => $s->berjalan())->count();
        $totalSiswaIndustri = User::siswaBerjalan()
            ->whereNotNull('perusahaan_id')->count();

        $rekap = [
            'total'          => $totalIndustri,
            'pembimbing'     => $totalPembimbing,
            'ada_siswa'      => $industriAdaSiswa,
            'siswa_industri' => $totalSiswaIndustri,
        ];

        $industri = Perusahaan::query()
            ->withCount(['siswa' => fn ($s) => $s->berjalan()])
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

    /**
     * Hapus SEMUA data industri sekaligus.
     * Kolom perusahaan_id pada siswa tidak memakai foreign key, jadi
     * dikosongkan manual agar tidak ada referensi yang menggantung.
     */
    public function destroyAll()
    {
        $total = Perusahaan::count();

        if ($total === 0) {
            return back()->with('error', 'Tidak ada data industri untuk dihapus.');
        }

        DB::transaction(function () {
            User::whereNotNull('perusahaan_id')->update(['perusahaan_id' => null]);

            Perusahaan::query()->delete();
        });

        return back()->with('success', "Berhasil menghapus {$total} data industri. Kolom industri pada siswa terkait ikut dikosongkan.");
    }

    // =====================================================
    //  IMPORT / EXPORT
    // =====================================================

    public function exportExcel(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        return Excel::download(new InstrukturExport($q), 'data-industri-' . date('Ymd-His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $industri = Perusahaan::query()
            ->withCount(['siswa' => fn ($s) => $s->berjalan()])
            ->when($q, function ($query) use ($q) {
                $query->where('nama_perusahaan', 'like', "%{$q}%")
                      ->orWhere('pembimbing_industri', 'like', "%{$q}%")
                      ->orWhere('alamat', 'like', "%{$q}%");
            })
            ->orderBy('nama_perusahaan')
            ->get();

        $pdf = Pdf::loadView('admin.instruktur.pdf', compact('industri'))->setPaper('a4', 'landscape');

        return $pdf->download('data-industri-' . date('Ymd-His') . '.pdf');
    }

    public function template()
    {
        return Excel::download(new InstrukturTemplateExport, 'template-import-industri.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            Excel::import(new InstrukturImport, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $pesan = collect($e->failures())
                ->map(fn ($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))
                ->take(10)
                ->implode(' | ');

            return back()->with('error', 'Sebagian data gagal diimpor. ' . $pesan);
        }

        return back()->with('success', 'Data industri berhasil diimpor.');
    }
}
