<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MonitoringController extends Controller
{
    /** Opsi dropdown filter kelas & jurusan (diambil dari siswa PKL). */
    private function opsiFilter(): array
    {
        $base = User::where('role', 'siswa_pkl');

        return [
            'kelasList'   => (clone $base)->whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
            'jurusanList' => (clone $base)->whereNotNull('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
        ];
    }

    /** Daftar siswa PKL untuk dropdown form tambah/edit. */
    private function siswaList()
    {
        return User::where('role', 'siswa_pkl')->orderBy('name')->get(['id', 'name', 'nisn']);
    }

    // ===================================================================
// JURNAL  (skema baru: status = draft | diajukan | disetujui)
// ===================================================================
public function jurnal(Request $request)
{
    $q       = trim($request->get('q', ''));
    $status  = $request->get('status', '');
    $kelas   = $request->get('kelas', '');
    $jurusan = $request->get('jurusan', '');
    $tanggal = $request->get('tanggal', '');

    $jurnal = Jurnal::query()
        ->with(['siswa', 'items'])
        ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
            $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->when($tanggal, fn ($query) => $query->whereDate('hari_tanggal', $tanggal))
        ->orderByDesc('hari_tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'total'     => Jurnal::count(),
        'disetujui' => Jurnal::where('status', 'disetujui')->count(),
        'diajukan'  => Jurnal::where('status', 'diajukan')->count(),
        'draft'     => Jurnal::where('status', 'draft')->count(),
    ];

    return view('admin.monitoring.jurnal', array_merge(
        compact('jurnal', 'q', 'status', 'kelas', 'jurusan', 'tanggal', 'rekap'),
        ['siswaList' => $this->siswaList()],
        $this->opsiFilter()
    ));
}

public function storeJurnal(Request $request)
{
    $data = $request->validate([
        'siswa_id'            => ['required', 'exists:users,id'],
        'hari_tanggal'        => ['required', 'date'],
        'status'              => ['required', Rule::in(['draft', 'diajukan', 'disetujui'])],
        'catatan_instruktur'  => ['nullable', 'string'],
        'foto_bukti'          => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        'items'               => ['required', 'array', 'min:1'],
        'items.*.unit_kerja'  => ['required', 'string'],
        'items.*.dokumentasi' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ], [
        'items.required'              => 'Minimal harus ada 1 unit kerja / pekerjaan.',
        'items.min'                   => 'Minimal harus ada 1 unit kerja / pekerjaan.',
        'items.*.unit_kerja.required' => 'Unit kerja / pekerjaan wajib diisi pada setiap poin.',
    ]);

    DB::transaction(function () use ($request, $data) {
        $fotoBukti = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoBukti = $request->file('foto_bukti')->store('bukti_fisik/jurnal', 'public');
        }

        $jurnal = Jurnal::create([
            'siswa_id'             => $data['siswa_id'],
            'hari_tanggal'         => $data['hari_tanggal'],
            'status'               => $data['status'],
            'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
            'foto_bukti'           => $fotoBukti,
            'validated_by_guru_id' => $data['status'] === 'disetujui' ? Auth::id() : null,
            'validated_at'         => $data['status'] === 'disetujui' ? now() : null,
        ]);

        foreach ($request->input('items', []) as $i => $row) {
            $unit = trim((string) ($row['unit_kerja'] ?? ''));
            if ($unit === '') {
                continue;
            }
            $path = null;
            if ($request->hasFile("items.$i.dokumentasi")) {
                $path = $request->file("items.$i.dokumentasi")->store('dokumentasi_jurnal', 'public');
            }
            $jurnal->items()->create([
                'unit_kerja'  => $unit,
                'dokumentasi' => $path,
            ]);
        }
    });

    return back()->with('success', 'Jurnal berhasil ditambahkan.');
}

public function updateJurnal(Request $request, Jurnal $jurnal)
{
    $data = $request->validate([
        'siswa_id'                     => ['required', 'exists:users,id'],
        'hari_tanggal'                 => ['required', 'date'],
        'status'                       => ['required', Rule::in(['draft', 'diajukan', 'disetujui'])],
        'catatan_instruktur'           => ['nullable', 'string'],
        'foto_bukti'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        'hapus_foto_bukti'             => ['nullable', 'boolean'],
        'items'                        => ['nullable', 'array'],
        'items.*.id'                   => ['nullable', 'integer'],
        'items.*.unit_kerja'           => ['nullable', 'string'],
        'items.*.existing_dokumentasi' => ['nullable', 'string'],
        'items.*.dokumentasi'          => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ]);

    DB::transaction(function () use ($request, $data, $jurnal) {
        $fotoBukti = $jurnal->foto_bukti;
        if ($request->boolean('hapus_foto_bukti') && $fotoBukti) {
            Storage::disk('public')->delete($fotoBukti);
            $fotoBukti = null;
        }
        if ($request->hasFile('foto_bukti')) {
            if ($fotoBukti) {
                Storage::disk('public')->delete($fotoBukti);
            }
            $fotoBukti = $request->file('foto_bukti')->store('bukti_fisik/jurnal', 'public');
        }

        $jurnal->update([
            'siswa_id'             => $data['siswa_id'],
            'hari_tanggal'         => $data['hari_tanggal'],
            'status'               => $data['status'],
            'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
            'foto_bukti'           => $fotoBukti,
            'validated_by_guru_id' => $data['status'] === 'disetujui' ? ($jurnal->validated_by_guru_id ?? Auth::id()) : null,
            'validated_at'         => $data['status'] === 'disetujui' ? ($jurnal->validated_at ?? now()) : null,
        ]);

        $keptIds = [];
        foreach ($request->input('items', []) as $i => $row) {
            $unit        = trim((string) ($row['unit_kerja'] ?? ''));
            $existingId  = $row['id'] ?? null;
            $existingDoc = $row['existing_dokumentasi'] ?? null;

            // item lama dikosongkan -> hapus item + fotonya
            if ($existingId && $unit === '') {
                if ($item = $jurnal->items()->find($existingId)) {
                    if ($item->dokumentasi) {
                        Storage::disk('public')->delete($item->dokumentasi);
                    }
                    $item->delete();
                }
                continue;
            }
            if ($unit === '') {
                continue;
            }

            $path = $existingDoc;
            if ($request->hasFile("items.$i.dokumentasi")) {
                if ($existingDoc) {
                    Storage::disk('public')->delete($existingDoc);
                }
                $path = $request->file("items.$i.dokumentasi")->store('dokumentasi_jurnal', 'public');
            }

            if ($existingId && ($item = $jurnal->items()->find($existingId))) {
                $item->update(['unit_kerja' => $unit, 'dokumentasi' => $path]);
                $keptIds[] = $item->id;
            } else {
                $baru = $jurnal->items()->create(['unit_kerja' => $unit, 'dokumentasi' => $path]);
                $keptIds[] = $baru->id;
            }
        }

        $sisa = $jurnal->items()->whereNotIn('id', $keptIds)->get();
        foreach ($sisa as $item) {
            if ($item->dokumentasi) {
                Storage::disk('public')->delete($item->dokumentasi);
            }
            $item->delete();
        }
    });

    return back()->with('success', 'Jurnal berhasil diperbarui.');
}

public function destroyJurnal(Jurnal $jurnal)
{
    foreach ($jurnal->items as $item) {
        if ($item->dokumentasi) {
            Storage::disk('public')->delete($item->dokumentasi);
        }
    }
    if ($jurnal->foto_bukti) {
        Storage::disk('public')->delete($jurnal->foto_bukti);
    }
    $jurnal->items()->delete();
    $jurnal->delete();

    return back()->with('success', 'Jurnal berhasil dihapus.');
}

   // ===================================================================
// CATATAN KEGIATAN  (skema baru: status = draft | diajukan | disetujui)
// ===================================================================
public function catatan(Request $request)
{
    $q       = trim($request->get('q', ''));
    $status  = $request->get('status', '');
    $kelas   = $request->get('kelas', '');
    $jurusan = $request->get('jurusan', '');

    $catatan = CatatanKegiatan::query()
        ->with('user')
        ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
            $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'total'     => CatatanKegiatan::count(),
        'disetujui' => CatatanKegiatan::where('status', 'disetujui')->count(),
        'diajukan'  => CatatanKegiatan::where('status', 'diajukan')->count(),
        'draft'     => CatatanKegiatan::where('status', 'draft')->count(),
    ];

    return view('admin.monitoring.catatan', array_merge(
        compact('catatan', 'q', 'status', 'kelas', 'jurusan', 'rekap'),
        ['siswaList' => $this->siswaList()],
        $this->opsiFilter()
    ));
}

