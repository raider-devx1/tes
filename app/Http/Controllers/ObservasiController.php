<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObservasiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROLE: GURU PEMBIMBING (mengisi lembar observasi)
    |--------------------------------------------------------------------------
    */

    public function indexGuru(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $status = $request->get('status'); // '1' = disetujui, '0' = menunggu

        $rekapQuery = Observasi::where('guru_id', Auth::id())
            ->whereHas('user', fn ($u) => $u->where('status_pkl', 'aktif'));

        $rekap = [
            'total'     => (clone $rekapQuery)->count(),
            'disetujui' => (clone $rekapQuery)->where('is_approved', true)->count(),
            'menunggu'  => (clone $rekapQuery)->where('is_approved', false)->count(),
        ];

        $observasi = Observasi::where('guru_id', Auth::id())
            ->whereHas('user', fn ($u) => $u->where('status_pkl', 'aktif'))
            ->with(['user', 'items'])
            ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%")))
            ->when($status !== null && $status !== '', fn ($query) =>
                $query->where('is_approved', $status === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('guru.observasi.index', compact('observasi', 'q', 'status', 'rekap'));
    }

    public function createGuru()
    {
        $siswas = User::where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->orderBy('name')
            ->get();

        return view('guru.observasi.create', compact('siswas'));
    }

    public function storeGuru(Request $request)
    {
        $validated = $request->validate([
            'user_id'              => 'required|exists:users,id',
            'hari_tanggal'         => 'required|date',
            'pekerjaan_projek'     => 'nullable|string|max:255',
            'items'                => 'required|array|min:1',
            'items.*.permasalahan' => 'required|string',
            'items.*.solusi'       => 'required|string',
        ], [
            'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
            'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
            'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
        ]);

        $siswa = User::where('id', $validated['user_id'])
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        DB::transaction(function () use ($validated, $siswa) {
            $observasi = Observasi::create([
                'user_id'          => $siswa->id,
                'guru_id'          => Auth::id(),
                'hari_tanggal'     => $validated['hari_tanggal'],
                'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
                'is_approved'      => false,
            ]);

            foreach ($validated['items'] as $item) {
                $observasi->items()->create([
                    'permasalahan' => $item['permasalahan'],
                    'solusi'       => $item['solusi'],
                ]);
            }
        });

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Data observasi berhasil disimpan.');
    }

    public function editGuru($id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->with('items')
            ->firstOrFail();

        $siswas = User::where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('guru.observasi.edit', compact('observasi', 'siswas'));
    }

    public function updateGuru(Request $request, $id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'user_id'              => 'required|exists:users,id',
            'hari_tanggal'         => 'required|date',
            'pekerjaan_projek'     => 'nullable|string|max:255',
            'items'                => 'required|array|min:1',
            'items.*.permasalahan' => 'required|string',
            'items.*.solusi'       => 'required|string',
        ], [
            'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
            'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
            'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
        ]);

        $siswa = User::where('id', $validated['user_id'])
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        DB::transaction(function () use ($observasi, $validated, $siswa) {
            $observasi->update([
                'user_id'          => $siswa->id,
                'hari_tanggal'     => $validated['hari_tanggal'],
                'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
            ]);

            $observasi->items()->delete();
            foreach ($validated['items'] as $item) {
                $observasi->items()->create([
                    'permasalahan' => $item['permasalahan'],
                    'solusi'       => $item['solusi'],
                ]);
            }
        });

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Data observasi berhasil diperbarui.');
    }

    public function destroyGuru($id)
    {
        $observasi = Observasi::where('id', $id)
            ->where('guru_id', Auth::id())
            ->firstOrFail();

        $observasi->delete();

        return redirect()->route('guru.observasi.index')
            ->with('success', 'Data observasi berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: SISWA PKL (melihat observasi)
    |--------------------------------------------------------------------------
    */

    public function indexSiswa(Request $request)
    {
        $observasi = Observasi::where('user_id', Auth::id())
            ->with(['guru', 'items'])
            ->when($request->filled('status'), fn ($q) => $q->where('is_approved', $request->status === 'disetujui'))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('hari_tanggal', $request->tanggal))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('siswa.observasi.index', compact('observasi'));
    }
}