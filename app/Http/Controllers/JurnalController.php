<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JurnalController extends Controller
{
    // ============================ SISWA ============================

    public function indexSiswa(Request $request)
    {
        $jurnals = Jurnal::where('siswa_id', Auth::id())
            ->with('items')
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
        $validated = $request->validate([
            'hari_tanggal'        => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.unit_kerja'  => 'required|string',
            'items.*.dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'items.required'              => 'Minimal harus ada 1 pekerjaan / unit kerja.',
            'items.min'                   => 'Minimal harus ada 1 pekerjaan / unit kerja.',
            'items.*.unit_kerja.required' => 'Unit kerja / pekerjaan wajib diisi pada setiap poin.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $jurnal = Jurnal::create([
                'siswa_id'           => Auth::id(),
                'hari_tanggal'       => $validated['hari_tanggal'],
                'status_persetujuan' => 'pending',
            ]);

            foreach ($request->input('items', []) as $i => $item) {
                $path = null;
                if ($request->hasFile("items.$i.dokumentasi")) {
                    $path = $request->file("items.$i.dokumentasi")->store('dokumentasi_jurnal', 'public');
                }

                $jurnal->items()->create([
                    'unit_kerja'  => $item['unit_kerja'],
                    'dokumentasi' => $path,
                ]);
            }
        });

        return redirect()->route('siswa.jurnal.index')
            ->with('success', 'Jurnal harian berhasil ditambahkan!');
    }

    public function editSiswa($id)
    {
        // Edit selalu diizinkan (apa pun statusnya)
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())
            ->with('items')
            ->firstOrFail();

        return view('siswa.jurnal.edit', compact('jurnal'));
    }

    public function updateSiswa(Request $request, $id)
    {
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'hari_tanggal'        => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.unit_kerja'  => 'required|string',
            'items.*.dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'items.required'              => 'Minimal harus ada 1 pekerjaan / unit kerja.',
            'items.min'                   => 'Minimal harus ada 1 pekerjaan / unit kerja.',
            'items.*.unit_kerja.required' => 'Unit kerja / pekerjaan wajib diisi pada setiap poin.',
        ]);

        DB::transaction(function () use ($request, $validated, $jurnal) {
            // Setelah diedit: status kembali "pending" dan perlu disetujui ulang
            $jurnal->update([
                'hari_tanggal'       => $validated['hari_tanggal'],
                'status_persetujuan' => 'pending',
                'catatan_instruktur' => null,
                'disetujui_oleh'     => null,
            ]);

            $keptIds = [];

            foreach ($request->input('items', []) as $i => $item) {
                $existingId  = $item['id'] ?? null;
                $existingDoc = $item['existing_dokumentasi'] ?? null;

                // Tentukan path foto: pakai yang lama, kecuali ada upload baru
                $path = $existingDoc;
                if ($request->hasFile("items.$i.dokumentasi")) {
                    if ($existingDoc) {
                        Storage::disk('public')->delete($existingDoc);
                    }
                    $path = $request->file("items.$i.dokumentasi")->store('dokumentasi_jurnal', 'public');
                }

                if ($existingId && ($jItem = $jurnal->items()->find($existingId))) {
                    $jItem->update([
                        'unit_kerja'  => $item['unit_kerja'],
                        'dokumentasi' => $path,
                    ]);
                    $keptIds[] = $jItem->id;
                } else {
                    $new = $jurnal->items()->create([
                        'unit_kerja'  => $item['unit_kerja'],
                        'dokumentasi' => $path,
                    ]);
                    $keptIds[] = $new->id;
                }
            }

            // Hapus pekerjaan yang dibuang dari form (beserta fotonya)
            $toDelete = $jurnal->items()->whereNotIn('id', $keptIds)->get();
            foreach ($toDelete as $del) {
                if ($del->dokumentasi) {
                    Storage::disk('public')->delete($del->dokumentasi);
                }
                $del->delete();
            }
        });

        return redirect()->route('siswa.jurnal.index')
            ->with('success', 'Jurnal berhasil diperbarui. Status kembali menunggu persetujuan instruktur.');
    }

    public function destroySiswa($id)
    {
        // Hapus selalu diizinkan (apa pun statusnya)
        $jurnal = Jurnal::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        foreach ($jurnal->items as $item) {
            if ($item->dokumentasi) {
                Storage::disk('public')->delete($item->dokumentasi);
            }
        }

        $jurnal->delete(); // jurnal_items ikut terhapus (cascade)

        return redirect()->route('siswa.jurnal.index')
            ->with('success', 'Jurnal harian berhasil dihapus!');
    }

    // ========================== INSTRUKTUR ==========================

   public function indexInstruktur(Request $request)
{
    $siswaIds = User::where('role', 'siswa_pkl')
        ->where('instruktur_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->pluck('id');

    $jurnals = Jurnal::whereIn('siswa_id', $siswaIds)
        ->with(['siswa', 'items'])
        ->when($request->filled('q'), function ($query) use ($request) {
            $q = $request->q;
            $query->whereHas('siswa', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        })
        ->when($request->filled('status'), fn ($query) =>
            $query->where('status_persetujuan', $request->status))
        ->when($request->filled('tanggal'), fn ($query) =>
            $query->whereDate('hari_tanggal', $request->tanggal))
        ->orderBy('hari_tanggal', 'desc')
        ->paginate(15)
        ->withQueryString();

    // ---- Kartu informasi jurnal siswa bimbingan aktif (tidak terpengaruh filter) ----
    $rekapQuery = Jurnal::whereIn('siswa_id', $siswaIds);

    $rekap = [
        'total'     => (clone $rekapQuery)->count(),
        'disetujui' => (clone $rekapQuery)->where('status_persetujuan', 'disetujui')->count(),
        'pending'   => (clone $rekapQuery)->where('status_persetujuan', 'pending')->count(),
        'revisi'    => (clone $rekapQuery)->where('status_persetujuan', 'revisi')->count(),
    ];

    return view('instruktur.jurnal.index', compact('jurnals', 'rekap'));
}

     public function updateInstruktur(Request $request, $id)
    {
        // Ambil jurnal beserta pemiliknya
        $jurnal = Jurnal::with('siswa')->findOrFail($id);

        // Cegah IDOR: jurnal harus milik siswa bimbingan instruktur yang login & masih aktif PKL
        abort_unless(
            $jurnal->siswa
                && $jurnal->siswa->instruktur_id === Auth::id()
                && $jurnal->siswa->status_pkl === 'aktif',
            403,
            'Akses ditolak: jurnal ini bukan milik siswa bimbingan Anda.'
        );

        // Validasi input agar status tidak bisa diisi nilai sembarang
        $validated = $request->validate([
            'status_persetujuan' => 'required|in:disetujui,pending,revisi',
            'catatan_instruktur' => 'nullable|string',
        ], [
            'status_persetujuan.required' => 'Status persetujuan wajib dipilih.',
            'status_persetujuan.in'       => 'Status persetujuan tidak valid.',
        ]);

        $jurnal->update([
            'status_persetujuan' => $validated['status_persetujuan'],
            'catatan_instruktur' => $validated['catatan_instruktur'] ?? null,
            'disetujui_oleh'     => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Status Jurnal diperbarui!');
    }
}