<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Pengaturan;
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
        return User::where('role', 'siswa_pkl')->where('status_pkl', '!=', 'selesai')->orderBy('name')->get(['id', 'name', 'nisn']);
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
        ->whereHas('siswa', fn ($s) => $s->where('status_pkl', '!=', 'selesai'))
        ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
            $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->when($tanggal, fn ($query) => $query->whereDate('hari_tanggal', $tanggal))
        ->orderByDesc('hari_tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekapBase = fn () => Jurnal::whereHas('siswa', fn ($s) => $s->where('status_pkl', '!=', 'selesai'));
    $rekap = [
        'total'     => $rekapBase()->count(),
        'disetujui' => $rekapBase()->where('status', 'disetujui')->count(),
        'diajukan'  => $rekapBase()->where('status', 'diajukan')->count(),
        'draft'     => $rekapBase()->where('status', 'draft')->count(),
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
        ->whereHas('user', fn ($u) => $u->where('status_pkl', '!=', 'selesai'))
        ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
            $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    $rekapBase = fn () => CatatanKegiatan::whereHas('user', fn ($u) => $u->where('status_pkl', '!=', 'selesai'));
    $rekap = [
        'total'     => $rekapBase()->count(),
        'disetujui' => $rekapBase()->where('status', 'disetujui')->count(),
        'diajukan'  => $rekapBase()->where('status', 'diajukan')->count(),
        'draft'     => $rekapBase()->where('status', 'draft')->count(),
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
    // Tandai otomatis Alpha (logika controller, menggantikan scheduler).
    User::where('role', 'siswa_pkl')->where('status_pkl', 'aktif')->get()
        ->each(fn ($s) => Absensi::sinkronkanAlpa($s));

    $q       = trim($request->get('q', ''));
    $status  = $request->get('status', '');
    $tanggal = $request->get('tanggal', '');
    $bulan   = $request->get('bulan', '');
    $kelas   = $request->get('kelas', '');
    $jurusan = $request->get('jurusan', '');

    $absensi = Absensi::query()
        ->with('siswa')
        ->whereHas('siswa', fn ($s) => $s->where('status_pkl', '!=', 'selesai'))
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

    $rekapBase = fn () => Absensi::whereHas('siswa', fn ($s) => $s->where('status_pkl', '!=', 'selesai'));
    $rekap = [
        'Hadir' => $rekapBase()->where('status', 'Hadir')->count(),
        'Izin'  => $rekapBase()->where('status', 'Izin')->count(),
        'Sakit' => $rekapBase()->where('status', 'Sakit')->count(),
        'Alpha' => $rekapBase()->where('status', 'Alpha')->count(),
    ];

    $tanggalDefault = $tanggal ?: date('Y-m-d');

    // Pengaturan jam & batas absensi yang berlaku untuk SEMUA siswa.
    $pengaturanAbsensi = [
        'jam_masuk'    => Pengaturan::ambil('absensi_jam_masuk', '08:00'),
        'jam_pulang'   => Pengaturan::ambil('absensi_jam_pulang', '16:00'),
        'durasi_menit' => (int) Pengaturan::ambil('absensi_durasi_menit', 30),
    ];

    // Status buka-paksa absensi global (true = absensi selalu terbuka, bebas waktu).
    $paksaBuka = Pengaturan::ambil('absensi_paksa_buka', '0') === '1';

    // Siswa yang absensinya dibuka manual per-orang (di luar buka global).
    $dibukaList = User::where('role', 'siswa_pkl')->where('absensi_dibuka', true)
        ->orderBy('name')->get(['id', 'name', 'nisn']);

    return view('admin.monitoring.absensi', array_merge(
        compact('absensi', 'q', 'status', 'tanggal', 'bulan', 'kelas', 'jurusan', 'rekap', 'tanggalDefault', 'pengaturanAbsensi', 'paksaBuka'),
        ['siswaList' => $this->siswaList(), 'dibukaList' => $dibukaList],
        $this->opsiFilter()
    ));
}

/**
 * Admin menyimpan pengaturan absensi GLOBAL untuk semua siswa:
 *  - jam masuk, jam pulang, dan batas (durasi menit) jendela absensi.
 * Nilai disimpan pada tabel pengaturans dan dipakai sebagai default jam siswa
 * (kecuali siswa memiliki jam khusus industri yang sudah disetujui guru).
 */
public function pengaturanAbsensi(Request $request)
{
    $data = $request->validate([
        'absensi_jam_masuk'    => ['required', 'date_format:H:i'],
        'absensi_jam_pulang'   => ['required', 'date_format:H:i'],
        'absensi_durasi_menit' => ['required', 'integer', 'min:1', 'max:1440'],
    ], [
        'absensi_jam_masuk.required'    => 'Jam masuk wajib diisi.',
        'absensi_jam_masuk.date_format' => 'Format jam masuk harus HH:MM.',
        'absensi_jam_pulang.required'   => 'Jam pulang wajib diisi.',
        'absensi_jam_pulang.date_format'=> 'Format jam pulang harus HH:MM.',
        'absensi_durasi_menit.required' => 'Batas absensi (menit) wajib diisi.',
    ]);

    Pengaturan::simpan('absensi_jam_masuk', $data['absensi_jam_masuk']);
    Pengaturan::simpan('absensi_jam_pulang', $data['absensi_jam_pulang']);
    Pengaturan::simpan('absensi_durasi_menit', (string) $data['absensi_durasi_menit']);

    return back()->with('success', 'Pengaturan absensi berhasil disimpan.');
}

/**
 * Admin membuka / menutup absensi tanpa mengikuti jadwal jam.
 *  - mode "semua" : buka/tutup untuk SEMUA siswa (flag global absensi_paksa_buka).
 *  - mode "nisn"  : buka/tutup untuk SATU siswa (dicocokkan berdasarkan NISN).
 *  - aksi "buka"  : absensi terbuka bebas waktu; "tutup" : kembali ikut jadwal.
 */
public function bukaAbsensi(Request $request)
{
    $mode = $request->input('mode') === 'nisn' ? 'nisn' : 'semua';
    $buka = $request->input('aksi') === 'buka';

    if ($mode === 'semua') {
        Pengaturan::simpan('absensi_paksa_buka', $buka ? '1' : '0');

        // Saat menutup global, matikan juga pembukaan per-siswa agar semua
        // benar-benar kembali mengikuti jadwal.
        if (! $buka) {
            User::where('role', 'siswa_pkl')->update(['absensi_dibuka' => false]);
        }

        return back()->with('success', $buka
            ? 'Absensi DIBUKA untuk semua siswa (bebas waktu, tidak mengikuti jadwal).'
            : 'Absensi ditutup untuk semua siswa. Kembali mengikuti jadwal jam.');
    }

    // mode "nisn": cocokkan NISN dengan data siswa PKL.
    $nisn = trim((string) $request->input('nisn', ''));
    if ($nisn === '') {
        return back()->with('error', 'NISN wajib diisi untuk membuka/menutup absensi per siswa.');
    }

    $siswa = User::where('role', 'siswa_pkl')->where('nisn', $nisn)->first();
    if (! $siswa) {
        return back()->with('error', "Siswa dengan NISN {$nisn} tidak ditemukan.");
    }

    $siswa->absensi_dibuka = $buka;
    $siswa->save();

    return back()->with('success', $buka
        ? "Absensi untuk {$siswa->name} (NISN {$nisn}) DIBUKA (bebas waktu)."
        : "Absensi untuk {$siswa->name} (NISN {$nisn}) ditutup (kembali ikut jadwal).");
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
