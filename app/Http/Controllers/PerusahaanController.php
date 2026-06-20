<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    /** Aturan validasi industri (dipakai store & update). */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'nama_perusahaan'     => ['required', 'string', 'max:150'],
            'bidang_usaha'        => ['nullable', 'string', 'max:150'],
            'alamat'              => ['required', 'string', 'max:255'],
            'telepon'             => ['nullable', 'string', 'max:20'],
            'email'               => ['nullable', 'email', 'max:150'],
            'pembimbing_industri' => ['nullable', 'string', 'max:150'],
            'kuota'               => ['required', 'integer', 'min:0', 'max:1000'],
        ]);
    }

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $industri = Perusahaan::query()
            ->withCount('siswa')
            ->when($q, function ($query) use ($q) {
                $query->where('nama_perusahaan', 'like', "%{$q}%")
                      ->orWhere('bidang_usaha', 'like', "%{$q}%")
                      ->orWhere('alamat', 'like', "%{$q}%");
            })
            ->orderBy('nama_perusahaan')
            ->paginate(10)
            ->withQueryString();

        return view('admin.industri.index', compact('industri', 'q'));
    }

    public function create()
    {
        return view('admin.industri.create', ['industri' => new Perusahaan()]);
    }

    public function store(Request $request)
    {
        Perusahaan::create($this->validateData($request));
        return redirect()->route('admin.industri.index')
            ->with('success', 'Data industri berhasil ditambahkan.');
    }

    public function edit(Perusahaan $industri)
    {
        return view('admin.industri.edit', compact('industri'));
    }

    public function update(Request $request, Perusahaan $industri)
    {
        $industri->update($this->validateData($request));
        return redirect()->route('admin.industri.index')
            ->with('success', 'Data industri berhasil diperbarui.');
    }

    public function destroy(Perusahaan $industri)
    {
        if ($industri->siswa()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus industri yang masih memiliki siswa PKL terdaftar.');
        }
        $industri->delete();
        return back()->with('success', 'Data industri berhasil dihapus.');
    }
}