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

    /** Daftar seluruh observasi yang dibuat guru ini. */
   public function indexGuru(Request $request)
{
    $q      = trim($request->get('q', ''));
    $status = $request->get('status'); // '1' = disetujui, '0' = menunggu

    // Rekap seluruh observasi siswa bimbingan guru ini (tidak terpengaruh filter/pagination)
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


   /** Form tambah observasi (hanya siswa bimbingan guru ini yang bisa dipilih). */
public function createGuru()
{
    $siswas = User::where('role', 'siswa_pkl')
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif')   // ⬅️ hanya siswa aktif yang bisa dipilih
        ->orderBy('name')
        ->get();

    return view('guru.observasi.create', compact('siswas'));
}

    /** Simpan observasi baru (1 observasi = banyak poin permasalahan & solusi). */
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

        // Pastikan siswa yang dipilih benar-benar bimbingan guru ini
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

    /** Form edit observasi (hanya milik guru ini). */
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

/** Simpan perubahan observasi (poin lama diganti dengan poin baru). */
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

    // Pastikan siswa yang dipilih benar-benar bimbingan guru ini
    $siswa = User::where('id', $validated['user_id'])
        ->where('guru_id', Auth::id())
        ->firstOrFail();

    DB::transaction(function () use ($observasi, $validated, $siswa) {
        $observasi->update([
            'user_id'          => $siswa->id,
            'hari_tanggal'     => $validated['hari_tanggal'],
            'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
        ]);

        // Ganti seluruh poin dengan data terbaru dari form
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

/** Hapus observasi beserta seluruh poinnya. */
public function destroyGuru($id)
{
    $observasi = Observasi::where('id', $id)
        ->where('guru_id', Auth::id())
        ->firstOrFail();

    $observasi->delete(); // observasi_items ikut terhapus (onDelete cascade)

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

    /*
    |--------------------------------------------------------------------------
    | ROLE: INSTRUKTUR INDUSTRI (menyetujui observasi)
    |--------------------------------------------------------------------------
    */

  public function indexInstruktur(Request $request)
{
    $instruktur_id = Auth::id();

    // Rekap seluruh observasi siswa bimbingan aktif (tidak terpengaruh filter)
    $rekapQuery = Observasi::whereHas('user', function ($u) use ($instruktur_id) {
        $u->where('instruktur_id', $instruktur_id)->where('status_pkl', 'aktif');
    });

    $rekap = [
        'total'     => (clone $rekapQuery)->count(),
        'disetujui' => (clone $rekapQuery)->where('is_approved', true)->count(),
        'menunggu'  => (clone $rekapQuery)->where('is_approved', false)->count(),
    ];

    $observasi = Observasi::whereHas('user', function ($u) use ($instruktur_id, $request) {
            $u->where('instruktur_id', $instruktur_id)
                ->where('status_pkl', 'aktif');

            if ($request->filled('q')) {
                $q = $request->q;
                $u->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            }
        })
        ->with(['user', 'guru', 'items'])
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('is_approved', $request->status === 'disetujui');
        })
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('instruktur.observasi.index', compact('observasi', 'rekap'));
}

       public function approveInstruktur($id)
    {
        $observasi = Observasi::findOrFail($id);

        abort_unless($observasi->user->instruktur_id === Auth::id(), 403, 'Akses ditolak.');

        $observasi->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Observasi berhasil disetujui.');
    }

    /** Batalkan persetujuan (kembali ke status menunggu). */
    public function batalApproveInstruktur($id)
    {
        $observasi = Observasi::findOrFail($id);

        abort_unless($observasi->user->instruktur_id === Auth::id(), 403, 'Akses ditolak.');

        $observasi->update(['is_approved' => false]);

        return redirect()->back()->with('success', 'Persetujuan observasi berhasil dibatalkan.');
    }

}