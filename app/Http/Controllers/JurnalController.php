<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JurnalController extends Controller
{
    // SISWA

public function indexSiswa(Request $request)
{
    $jurnals = Jurnal::where('siswa_id', Auth::id())
        ->when($request->filled('status'), fn ($q) => $q->where('status_persetujuan', $request->status))
        ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('hari_tanggal', $request->tanggal))
        ->orderBy('hari_tanggal', 'desc')
        ->paginate(15)
        ->withQueryString();

    return view('siswa.jurnal.index', compact('jurnals'));
}

    public function createSiswa()
    {
        return view('siswa.jurnal.create');
    }

    public function storeSiswa(Request $request)
    {
        $request->validate([
            'hari_tanggal' => 'required|date',
            'unit_kerja' => 'required|string|max:255',
            'dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('dokumentasi')) {
            $path = $request->file('dokumentasi')->store('dokumentasi_jurnal', 'public');
        }

        Jurnal::create([
            'siswa_id' => Auth::id(),
            'hari_tanggal' => $request->hari_tanggal,
            'unit_kerja' => $request->unit_kerja,
            'dokumentasi' => $path,
            'status_persetujuan' => 'pending',
        ]);

        return redirect()->route('siswa.jurnal.index')->with('success', 'Jurnal harian berhasil ditambahkan!');
    }

    public function destroySiswa($id)
    {
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        if ($jurnal->status_persetujuan !== 'pending') {
            return redirect()->back()->with('error', 'Jurnal yang sudah disetujui/direvisi tidak bisa dihapus.');
        }

        if ($jurnal->dokumentasi) {
            Storage::disk('public')->delete($jurnal->dokumentasi);
        }

        $jurnal->delete();
        return redirect()->route('siswa.jurnal.index')->with('success', 'Jurnal harian berhasil dihapus!');
    }

    
// INSTRUKTUR
public function indexInstruktur(Request $request)
{
    $siswaIds = User::where('role', 'siswa_pkl')
        ->where('instruktur_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->pluck('id');

    $jurnals = Jurnal::whereIn('siswa_id', $siswaIds)
        ->with('siswa')
        ->when($request->filled('q'), function ($query) use ($request) {
            $q = $request->q;
            $query->whereHas('siswa', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        })
        ->when($request->filled('status'), fn ($query) =>
            $query->where('status_persetujuan', $request->status))
        ->when($request->filled('tanggal'), fn ($query) =>          // ⬅️ filter tanggal
            $query->whereDate('hari_tanggal', $request->tanggal))
        ->orderBy('hari_tanggal', 'desc')
        ->paginate(15)
        ->withQueryString();

    return view('instruktur.jurnal.index', compact('jurnals'));
}

    public function updateInstruktur(Request $request, $id)
    {
        $jurnal = Jurnal::findOrFail($id);
        $jurnal->update([
            'status_persetujuan' => $request->status_persetujuan,
            'catatan_instruktur' => $request->catatan_instruktur,
            'disetujui_oleh' => Auth::id(),
        ]);
        return redirect()->back()->with('success', 'Status Jurnal diperbarui!');
    }
}