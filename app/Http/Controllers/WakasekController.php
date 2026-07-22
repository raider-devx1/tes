<?php

namespace App\Http\Controllers;

use App\Models\Observasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ROLE TAMBAHAN: WAKASEK (guru pembimbing yang ditetapkan admin).
 *
 * Halaman ini hanya bisa diakses oleh guru pembimbing yang berstatus Wakasek
 * (is_wakasek = true). Wakasek bertugas memvalidasi lembar observasi yang
 * diajukan oleh para guru pembimbing.
 */
class WakasekController extends Controller
{
    /** Pastikan hanya Wakasek yang ditetapkan admin yang boleh mengakses. */
    private function pastikanWakasek(): void
    {
        if (! (Auth::user()->is_wakasek ?? false)) {
            abort(403, 'Halaman ini hanya untuk Wakasek yang ditetapkan oleh admin.');
        }
    }

    /** Daftar lembar observasi yang menunggu / sudah divalidasi. */
    public function observasi(Request $request)
    {
        $this->pastikanWakasek();

        $q      = trim((string) $request->get('q', ''));
        $status = $request->get('status', 'diajukan');

        $baseQuery = Observasi::query()
            ->whereHas('user', fn ($u) => $u->where('role', 'siswa_pkl'));

        $rekap = [
            'menunggu'    => (clone $baseQuery)->where('status', 'diajukan')->count(),
            'tervalidasi' => (clone $baseQuery)->where('status', 'tervalidasi')->count(),
            'total'       => (clone $baseQuery)->count(),
        ];

        $observasi = (clone $baseQuery)
            ->with(['user', 'guru', 'items', 'validator'])
            ->when(in_array($status, ['diajukan', 'tervalidasi'], true),
                fn ($query) => $query->where('status', $status))
            ->when($q, fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->whereHas('user', fn ($u) =>
                        $u->where('name', 'like', "%{$q}%")
                          ->orWhere('nisn', 'like', "%{$q}%"))
                    ->orWhereHas('guru', fn ($g) =>
                        $g->where('name', 'like', "%{$q}%")
                          ->orWhere('nip', 'like', "%{$q}%"));
            }))
            ->orderByRaw('COALESCE(diajukan_at, created_at) DESC')
            ->paginate(15)
            ->withQueryString();

        return view('guru.wakasek.observasi', compact('observasi', 'rekap', 'q', 'status'));
    }

    /** Wakasek memvalidasi lembar observasi yang diajukan guru. */
    public function validasi($id)
    {
        $this->pastikanWakasek();

        $observasi = Observasi::where('id', $id)
            ->where('status', 'diajukan')
            ->firstOrFail();

        $observasi->update([
            'status'               => 'tervalidasi',
            'validated_by_guru_id' => Auth::id(),
            'validated_at'         => now(),
        ]);

        return back()->with('success', 'Lembar observasi berhasil divalidasi. Hasil cetak kini menampilkan keterangan "SUDAH DIVALIDASI".');
    }

    /** Wakasek membatalkan validasi -> kembali menunggu divalidasi. */
    public function batal($id)
    {
        $this->pastikanWakasek();

        $observasi = Observasi::where('id', $id)
            ->where('status', 'tervalidasi')
            ->firstOrFail();

        $observasi->update([
            'status'               => 'diajukan',
            'validated_by_guru_id' => null,
            'validated_at'         => null,
        ]);

        return back()->with('success', 'Validasi dibatalkan. Lembar observasi kembali berstatus menunggu divalidasi.');
    }
}
