<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodePkl;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = PeriodePkl::latest('tanggal_mulai')->paginate(15);
        return view('admin.periode.index', compact('periodes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'            => ['required', 'string', 'max:255'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'aktif'           => ['nullable', 'boolean'],
        ]);
        $data['aktif'] = $request->boolean('aktif');

        if ($data['aktif']) {
            PeriodePkl::query()->update(['aktif' => false]); // hanya satu periode aktif
        }
        PeriodePkl::create($data);

        return back()->with('success', 'Periode PKL berhasil ditambahkan.');
    }

    public function aktifkan(PeriodePkl $periode)
    {
        PeriodePkl::query()->update(['aktif' => false]);
        $periode->update(['aktif' => true]);
        return back()->with('success', 'Periode "' . $periode->nama . '" diaktifkan.');
    }

    public function destroy(PeriodePkl $periode)
    {
        $periode->delete();
        return back()->with('success', 'Periode PKL dihapus.');
    }
}