public function storeCatatan(Request $request)
{
    $data = $request->validate([
        'user_id'              => ['required', 'exists:users,id'],
        'nama_pekerjaan'       => ['required', 'string', 'max:255'],
        'perencanaan_kegiatan' => ['nullable', 'string'],
        'pelaksanaan_kegiatan' => ['nullable', 'string'],
        'catatan_instruktur'   => ['nullable', 'string'],
        'status'               => ['required', Rule::in(['draft', 'diajukan', 'disetujui'])],
        'foto_bukti'           => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ]);

    if ($request->hasFile('foto_bukti')) {
        $data['foto_bukti'] = $request->file('foto_bukti')->store('bukti_fisik/catatan', 'public');
    }
    $data['validated_by_guru_id'] = $data['status'] === 'disetujui' ? Auth::id() : null;
    $data['validated_at']         = $data['status'] === 'disetujui' ? now() : null;

    CatatanKegiatan::create($data);

    return back()->with('success', 'Catatan kegiatan berhasil ditambahkan.');
}

public function updateCatatan(Request $request, CatatanKegiatan $catatan)
{
    $data = $request->validate([
        'user_id'              => ['required', 'exists:users,id'],
        'nama_pekerjaan'       => ['required', 'string', 'max:255'],
        'perencanaan_kegiatan' => ['nullable', 'string'],
        'pelaksanaan_kegiatan' => ['nullable', 'string'],
        'catatan_instruktur'   => ['nullable', 'string'],
        'status'               => ['required', Rule::in(['draft', 'diajukan', 'disetujui'])],
        'foto_bukti'           => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        'hapus_foto_bukti'     => ['nullable', 'boolean'],
    ]);

    $fotoBukti = $catatan->foto_bukti;
    if ($request->boolean('hapus_foto_bukti') && $fotoBukti) {
        Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = null;
    }
    if ($request->hasFile('foto_bukti')) {
        if ($fotoBukti) Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = $request->file('foto_bukti')->store('bukti_fisik/catatan', 'public');
    }

    $catatan->update([
        'user_id'              => $data['user_id'],
        'nama_pekerjaan'       => $data['nama_pekerjaan'],
        'perencanaan_kegiatan' => $data['perencanaan_kegiatan'] ?? null,
        'pelaksanaan_kegiatan' => $data['pelaksanaan_kegiatan'] ?? null,
        'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
        'status'               => $data['status'],
        'foto_bukti'           => $fotoBukti,
        'validated_by_guru_id' => $data['status'] === 'disetujui' ? ($catatan->validated_by_guru_id ?? Auth::id()) : null,
        'validated_at'         => $data['status'] === 'disetujui' ? ($catatan->validated_at ?? now()) : null,
    ]);

    return back()->with('success', 'Catatan kegiatan berhasil diperbarui.');
}

