<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Pengaturan;
use App\Models\User;
use App\Support\ImageCompressor;
use Carbon\Carbon;
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
        // Opsi kelas & jurusan diambil dari SELURUH periode. Kalau dibatasi
        // periode aktif, kelas milik angkatan lama hilang dari dropdown dan
        // datanya jadi tidak bisa disaring sama sekali.
        $base = User::siswa()->withoutTrashed();

        return [
            'kelasList'   => (clone $base)->whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
            'jurusanList' => (clone $base)->whereNotNull('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
        ];
    }

    /**
     * Nilai filter "Status PKL" yang diizinkan.
     *
     * Nilai di luar daftar ini dianggap kosong (= tampilkan semua), sehingga
     * parameter URL yang diketik sembarangan tidak pernah masuk ke query.
     */
    private function statusPklValid($nilai): string
    {
        $nilai = is_string($nilai) ? trim(strtolower($nilai)) : '';

        return in_array($nilai, ['aktif', 'belum', 'selesai'], true) ? $nilai : '';
    }

    /**
     * Ekspresi ORDER BY untuk memprioritaskan siswa AKTIF di urutan pertama,
     * lalu BELUM, dan SELESAI paling akhir.
     *
     * Nama tabel diambil dari model (getTable()), bukan ditulis manual, agar
     * tetap benar bila konvensi penamaan tabel berubah. Subquery berkorelasi
     * dipakai supaya tidak perlu join -- join akan merusak whereHas() dan
     * paginate() yang sudah ada.
     */
    private function prioritasStatusSql(string $modelClass, string $kolomFk): string
    {
        $tabel = (new $modelClass)->getTable();

        return "(CASE (SELECT status_pkl FROM users WHERE users.id = {$tabel}.{$kolomFk})"
            . " WHEN 'aktif' THEN 1 WHEN 'belum' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END) ASC";
    }

    /** Daftar siswa PKL untuk dropdown form tambah/edit. */
    private function siswaList()
    {
        // Semua status (belum / aktif / selesai) DAN semua periode disertakan,
        // supaya admin tetap bisa menambah & memperbaiki data siswa angkatan
        // lama meskipun periode aktif sekarang sudah berganti.
        return User::siswa()->withoutTrashed()
            ->orderByRaw("CASE status_pkl WHEN 'aktif' THEN 1 WHEN 'belum' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get(['id', 'name', 'nisn', 'status_pkl']);
    }

    // ===================================================================
// JURNAL  (skema baru: status = draft | diajukan | disetujui)
// ===================================================================
public function jurnal(Request $request)
{
    $q         = trim($request->get('q', ''));
    $status    = $request->get('status', '');
    $statusPkl = $this->statusPklValid($request->get('status_pkl', ''));
    $kelas     = $request->get('kelas', '');
    $jurusan   = $request->get('jurusan', '');
    $tanggal   = $request->get('tanggal', '');

    $jurnal = Jurnal::query()
        ->with(['siswa', 'items'])
        ->whereHas('siswa', fn ($s) => $s->siswa()->withoutTrashed())
        ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
            $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->when($tanggal, fn ($query) => $query->whereDate('hari_tanggal', $tanggal))
        ->when($statusPkl, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('status_pkl', $statusPkl)))
        // Urutan utama: siswa AKTIF dulu, lalu BELUM, terakhir SELESAI.
        ->orderByRaw($this->prioritasStatusSql(Jurnal::class, 'siswa_id'))
        ->orderByDesc('hari_tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekapBase = fn () => Jurnal::whereHas('siswa', fn ($s) => $s->siswa()->withoutTrashed());
    $rekap = [
        'total'     => $rekapBase()->count(),
        'disetujui' => $rekapBase()->where('status', 'disetujui')->count(),
        'diajukan'  => $rekapBase()->where('status', 'diajukan')->count(),
        'draft'     => $rekapBase()->where('status', 'draft')->count(),
    ];

    return view('admin.monitoring.jurnal', array_merge(
        compact('jurnal', 'q', 'status', 'statusPkl', 'kelas', 'jurusan', 'tanggal', 'rekap'),
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
        'foto_bukti'          => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        'items'               => ['required', 'array', 'min:1'],
        'items.*.unit_kerja'  => ['required', 'string'],
        'items.*.dokumentasi' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
    ], [
        'items.required'              => 'Minimal harus ada 1 unit kerja / pekerjaan.',
        'items.min'                   => 'Minimal harus ada 1 unit kerja / pekerjaan.',
        'items.*.unit_kerja.required' => 'Unit kerja / pekerjaan wajib diisi pada setiap poin.',
    ]);

    DB::transaction(function () use ($request, $data) {
        $fotoBukti = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoBukti = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/jurnal');
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
                $path = ImageCompressor::store($request->file("items.$i.dokumentasi"), 'dokumentasi_jurnal');
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
        'foto_bukti'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        'hapus_foto_bukti'             => ['nullable', 'boolean'],
        'items'                        => ['nullable', 'array'],
        'items.*.id'                   => ['nullable', 'integer'],
        'items.*.unit_kerja'           => ['nullable', 'string'],
        'items.*.existing_dokumentasi' => ['nullable', 'string'],
        'items.*.dokumentasi'          => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
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
            $fotoBukti = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/jurnal');
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
                $path = ImageCompressor::store($request->file("items.$i.dokumentasi"), 'dokumentasi_jurnal');
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
    $q         = trim($request->get('q', ''));
    $status    = $request->get('status', '');
    $statusPkl = $this->statusPklValid($request->get('status_pkl', ''));
    $kelas     = $request->get('kelas', '');
    $jurusan   = $request->get('jurusan', '');

    $catatan = CatatanKegiatan::query()
        ->with('user')
        ->whereHas('user', fn ($u) => $u->siswa()->withoutTrashed())
        ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
            $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->when($statusPkl, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('status_pkl', $statusPkl)))
        // Urutan utama: siswa AKTIF dulu, lalu BELUM, terakhir SELESAI.
        ->orderByRaw($this->prioritasStatusSql(CatatanKegiatan::class, 'user_id'))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    $rekapBase = fn () => CatatanKegiatan::whereHas('user', fn ($u) => $u->siswa()->withoutTrashed());
    $rekap = [
        'total'     => $rekapBase()->count(),
        'disetujui' => $rekapBase()->where('status', 'disetujui')->count(),
        'diajukan'  => $rekapBase()->where('status', 'diajukan')->count(),
        'draft'     => $rekapBase()->where('status', 'draft')->count(),
    ];

    return view('admin.monitoring.catatan', array_merge(
        compact('catatan', 'q', 'status', 'statusPkl', 'kelas', 'jurusan', 'rekap'),
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
        'foto_bukti'           => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
    ]);

    if ($request->hasFile('foto_bukti')) {
        $data['foto_bukti'] = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/catatan');
    }

    // Kolom perencanaan_kegiatan & pelaksanaan_kegiatan bertipe NOT NULL di
    // basis data. Bila pengguna mengosongkannya, ConvertEmptyStringsToNull
    // mengubahnya menjadi null dan INSERT akan gagal. Simpan string kosong.
    $data['perencanaan_kegiatan'] = $data['perencanaan_kegiatan'] ?? '';
    $data['pelaksanaan_kegiatan'] = $data['pelaksanaan_kegiatan'] ?? '';

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
        'foto_bukti'           => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        'hapus_foto_bukti'     => ['nullable', 'boolean'],
    ]);

    $fotoBukti = $catatan->foto_bukti;
    if ($request->boolean('hapus_foto_bukti') && $fotoBukti) {
        Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = null;
    }
    if ($request->hasFile('foto_bukti')) {
        if ($fotoBukti) Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/catatan');
    }

    $catatan->update([
        'user_id'              => $data['user_id'],
        'nama_pekerjaan'       => $data['nama_pekerjaan'],
        // NOT NULL di basis data. Bila kunci tidak dikirim sama sekali
        // (mis. formulir parsial), nilai lama dipertahankan; bila dikirim
        // dalam keadaan kosong, disimpan sebagai string kosong -- bukan null,
        // karena null memicu galat 'NOT NULL constraint failed'.
        'perencanaan_kegiatan' => $request->has('perencanaan_kegiatan')
            ? ($data['perencanaan_kegiatan'] ?? '')
            : (string) $catatan->perencanaan_kegiatan,
        'pelaksanaan_kegiatan' => $request->has('pelaksanaan_kegiatan')
            ? ($data['pelaksanaan_kegiatan'] ?? '')
            : (string) $catatan->pelaksanaan_kegiatan,
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
    // SENGAJA tetap dibatasi periode berjalan. Baris ini MENULIS data (membuat
    // baris Alpha otomatis). Kalau dilepas ke semua periode, sistem akan terus
    // menambah Alpha untuk angkatan lama yang statusnya belum diubah -- itu
    // merusak data, bukan menampilkannya. Penyaringan lintas periode hanya
    // berlaku untuk PENAMPILAN data di bawah ini.
    User::siswaBerjalan()->where('status_pkl', 'aktif')->get()
        ->each(fn ($s) => Absensi::sinkronkanAlpa($s));

    $q         = trim($request->get('q', ''));
    $status    = $request->get('status', '');
    $statusPkl = $this->statusPklValid($request->get('status_pkl', ''));
    $tanggal   = $request->get('tanggal', '');
    $bulan     = $request->get('bulan', '');
    $kelas     = $request->get('kelas', '');
    $jurusan   = $request->get('jurusan', '');

    $absensi = Absensi::query()
        ->with('siswa')
        ->whereHas('siswa', fn ($s) => $s->siswa()->withoutTrashed())
        ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
            $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
        ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
        ->when($status,  fn ($query) => $query->where('status', $status))
        ->when($tanggal, fn ($query) => $query->whereDate('tanggal', $tanggal))
        ->when($bulan,   fn ($query) => $query->whereYear('tanggal', substr($bulan, 0, 4))
                                              ->whereMonth('tanggal', substr($bulan, 5, 2)))
        ->when($statusPkl, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('status_pkl', $statusPkl)))
        // Urutan utama: siswa AKTIF dulu, lalu BELUM, terakhir SELESAI.
        ->orderByRaw($this->prioritasStatusSql(Absensi::class, 'siswa_id'))
        ->orderByDesc('tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekapBase = fn () => Absensi::whereHas('siswa', fn ($s) => $s->siswa()->withoutTrashed());
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

    // Status buka-paksa absensi global, kini TERPISAH untuk fase masuk & pulang.
    // Flag lama absensi_paksa_buka (bila masih '1') dianggap membuka keduanya.
    $legacyGlobal = Pengaturan::ambil('absensi_paksa_buka', '0') === '1';
    $paksaMasuk   = $legacyGlobal || Pengaturan::ambil('absensi_paksa_buka_masuk', '0') === '1';
    $paksaPulang  = $legacyGlobal || Pengaturan::ambil('absensi_paksa_buka_pulang', '0') === '1';
    $paksaBuka    = $paksaMasuk || $paksaPulang;

    // Siswa yang absensinya dibuka manual per-orang (di luar buka global).
    // Lintas periode: siswa angkatan lama yang absensinya masih dibuka manual
    // harus tetap terlihat agar admin bisa menutupnya kembali.
    $dibukaList = User::siswa()->withoutTrashed()
        ->where(fn ($q) => $q->where('absensi_dibuka', true)
            ->orWhere('absensi_dibuka_masuk', true)
            ->orWhere('absensi_dibuka_pulang', true))
        ->orderBy('name')
        ->get(['id', 'name', 'nisn', 'absensi_dibuka', 'absensi_dibuka_masuk', 'absensi_dibuka_pulang']);

    // Data jam kerja industri per siswa (untuk pencarian & edit via NISN oleh admin).
    $siswaJam = User::siswa()->withoutTrashed()
        ->orderByRaw("CASE status_pkl WHEN 'aktif' THEN 1 WHEN 'belum' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END")
        ->orderBy('name')
        ->get(['id', 'name', 'nisn', 'kelas', 'status_pkl', 'jam_masuk_industri', 'jam_pulang_industri', 'jam_masuk_usulan', 'jam_pulang_usulan', 'status_jam_usulan', 'catatan_jam_usulan']);

    // Pengajuan jam yang masih menunggu validasi admin.
    $usulanJam = $siswaJam->where('status_jam_usulan', 'diajukan')->values();

    // Jam global admin sebagai acuan tampilan.
    $jamAdmin = [
        'masuk'  => $pengaturanAbsensi['jam_masuk'],
        'pulang' => $pengaturanAbsensi['jam_pulang'],
    ];

    return view('admin.monitoring.absensi', array_merge(
        compact('absensi', 'q', 'status', 'statusPkl', 'tanggal', 'bulan', 'kelas', 'jurusan', 'rekap', 'tanggalDefault', 'pengaturanAbsensi', 'paksaBuka', 'paksaMasuk', 'paksaPulang', 'siswaJam', 'usulanJam', 'jamAdmin'),
        ['siswaList' => $this->siswaList(), 'dibukaList' => $dibukaList],
        $this->opsiFilter()
    ));
}

/**
 * Admin menyimpan / mengubah jam kerja industri seorang siswa secara langsung.
 * Jam yang disimpan langsung berlaku sebagai jam khusus (status disetujui).
 */
public function updateJamAbsensi(Request $request, $siswa)
{
    $siswa = User::where('id', $siswa)->siswa()->firstOrFail();

    $validated = $request->validate([
        'jam_masuk_industri'  => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        'jam_pulang_industri' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
    ], [
        'jam_masuk_industri.required'  => 'Jam masuk wajib diisi.',
        'jam_pulang_industri.required' => 'Jam pulang wajib diisi.',
        'jam_masuk_industri.regex'     => 'Format jam masuk harus HH:MM.',
        'jam_pulang_industri.regex'    => 'Format jam pulang harus HH:MM.',
    ]);

    $siswa->update([
        'jam_masuk_industri'  => $this->normalizeJam($validated['jam_masuk_industri']),
        'jam_pulang_industri' => $this->normalizeJam($validated['jam_pulang_industri']),
        'status_jam_usulan'   => 'disetujui',
        'jam_masuk_usulan'    => null,
        'jam_pulang_usulan'   => null,
        'catatan_jam_usulan'  => null,
    ]);

    return back()->with('success', "Jam kerja industri {$siswa->name} berhasil diperbarui.");
}

/**
 * Admin menyetujui / menolak pengajuan jam kerja industri siswa.
 */
public function validasiJamAbsensi(Request $request, $siswa)
{
    $siswa = User::where('id', $siswa)->siswa()->firstOrFail();
    $aksi  = $request->input('aksi') === 'tolak' ? 'tolak' : 'setuju';

    if ($aksi === 'tolak') {
        $siswa->update([
            'status_jam_usulan'  => 'none',
            'jam_masuk_usulan'   => null,
            'jam_pulang_usulan'  => null,
            'catatan_jam_usulan' => null,
        ]);

        return back()->with('success', "Pengajuan jam {$siswa->name} ditolak. Siswa kembali memakai jam admin.");
    }

    $siswa->update([
        'jam_masuk_industri'  => $this->normalizeJam($siswa->jam_masuk_usulan),
        'jam_pulang_industri' => $this->normalizeJam($siswa->jam_pulang_usulan),
        'status_jam_usulan'   => 'disetujui',
        'jam_masuk_usulan'    => null,
        'jam_pulang_usulan'   => null,
        'catatan_jam_usulan'  => null,
    ]);

    return back()->with('success', "Pengajuan jam {$siswa->name} disetujui dan diterapkan.");
}

/** Normalisasi string jam ke format HH:MM:SS. */
private function normalizeJam(?string $value): ?string
{
    if (blank($value)) {
        return null;
    }

    $value = substr(trim($value), 0, 8);
    $parts = explode(':', $value);

    $jam   = str_pad((string) (int) ($parts[0] ?? 0), 2, '0', STR_PAD_LEFT);
    $menit = str_pad((string) (int) ($parts[1] ?? 0), 2, '0', STR_PAD_LEFT);
    $detik = str_pad((string) (int) ($parts[2] ?? 0), 2, '0', STR_PAD_LEFT);

    return "{$jam}:{$menit}:{$detik}";
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
    $mode   = $request->input('mode') === 'nisn' ? 'nisn' : 'semua';
    $buka   = $request->input('aksi') === 'buka';
    $target = in_array($request->input('target'), ['masuk', 'pulang'], true)
        ? $request->input('target')
        : 'semua';

    $labelTarget = [
        'masuk'  => 'Absen MASUK',
        'pulang' => 'Absen PULANG',
        'semua'  => 'Absensi (masuk & pulang)',
    ][$target];

    $kenaMasuk  = $target === 'masuk'  || $target === 'semua';
    $kenaPulang = $target === 'pulang' || $target === 'semua';

    if ($mode === 'semua') {
        if ($kenaMasuk) {
            Pengaturan::simpan('absensi_paksa_buka_masuk', $buka ? '1' : '0');
        }
        if ($kenaPulang) {
            Pengaturan::simpan('absensi_paksa_buka_pulang', $buka ? '1' : '0');
        }

        // Flag lama (membuka kedua fase sekaligus) tidak dipakai lagi; matikan
        // agar tidak "mengunci" kedua fase tetap terbuka.
        Pengaturan::simpan('absensi_paksa_buka', '0');

        // Saat menutup SEMUA, kembalikan juga pembukaan per-siswa ke jadwal.
        if (! $buka && $target === 'semua') {
            // SENGAJA tetap dibatasi periode berjalan: ini operasi tulis massal.
            User::siswaBerjalan()->update([
                'absensi_dibuka'        => false,
                'absensi_dibuka_masuk'  => false,
                'absensi_dibuka_pulang' => false,
            ]);
        }

        return back()->with('success', $buka
            ? "{$labelTarget} DIBUKA untuk semua siswa (bebas waktu). Fase lain tetap mengikuti jadwal."
            : "{$labelTarget} ditutup untuk semua siswa (kembali mengikuti jadwal jam).");
    }

    // mode "nisn": cocokkan NISN dengan data siswa PKL.
    $nisn = trim((string) $request->input('nisn', ''));
    if ($nisn === '') {
        return back()->with('error', 'NISN wajib diisi untuk membuka/menutup absensi per siswa.');
    }

    $siswa = User::siswa()->where('nisn', $nisn)->first();
    if (! $siswa) {
        return back()->with('error', "Siswa dengan NISN {$nisn} tidak ditemukan.");
    }

    if ($kenaMasuk) {
        $siswa->absensi_dibuka_masuk = $buka;
    }
    if ($kenaPulang) {
        $siswa->absensi_dibuka_pulang = $buka;
    }
    // Flag lama tidak dipakai lagi untuk buka per-siswa; pastikan mati.
    $siswa->absensi_dibuka = false;
    $siswa->save();

    return back()->with('success', $buka
        ? "{$labelTarget} untuk {$siswa->name} (NISN {$nisn}) DIBUKA (bebas waktu)."
        : "{$labelTarget} untuk {$siswa->name} (NISN {$nisn}) ditutup (kembali ikut jadwal).");
}

public function storeAbsensi(Request $request)
{
    $data = $request->validate([
        'siswa_id'           => ['required', 'exists:users,id'],
        'tanggal'            => ['required', 'date'],
        'status'             => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
        // Terima 'H:i' maupun 'H:i:s'. Peramban mengirim 'H:i', tetapi data
        // yang berasal dari database/tes sering berbentuk 'H:i:s'. Menolaknya
        // membuat penyimpanan gagal diam-diam (redirect back + error).
        'jam_masuk'          => ['nullable', 'date_format:H:i,H:i:s'],
        'jam_pulang'         => ['nullable', 'date_format:H:i,H:i:s'],
        'status_validasi'    => ['required', Rule::in(['draft', 'diajukan', 'disetujui'])],
        'catatan_instruktur' => ['nullable', 'string'],
        'foto_bukti'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
    ]);

    // Normalisasi tanggal ke 'Y-m-d'.
    $tanggal = Carbon::parse($data['tanggal'])->toDateString();

    // PENTING: 'jam_masuk' / 'jam_pulang' bersifat nullable. Bila pemanggil
    // tidak mengirim kuncinya sama sekali, kunci itu TIDAK ada di dalam $data.
    // Wajib memakai `?? null`, bukan akses langsung (memicu Undefined array key).
    $jamMasuk  = $data['jam_masuk']  ?? null;
    $jamPulang = $data['jam_pulang'] ?? null;

    $attrs = [
        'status'               => $data['status'],
        'jam_masuk'            => $jamMasuk  ? substr($jamMasuk, 0, 5)  : null,
        'jam_pulang'           => $jamPulang ? substr($jamPulang, 0, 5) : null,
        'status_validasi'      => $data['status_validasi'],
        'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
        'validated_by_guru_id' => $data['status_validasi'] === 'disetujui' ? Auth::id() : null,
        'validated_at'         => $data['status_validasi'] === 'disetujui' ? now() : null,
    ];
    if ($request->hasFile('foto_bukti')) {
        $attrs['foto_bukti'] = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/absensi');
    }

    // Pencarian baris lama memakai whereDate(), BUKAN updateOrCreate().
    // Alasannya: cast 'tanggal' => 'date' membuat Eloquent menuliskan nilai
    // sebagai '2026-07-29 00:00:00'. Di MySQL kolomnya bertipe DATE sehingga
    // bagian jam dipangkas dan pencocokan '2026-07-29' tetap berhasil; di
    // SQLite kolomnya TEXT sehingga string tersimpan berbeda dengan kunci
    // pencarian -> baris lama tidak ketemu -> INSERT baru -> menabrak unique
    // (siswa_id, tanggal). whereDate() membandingkan bagian tanggalnya saja,
    // sehingga perilakunya sama di kedua mesin basis data.
    $absensi = Absensi::where('siswa_id', $data['siswa_id'])
        ->whereDate('tanggal', $tanggal)
        ->first();

    if ($absensi) {
        $absensi->fill($attrs)->save();
    } else {
        Absensi::create($attrs + [
            'siswa_id' => $data['siswa_id'],
            'tanggal'  => $tanggal,
        ]);
    }

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
        'foto_bukti'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        'hapus_foto_bukti'   => ['nullable', 'boolean'],
    ]);

    $fotoBukti = $absensi->foto_bukti;
    if ($request->boolean('hapus_foto_bukti') && $fotoBukti) {
        Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = null;
    }
    if ($request->hasFile('foto_bukti')) {
        if ($fotoBukti) Storage::disk('public')->delete($fotoBukti);
        $fotoBukti = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/absensi');
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

/**
 * ATUR JUMLAH REKAP ABSENSI (Hadir / Izin / Sakit / Alpha) secara massal.
 *
 * Admin menentukan BERAPA BANYAK hari Hadir/Izin/Sakit/Alpha yang harus
 * dimiliki seorang siswa (mode "nisn") atau SELURUH siswa PKL berjalan
 * (mode "semua") di dalam satu rentang tanggal.
 *
 * Cara kerja:
 *  1. Seluruh baris absensi siswa di dalam rentang tanggal DIHAPUS lebih dulu
 *     (termasuk file foto buktinya) supaya hasil akhirnya persis sesuai jumlah
 *     yang diminta -- bukan menumpuk di atas data lama.
 *  2. Hari-hari dalam rentang diurutkan dari yang paling awal, lalu diisi
 *     berurutan: Hadir -> Izin -> Sakit -> Alpha.
 *  3. Sisa hari yang tidak terpakai diisi sesuai pilihan "status_sisa"
 *     (kosongkan / Hadir / Izin / Sakit / Alpha).
 *
 * Catatan penting:
 *  - Bila "status_sisa" dikosongkan, hari sisa yang sudah lewat AKAN ditandai
 *    Alpha otomatis oleh Absensi::sinkronkanAlpa() saat halaman absensi dibuka.
 *    Agar jumlah benar-benar terkunci, isi "status_sisa".
 *  - Baris Hadir otomatis diberi jam masuk & pulang efektif milik siswa.
 */
public function aturRekapAbsensi(Request $request)
{
    $data = $request->validate([
        'mode'               => ['required', Rule::in(['nisn', 'semua'])],
        'nisn'               => ['required_if:mode,nisn', 'nullable', 'string', 'max:20'],
        'tanggal_mulai'      => ['required', 'date'],
        'tanggal_selesai'    => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        'jumlah_hadir'       => ['required', 'integer', 'min:0', 'max:400'],
        'jumlah_izin'        => ['required', 'integer', 'min:0', 'max:400'],
        'jumlah_sakit'       => ['required', 'integer', 'min:0', 'max:400'],
        'jumlah_alpha'       => ['required', 'integer', 'min:0', 'max:400'],
        'lewati_akhir_pekan' => ['nullable', 'boolean'],
        'status_sisa'        => ['nullable', Rule::in(['', 'Hadir', 'Izin', 'Sakit', 'Alpha'])],
    ], [
        'nisn.required_if'                 => 'NISN wajib diisi untuk mode per siswa.',
        'tanggal_mulai.required'           => 'Tanggal mulai wajib diisi.',
        'tanggal_selesai.required'         => 'Tanggal selesai wajib diisi.',
        'tanggal_selesai.after_or_equal'   => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
    ]);

    $mulai   = Carbon::parse($data['tanggal_mulai'])->startOfDay();
    $selesai = Carbon::parse($data['tanggal_selesai'])->startOfDay();

    // Batas wajar: satu tahun. Mencegah rentang keliru (mis. salah ketik tahun)
    // menghasilkan puluhan ribu baris absensi.
    if ($mulai->diffInDays($selesai) > 366) {
        return back()->with('error', 'Rentang tanggal terlalu panjang. Maksimal 1 tahun.');
    }

    $lewatiAkhirPekan = $request->boolean('lewati_akhir_pekan');

    $tanggalTersedia = [];
    for ($d = $mulai->copy(); $d->lte($selesai); $d->addDay()) {
        if ($lewatiAkhirPekan && $d->isWeekend()) {
            continue;
        }
        $tanggalTersedia[] = $d->format('Y-m-d');
    }

    if (empty($tanggalTersedia)) {
        return back()->with('error', 'Tidak ada hari yang bisa diisi pada rentang tersebut (semua akhir pekan dilewati).');
    }

    // Urutan pengisian: Hadir dulu (hari paling awal), lalu Izin, Sakit, Alpha.
    $jumlah = [
        'Hadir' => (int) $data['jumlah_hadir'],
        'Izin'  => (int) $data['jumlah_izin'],
        'Sakit' => (int) $data['jumlah_sakit'],
        'Alpha' => (int) $data['jumlah_alpha'],
    ];
    $totalDiminta = array_sum($jumlah);

    if ($totalDiminta === 0 && blank($data['status_sisa'] ?? '')) {
        return back()->with('error', 'Isi minimal satu jumlah (Hadir/Izin/Sakit/Alpha), atau pilih status untuk sisa hari.');
    }

    if ($totalDiminta > count($tanggalTersedia)) {
        return back()->with('error', 'Total ' . $totalDiminta . ' hari melebihi ' . count($tanggalTersedia)
            . ' hari yang tersedia pada rentang ' . $mulai->format('d/m/Y') . ' - ' . $selesai->format('d/m/Y')
            . '. Perlebar rentang tanggal atau kurangi jumlahnya.');
    }

    // ---- Tentukan siswa sasaran ----
    if ($data['mode'] === 'nisn') {
        $nisn  = trim((string) ($data['nisn'] ?? ''));
        $siswa = User::siswa()->withoutTrashed()->where('nisn', $nisn)->first();

        if (! $siswa) {
            return back()->with('error', "Siswa dengan NISN {$nisn} tidak ditemukan.");
        }

        $sasaran = collect([$siswa]);
    } else {
        // Operasi tulis massal: SENGAJA dibatasi siswa periode berjalan agar
        // arsip angkatan lama tidak ikut ditimpa.
        $sasaran = User::siswaBerjalan()->get();

        if ($sasaran->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa PKL pada periode berjalan.');
        }
    }

    $statusSisa = $data['status_sisa'] ?? '';
    $now        = now();
    $adminId    = Auth::id();
    $periodeAktifId = Absensi::periodeAktifId();
    $totalBaris = 0;

    DB::transaction(function () use ($sasaran, $tanggalTersedia, $jumlah, $statusSisa, $mulai, $selesai, $now, $adminId, $periodeAktifId, &$totalBaris) {
        foreach ($sasaran as $siswa) {
            // 1) Bersihkan baris lama pada rentang ini (beserta foto buktinya).
            $lama = Absensi::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', '>=', $mulai->format('Y-m-d'))
                ->whereDate('tanggal', '<=', $selesai->format('Y-m-d'))
                ->get(['id', 'foto_bukti']);

            foreach ($lama as $row) {
                if ($row->foto_bukti) {
                    Storage::disk('public')->delete($row->foto_bukti);
                }
            }

            if ($lama->isNotEmpty()) {
                Absensi::whereIn('id', $lama->pluck('id'))->delete();
            }

            // 2) Susun baris baru sesuai jumlah yang diminta.
            $jamMasuk  = substr($siswa->jamMasukEfektif(), 0, 5);
            $jamPulang = substr($siswa->jamPulangEfektif(), 0, 5);
            $periodeId = $siswa->periode_id ?: $periodeAktifId;

            $baris = [];
            $i     = 0;

            foreach ($jumlah as $status => $banyak) {
                for ($n = 0; $n < $banyak; $n++, $i++) {
                    $baris[] = [
                        'siswa_id'             => $siswa->id,
                        'periode_id'           => $periodeId,
                        'tanggal'              => $tanggalTersedia[$i],
                        'status'               => $status,
                        'jam_masuk'            => $status === 'Hadir' ? $jamMasuk  : null,
                        'jam_pulang'           => $status === 'Hadir' ? $jamPulang : null,
                        'status_validasi'      => 'disetujui',
                        'validated_by_guru_id' => $adminId,
                        'validated_at'         => $now,
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];
                }
            }

            // 3) Sisa hari yang belum terpakai.
            if ($statusSisa !== '') {
                for (; $i < count($tanggalTersedia); $i++) {
                    $baris[] = [
                        'siswa_id'             => $siswa->id,
                        'periode_id'           => $periodeId,
                        'tanggal'              => $tanggalTersedia[$i],
                        'status'               => $statusSisa,
                        'jam_masuk'            => $statusSisa === 'Hadir' ? $jamMasuk  : null,
                        'jam_pulang'           => $statusSisa === 'Hadir' ? $jamPulang : null,
                        'status_validasi'      => 'disetujui',
                        'validated_by_guru_id' => $adminId,
                        'validated_at'         => $now,
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];
                }
            }

            if (! empty($baris)) {
                // insert() massal MELEWATI event model, karena itu periode_id
                // diisi manual di atas (trait MilikPeriodePkl tidak ikut jalan).
                foreach (array_chunk($baris, 500) as $potongan) {
                    Absensi::insertOrIgnore($potongan);
                }
                $totalBaris += count($baris);
            }

            // Bersihkan penanda cache sinkronisasi Alpha agar penandaan otomatis
            // menghitung ulang dari data yang baru saja disimpan.
            \Illuminate\Support\Facades\Cache::forget("sinkron_alpa:{$siswa->id}:" . $now->format('Y-m-d'));
        }
    });

    $ringkas = "Hadir {$jumlah['Hadir']}, Izin {$jumlah['Izin']}, Sakit {$jumlah['Sakit']}, Alpha {$jumlah['Alpha']}";
    $lingkup = $sasaran->count() === 1
        ? ('siswa ' . $sasaran->first()->name . ' (NISN ' . $sasaran->first()->nisn . ')')
        : ($sasaran->count() . ' siswa');

    $peringatan = $statusSisa === '' && $totalDiminta < count($tanggalTersedia)
        ? ' Sisa hari dibiarkan kosong dan bisa ditandai Alpha otomatis bila tanggalnya sudah lewat.'
        : '';

    return back()->with('success', "Rekap absensi untuk {$lingkup} berhasil diatur ({$ringkas}) pada "
        . $mulai->format('d/m/Y') . ' - ' . $selesai->format('d/m/Y') . ". Total {$totalBaris} baris ditulis." . $peringatan);
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
