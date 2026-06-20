<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodePklRequest;
use App\Models\PeriodePkl;
use Illuminate\Http\Request;

class PeriodePklController extends Controller
{
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

        return view('admin.periode.index', compact('periode', 'q'));
    }

    public function create()
    {
        return view('admin.periode.create', ['periode' => new PeriodePkl()]);
    }

    public function store(PeriodePklRequest $request)
    {
        PeriodePkl::create($request->validated());
        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode PKL berhasil ditambahkan.');
    }

    public function edit(PeriodePkl $periode)
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(PeriodePklRequest $request, PeriodePkl $periode)
    {
        $periode->update($request->validated());
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
}