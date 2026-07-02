<?php

namespace App\Http\Controllers;

use App\Models\PeriodePkl;
use Illuminate\Http\Request;

class PeriodePklController extends Controller
{
    /** Aturan validasi periode (dipakai store & update). */
    private function validateData(Request $request): array
    {
        $request->merge(['is_active' => $request->boolean('is_active')]);

        return $request->validate([
            'nama'            => ['required', 'string', 'max:100'],
            'tahun_ajaran'    => ['required', 'string', 'max:20'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'is_active'       => ['boolean'],
            'keterangan'      => ['nullable', 'string'],
        ], [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);
    }

   public function index(Request $request)
{
    $q = trim($request->get('q', ''));

    $periode = PeriodePkl::query()
        ->when($q, function ($query) use ($q) {
            $query->where('nama', 'like', "%{$q}%")
                  ->orWhere('tahun_ajaran', 'like', "%{$q}%");
        })
        ->orderByDesc('is_active')
        ->orderByDesc('tanggal_mulai')
        ->paginate(10)
        ->withQueryString();

    // Untuk dropdown "Atur Status Siswa per Periode" (tanpa pagination)
    $semuaPeriode = PeriodePkl::orderByDesc('is_active')
        ->orderByDesc('tanggal_mulai')
        ->get();

    return view('admin.periode.index', compact('periode', 'q', 'semuaPeriode'));
}

    public function create()
    {
        return view('admin.periode.create', ['periode' => new PeriodePkl()]);
    }

    public function store(Request $request)
    {
        PeriodePkl::create($this->validateData($request));
        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode PKL berhasil ditambahkan.');
    }

    public function edit(PeriodePkl $periode)
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(Request $request, PeriodePkl $periode)
    {
        $periode->update($this->validateData($request));
        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode PKL berhasil diperbarui.');
    }

    public function destroy(PeriodePkl $periode)
    {
        if ($periode->siswa()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus periode yang masih memiliki siswa terdaftar.');
        }
        $periode->delete();
        return back()->with('success', 'Periode PKL berhasil dihapus.');
    }

    /** Jadikan satu periode aktif (model otomatis menonaktifkan lainnya). */
    public function aktifkan(PeriodePkl $periode)
    {
        $periode->update(['is_active' => true]);
        return back()->with('success', "Periode \"{$periode->nama}\" kini menjadi periode aktif.");
    }

    /** Ubah status_pkl SEMUA siswa dalam satu periode sekaligus. */
public function updateStatusSiswa(Request $request)
{
    $request->validate([
        'periode_id' => ['required'],
        'status_pkl' => ['required', 'in:belum,aktif,selesai'],
    ], [
        'periode_id.required' => 'Silakan pilih periode terlebih dahulu.',
        'status_pkl.in'       => 'Status harus salah satu dari: belum, aktif, atau selesai.',
    ]);

    $periode = PeriodePkl::findOrFail($request->periode_id);

    $jumlah = \App\Models\User::where('role', 'siswa_pkl')
        ->where('periode_id', $periode->id)
        ->update(['status_pkl' => $request->status_pkl]);

    if ($jumlah === 0) {
        return back()->with('error', "Tidak ada siswa terdaftar pada periode \"{$periode->nama}\".");
    }

    return back()->with('success', "Status {$jumlah} siswa pada periode \"{$periode->nama}\" berhasil diubah menjadi \"{$request->status_pkl}\".");
}


}