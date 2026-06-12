<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lembar Observasi PKL.
 *  - Guru     : isi & kelola observasi siswa bimbingannya
 *  - Siswa    : melihat observasi atas dirinya
 *  - Instruktur : menyetujui observasi
 * Menggantikan ObservasiGuru/Siswa/InstrukturController.
 */
class ObservasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $observasis = Observasi::with(['siswa', 'guru'])
            ->when($user->isGuru(), fn ($q) => $q->where('guru_id', $user->id))
            ->when($user->isSiswa(), fn ($q) => $q->where('siswa_id', $user->id))
            ->when($user->isInstruktur(), fn ($q) => $q->whereIn('siswa_id', User::where('instruktur_id', $user->id)->pluck('id')))
            ->latest('hari_tanggal')
            ->paginate(15);

        return view('observasi.index', compact('observasis'));
    }

    public function create()
    {
        abort_unless(Auth::user()->isGuru(), 403);
        $siswas = User::where('guru_id', Auth::id())->where('role', User::ROLE_SISWA)->get();
        return view('observasi.create', compact('siswas'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isGuru(), 403);

        $data = $request->validate([
            'siswa_id'     => ['required', 'exists:users,id'],
            'hari_tanggal' => ['required', 'date'],
            'permasalahan' => ['required', 'string'],
            'solusi'       => ['required', 'string'],
        ]);
        $data['guru_id'] = Auth::id();
        Observasi::create($data);

        return redirect()->route('observasi.index')->with('success', 'Lembar observasi berhasil ditambahkan.');
    }

    public function approve(Observasi $observasi)
    {
        $user = Auth::user();
        abort_unless($user->isInstruktur() && User::where('id', $observasi->siswa_id)->where('instruktur_id', $user->id)->exists(), 403);

        $observasi->update(['status_persetujuan' => 'disetujui', 'disetujui_oleh' => Auth::id()]);
        return back()->with('success', 'Observasi disetujui.');
    }

    public function destroy(Observasi $observasi)
    {
        abort_unless($observasi->guru_id === Auth::id(), 403);
        $observasi->delete();
        return back()->with('success', 'Observasi dihapus.');
    }
}
