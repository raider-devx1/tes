<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Satu controller untuk Jurnal Kegiatan, melayani 3 peran:
 *  - Siswa  : CRUD jurnal miliknya
 *  - Instruktur : melihat & menyetujui jurnal siswa bimbingannya
 *  - Guru   : memantau (read-only) jurnal siswa bimbingannya
 * Menggantikan JurnalSiswaController + sebagian InstrukturController + GuruController.
 */
class JurnalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $jurnals = Jurnal::with('siswa')
            ->when($user->isSiswa(), fn ($q) => $q->where('siswa_id', $user->id))
            ->when($user->isInstruktur(), fn ($q) => $q->whereIn('siswa_id', $this->siswaIds('instruktur_id')))
            ->when($user->isGuru(), fn ($q) => $q->whereIn('siswa_id', $this->siswaIds('guru_id')))
            ->when($request->filled('siswa_id'), fn ($q) => $q->where('siswa_id', $request->siswa_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status_persetujuan', $request->status))
            ->latest('hari_tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('jurnal.index', compact('jurnals'));
    }

    public function create()
    {
        $this->abortKecualiSiswa();
        return view('jurnal.create');
    }

    public function store(Request $request)
    {
        $this->abortKecualiSiswa();

        $data = $request->validate([
            'hari_tanggal'        => ['required', 'date'],
            'unit_kerja'          => ['required', 'string', 'max:255'],
            'deskripsi_pekerjaan' => ['required', 'string'],
            'dokumentasi'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($request->hasFile('dokumentasi')) {
            $data['dokumentasi'] = $request->file('dokumentasi')->store('jurnal', 'public');
        }
        $data['siswa_id'] = Auth::id();

        Jurnal::create($data);

        // Notifikasi ke instruktur
        if ($idInstruktur = Auth::user()->instruktur_id) {
            Notifikasi::kirim($idInstruktur, 'Jurnal baru', Auth::user()->name . ' menambahkan jurnal kegiatan.', route('jurnal.index'));
        }

        return redirect()->route('jurnal.index')->with('success', 'Jurnal harian berhasil ditambahkan.');
    }

    /** Instruktur menyetujui / merevisi jurnal. */
    public function approve(Request $request, Jurnal $jurnal)
    {
        $this->abortKecualiInstruktur($jurnal->siswa_id);

        $data = $request->validate([
            'status_persetujuan' => ['required', 'in:disetujui,revisi'],
            'catatan_instruktur' => ['nullable', 'string'],
        ]);
        $data['disetujui_oleh'] = Auth::id();
        $jurnal->update($data);

        Notifikasi::kirim($jurnal->siswa_id, 'Status jurnal diperbarui', 'Jurnal Anda telah ' . $data['status_persetujuan'] . '.', route('jurnal.index'));

        return back()->with('success', 'Status jurnal diperbarui.');
    }

    public function destroy(Jurnal $jurnal)
    {
        abort_unless($jurnal->siswa_id === Auth::id(), 403);

        if ($jurnal->status_persetujuan !== 'pending') {
            return back()->with('error', 'Jurnal yang sudah diproses instruktur tidak dapat dihapus.');
        }
        if ($jurnal->dokumentasi) {
            Storage::disk('public')->delete($jurnal->dokumentasi);
        }
        $jurnal->delete();

        return back()->with('success', 'Jurnal berhasil dihapus.');
    }

    /* ---------- helper privat (DRY) ---------- */
    private function siswaIds(string $kolom)
    {
        return User::where($kolom, Auth::id())->pluck('id');
    }
    private function abortKecualiSiswa(): void
    {
        abort_unless(Auth::user()->isSiswa(), 403);
    }
    private function abortKecualiInstruktur(int $siswaId): void
    {
        $user = Auth::user();
        abort_unless($user->isInstruktur() && User::where('id', $siswaId)->where('instruktur_id', $user->id)->exists(), 403);
    }
}