public function destroyCatatan(CatatanKegiatan $catatan)
{
    if ($catatan->foto_bukti) {
        Storage::disk('public')->delete($catatan->foto_bukti);
    }
    $catatan->delete();

    return back()->with('success', 'Catatan kegiatan berhasil dihapus.');
}

   // ===================================================================
// ABSENSI  (mirror siswa: + filter bulan, status_validasi, foto_bukti)
// ===================================================================
public function absensi(Request $request)
{
    $q       = trim($request->get('q', ''));
    $status  = $request->get('status', '');
    $tanggal = $request->get('tanggal', '');
    $bulan   = $request->get('bulan', '');
    $kelas   = $request->get('kelas', '');
    $jurusan = $request->get('jurusan', '');

    $absensi = Absensi::query()
        ->with('siswa')
        ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
            $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->when($tanggal, fn ($query) => $query->whereDate('tanggal', $tanggal))
        ->when($bulan,   fn ($query) => $query->whereYear('tanggal', substr($bulan, 0, 4))
                                              ->whereMonth('tanggal', substr($bulan, 5, 2)))
        ->orderByDesc('tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'Hadir' => Absensi::where('status', 'Hadir')->count(),
        'Izin'  => Absensi::where('status', 'Izin')->count(),
        'Sakit' => Absensi::where('status', 'Sakit')->count(),
        'Alpha' => Absensi::where('status', 'Alpha')->count(),
    ];

    $tanggalDefault = $tanggal ?: date('Y-m-d');

    return view('admin.monitoring.absensi', array_merge(
        compact('absensi', 'q', 'status', 'tanggal', 'bulan', 'kelas', 'jurusan', 'rekap', 'tanggalDefault'),
        ['siswaList' => $this->siswaList()],
        $this->opsiFilter()
    ));
}

