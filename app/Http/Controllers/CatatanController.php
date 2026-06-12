<?php

namespace App\Http\Controllers;

use App\Models\CatatanKegiatan;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Catatan Kegiatan PKL untuk 3 peran (siswa isi, instruktur setujui, guru pantau).
 * Menggantikan CatatanSiswaController + CatatanGuruController + CatatanInstrukturController.
 */
class CatatanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $catatans = CatatanKegiatan::with('siswa')
            ->when($user->isSiswa(), fn ($q) => $q->where('siswa_id', $user->id))
            ->when($user->isInstruktur(), fn ($q) => $q->whereIn('siswa_id', User::where('instruktur_id', $user->id)->pluck('id')))
            ->when($user->isGuru(), fn ($q) => $q->whereIn('siswa_id', User::where('guru_id', $user->id)->pluck('id')))
            ->latest()
            ->paginate(15);

        return view('catatan.index', compact('catatans'));
    }

    public function create()
    {
        abort_unless(Auth::user()->isSiswa(), 403);
        return view('catatan.create');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSiswa(), 403);

        $data = $request->validate([
            'nama_pekerjaan' => ['required', 'string', 'max:255'],
            'perencanaan'    => ['required', 'string'],
            'pelaksanaan'    => ['required', 'string'],
        ]);
        $data['siswa_id'] = Auth::id();
        CatatanKegiatan::create($data);

        if ($idInstruktur = Auth::user()->instruktur_id) {
            Notifikasi::kirim($idInstruktur, 'Catatan baru', Auth::user()->name . ' menambahkan catatan kegiatan.', route('catatan.index'));
        }

        return redirect()->route('catatan.index')->with('success', 'Catatan kegiatan berhasil ditambahkan.');
    }

    public function approve(Request $request, CatatanKegiatan $catatan)
    {
        $user = Auth::user();
        abort_unless($user->isInstruktur() && User::where('id', $catatan->siswa_id)->where('instruktur_id', $user->id)->exists(), 403);

        $data = $request->validate([
            'status_persetujuan' => ['required', 'in:disetujui,revisi'],
            'catatan_instruktur' => ['nullable', 'string'],
        ]);
        $data['disetujui_oleh'] = Auth::id();
        $catatan->update($data);

        Notifikasi::kirim($catatan->siswa_id, 'Status catatan diperbarui', 'Catatan kegiatan Anda telah ' . $data['status_persetujuan'] . '.', route('catatan.index'));

        return back()->with('success', 'Status catatan diperbarui.');
    }

    public function destroy(CatatanKegiatan $catatan)
    {
        abort_unless($catatan->siswa_id === Auth::id() && $catatan->status_persetujuan === 'pending', 403);
        $catatan->delete();
        return back()->with('success', 'Catatan berhasil dihapus.');
    }
}