public function storeAbsensi(Request $request)
{
    $data = $request->validate([
        'siswa_id'           => ['required', 'exists:users,id'],
        'tanggal'            => ['required', 'date'],
        'status'             => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
        'jam_masuk'          => ['nullable', 'date_format:H:i'],
        'jam_pulang'         => ['nullable', 'date_format:H:i'],
        'status_validasi'    => ['required', Rule::in(['draft', 'diajukan', 'disetujui'])],
        'catatan_instruktur' => ['nullable', 'string'],
        'foto_bukti'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ]);

    $attrs = [
        'status'               => $data['status'],
        'jam_masuk'            => $data['jam_masuk'] ?? null,
        'jam_pulang'           => $data['jam_pulang'] ?? null,
        'status_validasi'      => $data['status_validasi'],
        'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
        'validated_by_guru_id' => $data['status_validasi'] === 'disetujui' ? Auth::id() : null,
        'validated_at'         => $data['status_validasi'] === 'disetujui' ? now() : null,
    ];
    if ($request->hasFile('foto_bukti')) {
        $attrs['foto_bukti'] = $request->file('foto_bukti')->store('bukti_fisik/absensi', 'public');
    }

    Absensi::updateOrCreate(
        ['siswa_id' => $data['siswa_id'], 'tanggal' => $data['tanggal']],
        $attrs
    );

    return back()->with('success', 'Absensi berhasil disimpan.');
}

public function updateAbsensi(Request $request, Absensi $absensi)
{
    $data = $request->validate([
        'siswa_id'           => ['required', 'exists:users,id'],
        'tanggal'            => ['required', 'date'],
        'status'             => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
        'jam_masuk'          => ['nullable', 'date_format:H:i'],
        'jam_pulang'         => ['nullable', 'date_format:H:i'],
        'status_validasi'    => ['required', Rule::in(['draft', 'diajukan', 'disetujui'])],
        'catatan_instruktur' => ['nullable', 'string'],
        'foto_bukti'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        'hapus_foto_bukti'   => ['nullable', 'boolean'],
    ]);

    $fotoBukti = $absensi->foto_bukti;
    if ($request->boolean('hapus_foto_bukti') && $fotoBukti) {
        Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = null;
    }
    if ($request->hasFile('foto_bukti')) {
        if ($fotoBukti) Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = $request->file('foto_bukti')->store('bukti_fisik/absensi', 'public');
    }

    $absensi->update([
        'siswa_id'             => $data['siswa_id'],
        'tanggal'              => $data['tanggal'],
        'status'               => $data['status'],
        'jam_masuk'            => $data['jam_masuk'] ?? null,
        'jam_pulang'           => $data['jam_pulang'] ?? null,
        'status_validasi'      => $data['status_validasi'],
        'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
        'foto_bukti'           => $fotoBukti,
        'validated_by_guru_id' => $data['status_validasi'] === 'disetujui' ? ($absensi->validated_by_guru_id ?? Auth::id()) : null,
        'validated_at'         => $data['status_validasi'] === 'disetujui' ? ($absensi->validated_at ?? now()) : null,
    ]);

    return back()->with('success', 'Absensi berhasil diperbarui.');
}

public function destroyAbsensi(Absensi $absensi)
{
    if ($absensi->foto_bukti) {
        Storage::disk('public')->delete($absensi->foto_bukti);
    }
    $absensi->delete();

    return back()->with('success', 'Absensi berhasil dihapus.');
}

}
