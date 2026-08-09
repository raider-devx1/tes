<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Pengaturan;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\TandaTangan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
     * Nilai filter "Status Validasi" absensi yang diizinkan.
     *
     * - draft     : masih draft (belum diajukan siswa)
     * - diajukan  : menunggu divalidasi
     * - disetujui : sudah tervalidasi
     * - belum     : gabungan draft + diajukan (semua yang belum tervalidasi)
     *
     * Nilai di luar daftar ini dianggap kosong (= tampilkan semua), sehingga
     * parameter URL yang diketik sembarangan tidak pernah masuk ke query.
     */
    private function statusValidasiValid($nilai): string
    {
        $nilai = is_string($nilai) ? trim(strtolower($nilai)) : '';

        return in_array($nilai, ['draft', 'diajukan', 'disetujui', 'belum'], true) ? $nilai : '';
    }

    /**
     * Terapkan filter status validasi ke query absensi.
     *
     * Baris absensi lama bisa memiliki status_validasi NULL. Nilai NULL itu
     * diperlakukan sama dengan 'draft', persis seperti yang ditampilkan pada
     * tabel (status_validasi ?? 'draft'), supaya hasil filter tidak bocor.
     */
    private function filterStatusValidasi($query, string $statusValidasi)
    {
        if ($statusValidasi === 'draft') {
            $query->where(fn ($x) => $x->where('status_validasi', 'draft')->orWhereNull('status_validasi'));
        } elseif ($statusValidasi === 'belum') {
            $query->where(fn ($x) => $x->whereIn('status_validasi', ['draft', 'diajukan'])->orWhereNull('status_validasi'));
        } elseif ($statusValidasi !== '') {
            $query->where('status_validasi', $statusValidasi);
        }

        return $query;
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
            ->with('perusahaan:id,pembimbing_industri')
            ->orderByRaw("CASE status_pkl WHEN 'aktif' THEN 1 WHEN 'belum' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get(['id', 'name', 'nisn', 'status_pkl', 'perusahaan_id'])
            ->each(function ($siswa) {
                // Nama instruktur ikut dikirim ke form agar teks "Diparaf oleh" terisi otomatis.
                $siswa->setAttribute('instruktur_nama', $this->namaInstrukturSiswa($siswa));
                $siswa->unsetRelation('perusahaan');
            });
    }

    /**
     * Nama instruktur (pembimbing industri) milik seorang siswa.
     * Dipakai untuk mengisi otomatis nama di bawah paraf digital.
     */
    private function namaInstrukturSiswa($siswa): ?string
    {
        if (! $siswa instanceof User) {
            $siswa = $siswa ? User::find($siswa) : null;
        }

        $nama = $siswa ? ($siswa->instruktur->name ?? null) : null;

        return ($nama && $nama !== 'Belum Diatur') ? $nama : null;
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
        // Tanda tangan digital instruktur (opsional), dikirim sebagai PNG base64 dari kanvas.
        'ttd_instruktur'      => ['nullable', 'string'],
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

        // Paraf digital instruktur (opsional): disimpan sebagai berkas PNG.
        $ttdPath = TandaTangan::simpan($request->input('ttd_instruktur'), 'ttd/jurnal');

        $jurnal = Jurnal::create([
            'siswa_id'             => $data['siswa_id'],
            'hari_tanggal'         => $data['hari_tanggal'],
            'status'               => $data['status'],
            'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
            'foto_bukti'           => $fotoBukti,
            'ttd_instruktur'       => $ttdPath,
            'ttd_instruktur_nama'  => $ttdPath ? $this->namaInstrukturSiswa($data['siswa_id']) : null,
            'ttd_signed_at'        => $ttdPath ? now() : null,
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
        'ttd_instruktur'               => ['nullable', 'string'],
        'hapus_ttd_instruktur'         => ['nullable', 'boolean'],
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

        // ---- Paraf digital instruktur (opsional) ----
        $ttdPath  = $jurnal->ttd_instruktur;
        $ttdNama  = $jurnal->ttd_instruktur_nama;
        $ttdWaktu = $jurnal->ttd_signed_at;

        if ($request->boolean('hapus_ttd_instruktur') && $ttdPath) {
            TandaTangan::hapus($ttdPath);
            $ttdPath  = null;
            $ttdNama  = null;
            $ttdWaktu = null;
        }

        $ttdBaru = TandaTangan::simpan($request->input('ttd_instruktur'), 'ttd/jurnal');
        if ($ttdBaru) {
            TandaTangan::hapus($ttdPath);   // ganti paraf lama bila ada
            $ttdPath  = $ttdBaru;
            $ttdNama  = $this->namaInstrukturSiswa($jurnal->siswa_id);
            $ttdWaktu = now();
        }

        $jurnal->update([
            'siswa_id'             => $data['siswa_id'],
            'hari_tanggal'         => $data['hari_tanggal'],
            'status'               => $data['status'],
            'catatan_instruktur'   => $data['catatan_instruktur'] ?? null,
            'foto_bukti'           => $fotoBukti,
            'ttd_instruktur'       => $ttdPath,
            'ttd_instruktur_nama'  => $ttdNama,
            'ttd_signed_at'        => $ttdWaktu,
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
    TandaTangan::hapus($jurnal->ttd_instruktur);
    $jurnal->items()->delete();
    $jurnal->delete();

    return back()->with('success', 'Jurnal berhasil dihapus.');
}

/**
 * PRATINJAU VALIDASI JURNAL (JSON, dipanggil modal admin).
 *
 * Menghitung berapa jurnal yang akan terkena aksi validasi untuk filter yang
 * sedang dipilih: BEBERAPA NISN sekaligus atau semua siswa, pada satu hari
 * tertentu / rentang tanggal / seluruh riwayat.
 */
public function pratinjauValidasiJurnal(Request $request)
{
    $mode  = $request->query('mode') === 'semua' ? 'semua' : 'nisn';
    $jenis = in_array($request->query('jenis_tanggal'), ['tanggal', 'rentang', 'semua'], true)
        ? (string) $request->query('jenis_tanggal')
        : 'rentang';

    $tanggal = $request->query('tanggal');
    $mulai   = $request->query('tanggal_mulai');
    $selesai = $request->query('tanggal_selesai');

    $daftarNisn = $this->pecahDaftar($request->query('nisn', ''));

    if ($mode === 'nisn' && empty($daftarNisn)) {
        return response()->json(['ok' => false, 'pesan' => 'Masukkan minimal satu NISN.'], 422);
    }

    if (count($daftarNisn) > 300) {
        return response()->json(['ok' => false, 'pesan' => 'Terlalu banyak NISN sekaligus. Maksimal 300 NISN.'], 422);
    }

    $tidakDitemukan = [];
    $siswa = $this->siswaSasaranValidasi(
        $mode,
        $daftarNisn,
        $request->query('semua_periode') === '1',
        $tidakDitemukan
    );

    $ringkasan = ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'belum' => 0, 'siswa' => 0];

    if ($siswa->isEmpty()) {
        return response()->json([
            'ok'              => true,
            'jumlah_siswa'    => 0,
            'daftar'          => [],
            'ringkasan'       => $ringkasan,
            'tidak_ditemukan' => $tidakDitemukan,
        ]);
    }

    $query = Jurnal::query()->whereIn('siswa_id', $siswa->pluck('id'));
    $this->filterTanggalValidasi($query, $jenis, $tanggal, $mulai, $selesai, 'hari_tanggal');

    $rekap = $query->selectRaw('siswa_id, status, COUNT(*) as jumlah')
        ->groupBy('siswa_id', 'status')
        ->get();

    // Status jurnal lama bisa NULL / nilai tak dikenal: dihitung sebagai draft.
    $peta = [];
    foreach ($rekap as $row) {
        $st = in_array($row->status, ['draft', 'diajukan', 'disetujui'], true) ? $row->status : 'draft';

        $peta[$row->siswa_id][$st] = (int) ($peta[$row->siswa_id][$st] ?? 0) + (int) $row->jumlah;
    }

    $daftar = [];

    foreach ($siswa as $s) {
        $draft     = (int) ($peta[$s->id]['draft'] ?? 0);
        $diajukan  = (int) ($peta[$s->id]['diajukan'] ?? 0);
        $disetujui = (int) ($peta[$s->id]['disetujui'] ?? 0);
        $total     = $draft + $diajukan + $disetujui;

        $ringkasan['total']     += $total;
        $ringkasan['draft']     += $draft;
        $ringkasan['diajukan']  += $diajukan;
        $ringkasan['disetujui'] += $disetujui;

        if ($total > 0) {
            $ringkasan['siswa']++;
        }

        $daftar[] = [
            'nisn'       => (string) $s->nisn,
            'name'       => $s->name,
            'kelas'      => $s->kelas ?? '-',
            'status_pkl' => $s->status_pkl ?? '-',
            'total'      => $total,
            'draft'      => $draft,
            'diajukan'   => $diajukan,
            'disetujui'  => $disetujui,
            'belum'      => $draft + $diajukan,
        ];
    }

    $ringkasan['belum'] = $ringkasan['draft'] + $ringkasan['diajukan'];

    return response()->json([
        'ok'              => true,
        'jumlah_siswa'    => $siswa->count(),
        // Mode "semua" bisa berisi ratusan siswa: kirim contoh 50 teratas saja.
        'daftar'          => $mode === 'semua' ? array_slice($daftar, 0, 50) : $daftar,
        'ringkasan'       => $ringkasan,
        'tidak_ditemukan' => $tidakDitemukan,
    ]);
}

/**
 * VALIDASI JURNAL MASSAL (fitur tersendiri di halaman monitoring jurnal).
 *
 * Lingkup:
 *  - mode 'nisn'  : BEBERAPA NISN sekaligus (dipisah koma/spasi/baris baru)
 *  - mode 'semua' : seluruh siswa PKL periode berjalan (opsional semua periode)
 *
 * Filter tanggal:
 *  - 'tanggal' : hanya satu hari tertentu
 *  - 'rentang' : rentang tanggal tertentu
 *  - 'semua'   : seluruh riwayat jurnal
 *
 * Aksi:
 *  - 'setujui'  : status -> disetujui (tervalidasi)
 *  - 'batalkan' : status -> draft (validasi dilepas)
 *
 * Fitur ini TIDAK menyentuh isi jurnal: unit kerja, dokumentasi, foto bukti,
 * maupun catatan instruktur dibiarkan apa adanya.
 */
public function validasiJurnal(Request $request)
{
    $data = $request->validate([
        'mode'            => ['required', Rule::in(['nisn', 'semua'])],
        'nisn'            => ['required_if:mode,nisn', 'nullable', 'string', 'max:5000'],
        'jenis_tanggal'   => ['required', Rule::in(['tanggal', 'rentang', 'semua'])],
        'tanggal'         => ['required_if:jenis_tanggal,tanggal', 'nullable', 'date'],
        'tanggal_mulai'   => ['required_if:jenis_tanggal,rentang', 'nullable', 'date'],
        'tanggal_selesai' => ['required_if:jenis_tanggal,rentang', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
        'aksi'            => ['required', Rule::in(['setujui', 'batalkan'])],
        // Sumber = status jurnal yang ikut diproses saat menyetujui.
        'sumber'          => ['nullable', Rule::in(['', 'semua', 'draft', 'diajukan'])],
        'semua_periode'   => ['nullable', 'boolean'],
    ], [
        'nisn.required_if'               => 'NISN wajib diisi untuk validasi per siswa (boleh lebih dari satu).',
        'tanggal.required_if'            => 'Tanggal wajib diisi bila memvalidasi hari tertentu.',
        'tanggal_mulai.required_if'      => 'Tanggal mulai wajib diisi bila memakai rentang tanggal.',
        'tanggal_selesai.required_if'    => 'Tanggal selesai wajib diisi bila memakai rentang tanggal.',
        'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
    ]);

    $mode   = $data['mode'];
    $jenis  = $data['jenis_tanggal'];
    $aksi   = $data['aksi'];
    $sumber = (string) ($data['sumber'] ?? 'semua');

    $tidakDitemukan = [];
    $siswa = $this->siswaSasaranValidasi(
        $mode,
        $data['nisn'] ?? '',
        $request->boolean('semua_periode'),
        $tidakDitemukan
    );

    if ($siswa->isEmpty()) {
        return back()->with('error', $mode === 'nisn'
            ? ('Tidak ada NISN yang cocok dengan data siswa PKL.'
                . (empty($tidakDitemukan) ? '' : ' NISN yang dicari: ' . implode(', ', array_slice($tidakDitemukan, 0, 10)) . '.'))
            : 'Tidak ada siswa PKL pada periode berjalan.');
    }

    $query = Jurnal::query()->whereIn('siswa_id', $siswa->pluck('id'));
    $this->filterTanggalValidasi(
        $query,
        $jenis,
        $data['tanggal'] ?? null,
        $data['tanggal_mulai'] ?? null,
        $data['tanggal_selesai'] ?? null,
        'hari_tanggal'
    );

    if ($aksi === 'setujui') {
        // Hanya jurnal yang BELUM disetujui yang perlu diproses.
        if (in_array($sumber, ['draft', 'diajukan'], true)) {
            $query->where('status', $sumber);
        } else {
            $query->where(fn ($q) => $q->where('status', '!=', 'disetujui')
                                       ->orWhereNull('status'));
        }
    } else {
        $query->where('status', 'disetujui');
    }

    $baris = $query->get(['id', 'siswa_id']);

    if ($baris->isEmpty()) {
        return back()->with('error', $aksi === 'setujui'
            ? 'Tidak ada jurnal yang perlu divalidasi pada filter tersebut (semuanya sudah tervalidasi atau datanya belum ada).'
            : 'Tidak ada jurnal tervalidasi yang bisa dibatalkan pada filter tersebut.');
    }

    // Kolom yang disentuh sama dengan validasi guru (JurnalController@validasiByGuru).
    $isi = $aksi === 'setujui'
        ? ['status' => 'disetujui', 'validated_by_guru_id' => Auth::id(), 'validated_at' => now()]
        : ['status' => 'draft', 'validated_by_guru_id' => null, 'validated_at' => null];

    DB::transaction(function () use ($baris, $isi) {
        foreach ($baris->pluck('id')->chunk(500) as $potongan) {
            Jurnal::whereIn('id', $potongan->all())->update($isi);
        }
    });

    $totalBaris     = $baris->count();
    $siswaTerdampak = $baris->pluck('siswa_id')->unique()->count();

    $cakupan = match ($jenis) {
        'tanggal' => 'tanggal ' . Carbon::parse($data['tanggal'])->format('d/m/Y'),
        'rentang' => 'rentang ' . Carbon::parse($data['tanggal_mulai'])->format('d/m/Y')
            . ' - ' . Carbon::parse($data['tanggal_selesai'])->format('d/m/Y'),
        default   => 'seluruh riwayat jurnal',
    };

    $siswaPertama = $siswa->firstWhere('id', $baris->first()->siswa_id);

    $lingkup = $siswaTerdampak === 1
        ? ('siswa ' . $siswaPertama?->name . ' (NISN ' . $siswaPertama?->nisn . ')')
        : ($siswaTerdampak . ' siswa');

    $pesan = $aksi === 'setujui'
        ? "Validasi jurnal berhasil: {$totalBaris} jurnal milik {$lingkup} kini TERVALIDASI (disetujui) pada {$cakupan}."
        : "Validasi dibatalkan: {$totalBaris} jurnal milik {$lingkup} dikembalikan ke draft pada {$cakupan}.";

    if ($aksi === 'setujui' && in_array($sumber, ['draft', 'diajukan'], true)) {
        $pesan .= ' Hanya jurnal berstatus ' . $sumber . ' yang diproses.';
    }

    $pesan .= ' Isi jurnal, dokumentasi, foto bukti, dan catatan instruktur tidak diubah.';

    if (! empty($tidakDitemukan)) {
        $contoh = implode(', ', array_slice($tidakDitemukan, 0, 5));
        $sisa   = count($tidakDitemukan) - 5;
        $pesan .= ' NISN tidak ditemukan (' . count($tidakDitemukan) . '): ' . $contoh . ($sisa > 0 ? " dan {$sisa} lainnya." : '.');
    }

    return back()->with('success', $pesan);
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
        // Tanda tangan digital instruktur (opsional), PNG base64 dari kanvas.
        'ttd_instruktur'       => ['nullable', 'string'],
    ]);

    // Nilai mentah kanvas tidak boleh langsung masuk basis data.
    unset($data['ttd_instruktur']);

    if ($request->hasFile('foto_bukti')) {
        $data['foto_bukti'] = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/catatan');
    }

    $ttdPath = TandaTangan::simpan($request->input('ttd_instruktur'), 'ttd/catatan');
    if ($ttdPath) {
        $data['ttd_instruktur']      = $ttdPath;
        $data['ttd_instruktur_nama'] = $this->namaInstrukturSiswa($data['user_id']);
        $data['ttd_signed_at']       = now();
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
        'ttd_instruktur'       => ['nullable', 'string'],
        'hapus_ttd_instruktur' => ['nullable', 'boolean'],
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

    // ---- Paraf digital instruktur (opsional) ----
    $ttdPath  = $catatan->ttd_instruktur;
    $ttdNama  = $catatan->ttd_instruktur_nama;
    $ttdWaktu = $catatan->ttd_signed_at;

    if ($request->boolean('hapus_ttd_instruktur') && $ttdPath) {
        TandaTangan::hapus($ttdPath);
        $ttdPath  = null;
        $ttdNama  = null;
        $ttdWaktu = null;
    }

    $ttdBaru = TandaTangan::simpan($request->input('ttd_instruktur'), 'ttd/catatan');
    if ($ttdBaru) {
        TandaTangan::hapus($ttdPath);   // ganti paraf lama bila ada
        $ttdPath  = $ttdBaru;
        $ttdNama  = $this->namaInstrukturSiswa($catatan->user_id);
        $ttdWaktu = now();
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
        'ttd_instruktur'       => $ttdPath,
        'ttd_instruktur_nama'  => $ttdNama,
        'ttd_signed_at'        => $ttdWaktu,
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
    TandaTangan::hapus($catatan->ttd_instruktur);
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
    // Filter status validasi: draft / diajukan (menunggu) / disetujui / belum.
    $statusValidasi = $this->statusValidasiValid($request->get('status_validasi', ''));

    $absensi = Absensi::query()
        ->with('siswa')
        ->whereHas('siswa', fn ($s) => $s->siswa()->withoutTrashed())
        // Filter STATUS VALIDASI (draft / menunggu divalidasi / tervalidasi).
        ->when($statusValidasi, fn ($query) => $this->filterStatusValidasi($query, $statusValidasi))
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
    $hariKerjaGlobal   = User::hariKerjaGlobal();
    $pengaturanAbsensi = [
        'jam_masuk'    => Pengaturan::ambil('absensi_jam_masuk', '08:00'),
        'jam_pulang'   => Pengaturan::ambil('absensi_jam_pulang', '16:00'),
        'durasi_menit' => (int) Pengaturan::ambil('absensi_durasi_menit', 30),
        // Jadwal hari kerja global: senin_jumat (bawaan) | senin_sabtu | senin_minggu.
        'hari_kerja'            => $hariKerjaGlobal,
        'hari_kerja_label'      => User::labelJadwal($hariKerjaGlobal),
        'hari_kerja_keterangan' => User::keteranganJadwal($hariKerjaGlobal),
    ];

    // BERSIH-BERSIH OTOMATIS: pembukaan absensi yang batas waktunya sudah lewat
    // dimatikan di sini, baik yang global (tabel pengaturans) maupun per-siswa
    // (kolom users.absensi_dibuka_sampai). Karena dijalankan saat halaman ini
    // dimuat, absensi menutup sendiri tanpa perlu cron/scheduler dan tanpa
    // admin harus menekan tombol "Tutup".
    Pengaturan::bersihkanPaksaKedaluwarsa();
    User::tutupBukaKedaluwarsa();

    // Status buka-paksa absensi global, kini TERPISAH untuk fase masuk & pulang.
    // Flag lama absensi_paksa_buka (bila masih '1') dianggap membuka keduanya.
    $legacyGlobal = Pengaturan::ambil('absensi_paksa_buka', '0') === '1';
    $paksaMasuk   = $legacyGlobal || Pengaturan::paksaBukaAktif('masuk');
    $paksaPulang  = $legacyGlobal || Pengaturan::paksaBukaAktif('pulang');
    $paksaBuka    = $paksaMasuk || $paksaPulang;

    // Sisa waktu buka-paksa global per fase untuk ditampilkan di modal & banner.
    // Nilai '' berarti fase itu tidak sedang dibuka-paksa lewat tenggat.
    $paksaSisa = [
        'masuk'  => Pengaturan::labelSisaPaksa('masuk'),
        'pulang' => Pengaturan::labelSisaPaksa('pulang'),
    ];

    // Siswa yang absensinya dibuka manual per-orang (di luar buka global).
    // Lintas periode: siswa angkatan lama yang absensinya masih dibuka manual
    // harus tetap terlihat agar admin bisa menutupnya kembali.
    $dibukaList = User::siswa()->withoutTrashed()
        ->where(fn ($q) => $q->where('absensi_dibuka', true)
            ->orWhere('absensi_dibuka_masuk', true)
            ->orWhere('absensi_dibuka_pulang', true))
        ->orderBy('name')
        ->get(['id', 'name', 'nisn', 'absensi_dibuka', 'absensi_dibuka_masuk', 'absensi_dibuka_pulang', 'absensi_dibuka_sampai']);

    // Data jam kerja industri per siswa (untuk pencarian & edit via NISN oleh admin).
    $siswaJam = User::siswa()->withoutTrashed()
        ->orderByRaw("CASE status_pkl WHEN 'aktif' THEN 1 WHEN 'belum' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END")
        ->orderBy('name')
        ->get(['id', 'name', 'nisn', 'kelas', 'status_pkl', 'jam_masuk_industri', 'jam_pulang_industri', 'jam_masuk_usulan', 'jam_pulang_usulan', 'status_jam_usulan', 'catatan_jam_usulan', 'hari_kerja']);

    // Pengajuan jam yang masih menunggu validasi admin.
    $usulanJam = $siswaJam->where('status_jam_usulan', 'diajukan')->values();

    // Data JADWAL HARI KERJA per siswa untuk modal "Jadwal Absensi".
    // Dipakai untuk mencocokkan NISN yang ditempel admin (boleh banyak sekaligus)
    // dan menampilkan jadwal yang sedang berlaku bagi tiap siswa.
    $siswaJadwal = $siswaJam->map(fn ($s) => [
        'nisn'       => (string) $s->nisn,
        'name'       => $s->name,
        'kelas'      => $s->kelas ?? '-',
        'status_pkl' => $s->status_pkl ?? '-',
        'hari_kerja' => (string) ($s->hari_kerja ?? ''),
        'label'      => $s->labelHariKerja(),
        'khusus'     => $s->pakaiHariKerjaKhusus(),
    ])->values();

    // Siswa yang memakai jadwal KHUSUS (pengecualian dari jadwal global).
    $jadwalKhususList = $siswaJadwal->where('khusus', true)->values();

    // Jam global admin sebagai acuan tampilan.
    $jamAdmin = [
        'masuk'  => $pengaturanAbsensi['jam_masuk'],
        'pulang' => $pengaturanAbsensi['jam_pulang'],
    ];

    return view('admin.monitoring.absensi', array_merge(
        compact('absensi', 'q', 'status', 'statusPkl', 'statusValidasi', 'tanggal', 'bulan', 'kelas', 'jurusan', 'rekap', 'tanggalDefault', 'pengaturanAbsensi', 'paksaBuka', 'paksaMasuk', 'paksaPulang', 'paksaSisa', 'siswaJam', 'usulanJam', 'jamAdmin', 'siswaJadwal', 'jadwalKhususList'),
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
        // Jadwal hari kerja GLOBAL. Siswa yang jadwalnya berbeda tetap bisa
        // diatur satu per satu lewat pencarian NISN (kolom users.hari_kerja).
        'absensi_hari_kerja'   => ['nullable', Rule::in(User::daftarHariKerja())],
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

    $hariKerja = $data['absensi_hari_kerja'] ?? User::HARI_KERJA_SENIN_JUMAT;
    Pengaturan::simpan('absensi_hari_kerja', $hariKerja);
    $this->lupakanCacheAbsensi();

    return back()->with('success', 'Pengaturan absensi berhasil disimpan. Jadwal hari kerja: '
        . User::labelJadwal($hariKerja) . ' (' . User::keteranganJadwal($hariKerja) . ').');
}

/**
 * ATUR JADWAL HARI KERJA ABSENSI
 * -----------------------------------------------------------------------
 * Dua mode:
 *  - mode "semua" : mengubah jadwal GLOBAL sekolah, tersimpan pada tabel
 *                   `pengaturans` (kunci: absensi_hari_kerja).
 *  - mode "nisn"  : mengubah jadwal KHUSUS milik BEBERAPA siswa sekaligus
 *                   (kolom users.hari_kerja). NISN boleh ditempel banyak,
 *                   dipisah koma / titik koma / spasi / baris baru.
 *
 * Pilihan jadwal: senin_jumat, senin_sabtu, senin_minggu.
 * Khusus mode "nisn", nilai kosong berarti HAPUS jadwal khusus sehingga
 * siswa tersebut kembali mengikuti jadwal global.
 */
public function jadwalAbsensi(Request $request)
{
    $data = $request->validate([
        'mode'         => ['required', Rule::in(['semua', 'nisn'])],
        'nisn'         => ['required_if:mode,nisn', 'nullable', 'string', 'max:5000'],
        // '' hanya sah pada mode nisn (artinya: ikut jadwal global).
        'hari_kerja'   => ['nullable', 'string', Rule::in(array_merge([''], User::daftarHariKerja()))],
        'hapus_khusus' => ['nullable', 'boolean'],
    ], [
        'nisn.required_if' => 'NISN wajib diisi untuk mode per NISN.',
        'hari_kerja.in'    => 'Pilihan jadwal hari kerja tidak dikenali.',
    ]);

    $jadwal = (string) ($data['hari_kerja'] ?? '');

    // ================= MODE SELURUH SISWA (jadwal global) =================
    if ($data['mode'] === 'semua') {
        if ($jadwal === '') {
            return back()->with('error', 'Pilih salah satu jadwal hari kerja terlebih dahulu.');
        }

        Pengaturan::simpan('absensi_hari_kerja', $jadwal);

        $pesan = 'Jadwal absensi SELURUH siswa disetel ke ' . User::labelJadwal($jadwal)
            . ' (' . User::keteranganJadwal($jadwal) . ').';

        if ($request->boolean('hapus_khusus')) {
            // Samakan semua siswa: jadwal khusus per orang dibuang.
            $jumlah = User::siswa()->withoutTrashed()
                ->whereNotNull('hari_kerja')
                ->update(['hari_kerja' => null]);

            $pesan .= ' ' . $jumlah . ' jadwal khusus per siswa ikut dihapus, jadi semua siswa memakai jadwal ini.';
        } else {
            $sisa = User::siswa()->withoutTrashed()->whereNotNull('hari_kerja')->count();

            if ($sisa > 0) {
                $pesan .= ' Catatan: ' . $sisa . ' siswa masih memakai jadwal khusus sendiri dan TIDAK terpengaruh.';
            }
        }

        $this->lupakanCacheAbsensi();

        return back()->with('success', $pesan);
    }

    // ============== MODE PER NISN (boleh banyak sekaligus) ===============
    $daftarNisn = $this->pecahDaftar($data['nisn'] ?? '');

    if (empty($daftarNisn)) {
        return back()->with('error', 'Masukkan minimal satu NISN.');
    }

    if (count($daftarNisn) > 300) {
        return back()->with('error', 'Terlalu banyak NISN sekaligus. Maksimal 300 NISN per proses.');
    }

    $sasaran = User::siswa()->withoutTrashed()
        ->whereIn('nisn', $daftarNisn)
        ->orderBy('name')
        ->get();

    $tidakDitemukan = array_values(array_diff(
        $daftarNisn,
        $sasaran->pluck('nisn')->map(fn ($n) => (string) $n)->all()
    ));

    if ($sasaran->isEmpty()) {
        return back()->with('error', 'Tidak ada siswa yang cocok dengan NISN: '
            . implode(', ', array_slice($daftarNisn, 0, 10))
            . (count($daftarNisn) > 10 ? ' ...' : '') . '.');
    }

    // Kosong = hapus jadwal khusus (kembali ikut jadwal global).
    $nilaiBaru = $jadwal === '' ? null : $jadwal;
    $diubah    = 0;

    foreach ($sasaran as $siswaSasaran) {
        if ((string) ($siswaSasaran->hari_kerja ?? '') !== (string) ($nilaiBaru ?? '')) {
            $siswaSasaran->hari_kerja = $nilaiBaru;
            $siswaSasaran->save();
            $diubah++;
        }
    }

    $pesan = $nilaiBaru === null
        ? 'Jadwal khusus dihapus untuk ' . $sasaran->count() . ' siswa; sekarang mengikuti jadwal global ('
            . User::labelJadwal(User::hariKerjaGlobal()) . ').'
        : 'Jadwal ' . User::labelJadwal($nilaiBaru) . ' (' . User::keteranganJadwal($nilaiBaru)
            . ') disimpan untuk ' . $sasaran->count() . ' siswa: '
            . $sasaran->take(8)->pluck('name')->implode(', ')
            . ($sasaran->count() > 8 ? ', dan ' . ($sasaran->count() - 8) . ' lainnya' : '') . '.';

    if ($diubah === 0) {
        $pesan .= ' Tidak ada perubahan karena jadwalnya memang sudah sama.';
    }

    if (! empty($tidakDitemukan)) {
        $pesan .= ' NISN tidak ditemukan: ' . implode(', ', array_slice($tidakDitemukan, 0, 15))
            . (count($tidakDitemukan) > 15 ? ' ...' : '') . '.';
    }

    $this->lupakanCacheAbsensi($sasaran->pluck('id')->all());

    return back()->with('success', $pesan);
}

/**
 * Bersihkan guard cache sinkronisasi Alpha agar jadwal yang baru langsung
 * berlaku, tanpa menunggu cache harian kedaluwarsa sendiri.
 *
 * @param  array<int>|null  $siswaIds  null = seluruh siswa
 */
private function lupakanCacheAbsensi(?array $siswaIds = null): void
{
    $tanggal = now()->format('Y-m-d');

    $ids = $siswaIds !== null
        ? $siswaIds
        : User::siswa()->withoutTrashed()->pluck('id')->all();

    foreach ($ids as $id) {
        Cache::forget("sinkron_alpa:{$id}:{$tanggal}");
    }
}

/**
 * Admin membuka / menutup absensi tanpa mengikuti jadwal jam.
 *  - mode "semua" : buka/tutup untuk SEMUA siswa (flag global absensi_paksa_buka).
 *  - mode "nisn"  : buka/tutup untuk SATU siswa (dicocokkan berdasarkan NISN).
 *  - aksi "buka"  : absensi terbuka di luar jadwal; "tutup" : kembali ikut jadwal.
 *
 * Pembukaan diberi BATAS WAKTU (default 30 menit) sehingga absensi menutup
 * sendiri dan admin tidak perlu menekan "Tutup" lagi. Centang "tanpa batas"
 * untuk memakai perilaku lama.
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

    // LAMA PEMBUKAAN. Nilai di luar jangkauan dikembalikan ke bawaan agar aksi
    // admin tidak pernah gagal hanya karena angka menit yang aneh.
    $data = $request->validate([
        'durasi_menit' => ['nullable', 'integer', 'min:1', 'max:' . Pengaturan::PAKSA_MENIT_MAKS],
        'tanpa_batas'  => ['nullable', 'boolean'],
    ], [
        'durasi_menit.integer' => 'Lama absensi dibuka harus berupa angka menit.',
        'durasi_menit.min'     => 'Lama absensi dibuka minimal 1 menit.',
        'durasi_menit.max'     => 'Lama absensi dibuka maksimal ' . Pengaturan::PAKSA_MENIT_MAKS . ' menit (24 jam).',
    ]);

    $tanpaBatas = $request->boolean('tanpa_batas');
    $durasi     = (int) ($data['durasi_menit'] ?? Pengaturan::PAKSA_MENIT_DEFAULT);

    if ($durasi < 1 || $durasi > Pengaturan::PAKSA_MENIT_MAKS) {
        $durasi = Pengaturan::PAKSA_MENIT_DEFAULT;
    }

    // null = tanpa batas waktu. Aksi "tutup" tidak pernah memasang tenggat.
    $sampai = ($buka && ! $tanpaBatas) ? now()->addMinutes($durasi) : null;

    $ket = $sampai
        ? "selama {$durasi} menit (otomatis tertutup pukul {$sampai->format('H:i')} WITA)"
        : 'tanpa batas waktu (perlu ditutup manual)';

    if ($mode === 'semua') {
        // aturPaksa() sekaligus menyimpan / membersihkan batas waktunya.
        if ($kenaMasuk) {
            Pengaturan::aturPaksa('masuk', $buka, $sampai);
        }
        if ($kenaPulang) {
            Pengaturan::aturPaksa('pulang', $buka, $sampai);
        }

        // Flag lama (membuka kedua fase sekaligus) tidak dipakai lagi; matikan
        // agar tidak "mengunci" kedua fase tetap terbuka.
        Pengaturan::simpan('absensi_paksa_buka', '0');

        // Saat menutup SEMUA, kembalikan juga pembukaan per-siswa ke jadwal
        // sekaligus menghapus tenggatnya supaya tidak tertinggal.
        if (! $buka && $target === 'semua') {
            // SENGAJA tetap dibatasi periode berjalan: ini operasi tulis massal.
            User::siswaBerjalan()->update([
                'absensi_dibuka'        => false,
                'absensi_dibuka_masuk'  => false,
                'absensi_dibuka_pulang' => false,
                'absensi_dibuka_sampai' => null,
            ]);
        }

        return back()->with('success', $buka
            ? "{$labelTarget} DIBUKA untuk semua siswa {$ket}. Fase lain tetap mengikuti jadwal."
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

    // Tenggat dipakai bersama oleh seluruh fase milik siswa ini. Saat menutup,
    // tenggat baru dihapus kalau memang tidak ada fase lain yang masih terbuka.
    if ($buka) {
        $siswa->absensi_dibuka_sampai = $sampai;
    } elseif (! $siswa->absensi_dibuka_masuk && ! $siswa->absensi_dibuka_pulang) {
        $siswa->absensi_dibuka_sampai = null;
    }

    $siswa->save();

    return back()->with('success', $buka
        ? "{$labelTarget} untuk {$siswa->name} (NISN {$nisn}) DIBUKA {$ket}."
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
 * INFORMASI ABSENSI SATU SISWA (dipanggil lewat AJAX dari modal admin).
 *
 * Admin mengetik NISN -> layar langsung menampilkan jumlah Hadir, Izin,
 * Sakit, dan Alpha siswa tersebut sehingga bisa langsung diedit.
 *
 * Mengembalikan dua kelompok angka:
 *  - "total"  : seluruh riwayat absensi siswa (tanpa batas tanggal)
 *  - "rentang": hanya di dalam rentang tanggal yang sedang dipilih admin
 */
public function rekapSiswa(Request $request)
{
    $nisn = trim((string) $request->query('nisn', ''));

    if ($nisn === '') {
        return response()->json(['ok' => false, 'pesan' => 'NISN wajib diisi.'], 422);
    }

    $siswa = User::siswa()->withoutTrashed()->where('nisn', $nisn)->first();

    if (! $siswa) {
        return response()->json(['ok' => false, 'pesan' => "Siswa dengan NISN {$nisn} tidak ditemukan."], 404);
    }

    $hitung = function ($query) {
        return [
            'Hadir' => (clone $query)->where('status', 'Hadir')->count(),
            'Izin'  => (clone $query)->where('status', 'Izin')->count(),
            'Sakit' => (clone $query)->where('status', 'Sakit')->count(),
            'Alpha' => (clone $query)->where('status', 'Alpha')->count(),
        ];
    };

    $semua = Absensi::where('siswa_id', $siswa->id);
    $total = $hitung($semua);

    // Rekap di dalam rentang tanggal (bila admin sudah mengisinya).
    $rentang = null;
    $mulai   = $request->query('tanggal_mulai');
    $selesai = $request->query('tanggal_selesai');

    if (filled($mulai) && filled($selesai)) {
        try {
            $m = Carbon::parse($mulai)->format('Y-m-d');
            $s = Carbon::parse($selesai)->format('Y-m-d');

            $q = Absensi::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', '>=', $m)
                ->whereDate('tanggal', '<=', $s);

            $rentang = $hitung($q);
        } catch (\Throwable $e) {
            $rentang = null; // tanggal tidak valid: cukup abaikan bagian ini.
        }
    }

    return response()->json([
        'ok'    => true,
        'siswa' => [
            'nisn'       => $siswa->nisn,
            'name'       => $siswa->name,
            'kelas'      => $siswa->kelas ?? '-',
            'jurusan'    => $siswa->jurusan ?? '-',
            'status_pkl' => $siswa->status_pkl ?? '-',
            // Jadwal hari kerja: khusus siswa ini bila diatur, selain itu global.
            'hari_kerja'        => $siswa->hari_kerja ?? '',
            'hari_kerja_efektif'=> $siswa->hariKerjaEfektif(),
            'hari_kerja_label'  => $siswa->labelHariKerja(),
            'hari_kerja_khusus' => $siswa->pakaiHariKerjaKhusus(),
        ],
        'total'        => $total,
        'total_baris'  => array_sum($total),
        'rentang'      => $rentang,
    ]);
}

/**
 * Pecah masukan daftar (NISN / status) menjadi array bersih & unik.
 *
 * Admin bebas memisahkan dengan koma, titik koma, spasi, atau baris baru,
 * sehingga daftar NISN dari Excel bisa ditempel apa adanya.
 */
private function pecahDaftar($nilai): array
{
    if (is_array($nilai)) {
        $nilai = implode(',', $nilai);
    }

    $pecah  = preg_split('/[\s,;]+/', (string) $nilai) ?: [];
    $bersih = [];

    foreach ($pecah as $item) {
        $item = trim($item);

        if ($item !== '' && ! in_array($item, $bersih, true)) {
            $bersih[] = $item;
        }
    }

    return $bersih;
}

/**
 * Filter tanggal untuk fitur VALIDASI MASSAL (absensi maupun jurnal).
 *  - 'tanggal' : hanya SATU hari tertentu
 *  - 'rentang' : dari tanggal mulai s.d. tanggal selesai
 *  - 'semua'   : seluruh riwayat (tanpa batas tanggal)
 *
 * $kolom dibuat bisa diganti karena nama kolom tanggalnya berbeda antar tabel:
 * absensi memakai 'tanggal', jurnal memakai 'hari_tanggal'.
 */
private function filterTanggalValidasi($query, string $jenis, ?string $tanggal, ?string $mulai, ?string $selesai, string $kolom = 'tanggal')
{
    if ($jenis === 'tanggal' && filled($tanggal)) {
        $query->whereDate($kolom, Carbon::parse($tanggal)->format('Y-m-d'));
    } elseif ($jenis === 'rentang' && filled($mulai) && filled($selesai)) {
        $query->whereDate($kolom, '>=', Carbon::parse($mulai)->format('Y-m-d'))
              ->whereDate($kolom, '<=', Carbon::parse($selesai)->format('Y-m-d'));
    }

    return $query;
}

/**
 * Kumpulkan siswa sasaran untuk fitur VALIDASI ABSENSI.
 *
 * Mode 'nisn' menerima BANYAK NISN sekaligus. NISN yang tidak ditemukan
 * dikembalikan lewat $tidakDitemukan agar bisa dilaporkan ke admin.
 */
private function siswaSasaranValidasi(string $mode, $nisnMentah, bool $semuaPeriode, array &$tidakDitemukan)
{
    $tidakDitemukan = [];

    if ($mode === 'nisn') {
        $daftarNisn = $this->pecahDaftar($nisnMentah);

        if (empty($daftarNisn)) {
            return collect();
        }

        $siswa = User::siswa()->withoutTrashed()
            ->whereIn('nisn', $daftarNisn)
            ->orderBy('name')
            ->get(['id', 'name', 'nisn', 'kelas', 'jurusan', 'status_pkl']);

        $ditemukan      = $siswa->pluck('nisn')->map(fn ($n) => (string) $n)->all();
        $tidakDitemukan = array_values(array_diff($daftarNisn, $ditemukan));

        return $siswa;
    }

    // Mode 'semua'. Bawaannya hanya siswa periode BERJALAN supaya arsip
    // angkatan lama tidak ikut tersentuh; admin bisa melebarkannya sendiri.
    $query = $semuaPeriode ? User::siswa()->withoutTrashed() : User::siswaBerjalan();

    return $query->orderBy('name')->get(['id', 'name', 'nisn', 'kelas', 'jurusan', 'status_pkl']);
}

/**
 * PRATINJAU VALIDASI ABSENSI (JSON, dipanggil modal admin).
 *
 * Menampilkan berapa baris absensi yang akan terkena aksi validasi untuk
 * filter yang sedang dipilih: per beberapa NISN atau semua siswa, pada satu
 * hari tertentu / rentang tanggal / seluruh riwayat.
 */
public function pratinjauValidasi(Request $request)
{
    $mode  = $request->query('mode') === 'semua' ? 'semua' : 'nisn';
    $jenis = in_array($request->query('jenis_tanggal'), ['tanggal', 'rentang', 'semua'], true)
        ? (string) $request->query('jenis_tanggal')
        : 'rentang';

    $tanggal = $request->query('tanggal');
    $mulai   = $request->query('tanggal_mulai');
    $selesai = $request->query('tanggal_selesai');

    $statusFilter = array_values(array_intersect(
        $this->pecahDaftar($request->query('status', '')),
        ['Hadir', 'Izin', 'Sakit', 'Alpha']
    ));

    $daftarNisn = $this->pecahDaftar($request->query('nisn', ''));

    if ($mode === 'nisn' && empty($daftarNisn)) {
        return response()->json(['ok' => false, 'pesan' => 'Masukkan minimal satu NISN.'], 422);
    }

    if (count($daftarNisn) > 300) {
        return response()->json(['ok' => false, 'pesan' => 'Terlalu banyak NISN sekaligus. Maksimal 300 NISN.'], 422);
    }

    $tidakDitemukan = [];
    $siswa = $this->siswaSasaranValidasi(
        $mode,
        $daftarNisn,
        $request->query('semua_periode') === '1',
        $tidakDitemukan
    );

    $ringkasan = ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'disetujui' => 0, 'belum' => 0, 'siswa' => 0];

    if ($siswa->isEmpty()) {
        return response()->json([
            'ok'              => true,
            'jumlah_siswa'    => 0,
            'daftar'          => [],
            'ringkasan'       => $ringkasan,
            'tidak_ditemukan' => $tidakDitemukan,
        ]);
    }

    $query = Absensi::query()->whereIn('siswa_id', $siswa->pluck('id'));
    $this->filterTanggalValidasi($query, $jenis, $tanggal, $mulai, $selesai);

    if (! empty($statusFilter)) {
        $query->whereIn('status', $statusFilter);
    }

    $rekap = $query->selectRaw('siswa_id, status_validasi, COUNT(*) as jumlah')
        ->groupBy('siswa_id', 'status_validasi')
        ->get();

    $peta = [];
    foreach ($rekap as $row) {
        $sv = in_array($row->status_validasi, ['draft', 'diajukan', 'disetujui'], true)
            ? $row->status_validasi
            : 'draft';

        $peta[$row->siswa_id][$sv] = (int) $row->jumlah;
    }

    $daftar = [];

    foreach ($siswa as $s) {
        $draft     = (int) ($peta[$s->id]['draft'] ?? 0);
        $diajukan  = (int) ($peta[$s->id]['diajukan'] ?? 0);
        $disetujui = (int) ($peta[$s->id]['disetujui'] ?? 0);
        $total     = $draft + $diajukan + $disetujui;

        $ringkasan['total']     += $total;
        $ringkasan['draft']     += $draft;
        $ringkasan['diajukan']  += $diajukan;
        $ringkasan['disetujui'] += $disetujui;

        if ($total > 0) {
            $ringkasan['siswa']++;
        }

        $daftar[] = [
            'nisn'       => (string) $s->nisn,
            'name'       => $s->name,
            'kelas'      => $s->kelas ?? '-',
            'status_pkl' => $s->status_pkl ?? '-',
            'total'      => $total,
            'draft'      => $draft,
            'diajukan'   => $diajukan,
            'disetujui'  => $disetujui,
            'belum'      => $draft + $diajukan,
        ];
    }

    $ringkasan['belum'] = $ringkasan['draft'] + $ringkasan['diajukan'];

    return response()->json([
        'ok'              => true,
        'jumlah_siswa'    => $siswa->count(),
        // Mode "semua" bisa berisi ratusan siswa: kirim contoh 50 teratas saja.
        'daftar'          => $mode === 'semua' ? array_slice($daftar, 0, 50) : $daftar,
        'ringkasan'       => $ringkasan,
        'tidak_ditemukan' => $tidakDitemukan,
    ]);
}

/**
 * VALIDASI ABSENSI MASSAL (fitur terpisah dari "Atur Jumlah").
 *
 * Lingkup:
 *  - mode 'nisn'  : BEBERAPA NISN sekaligus (dipisah koma/spasi/baris baru)
 *  - mode 'semua' : seluruh siswa PKL periode berjalan (opsional semua periode)
 *
 * Filter tanggal:
 *  - 'tanggal' : hanya satu hari tertentu
 *  - 'rentang' : rentang tanggal tertentu
 *  - 'semua'   : seluruh riwayat absensi
 *
 * Aksi:
 *  - 'setujui'  : status_validasi -> disetujui (tervalidasi)
 *  - 'batalkan' : status_validasi -> draft (validasi dilepas)
 *
 * Fitur ini TIDAK menyentuh status kehadiran, jam absen, maupun foto bukti.
 */
public function validasiAbsensi(Request $request)
{
    $data = $request->validate([
        'mode'            => ['required', Rule::in(['nisn', 'semua'])],
        'nisn'            => ['required_if:mode,nisn', 'nullable', 'string', 'max:5000'],
        'jenis_tanggal'   => ['required', Rule::in(['tanggal', 'rentang', 'semua'])],
        'tanggal'         => ['required_if:jenis_tanggal,tanggal', 'nullable', 'date'],
        'tanggal_mulai'   => ['required_if:jenis_tanggal,rentang', 'nullable', 'date'],
        'tanggal_selesai' => ['required_if:jenis_tanggal,rentang', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
        'aksi'            => ['required', Rule::in(['setujui', 'batalkan'])],
        'status'          => ['nullable', 'array'],
        'status.*'        => [Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
        // Sumber = status validasi yang ikut diproses saat menyetujui.
        'sumber'          => ['nullable', Rule::in(['', 'semua', 'draft', 'diajukan'])],
        'semua_periode'   => ['nullable', 'boolean'],
    ], [
        'nisn.required_if'               => 'NISN wajib diisi untuk validasi per siswa (boleh lebih dari satu).',
        'tanggal.required_if'            => 'Tanggal wajib diisi bila memvalidasi hari tertentu.',
        'tanggal_mulai.required_if'      => 'Tanggal mulai wajib diisi bila memakai rentang tanggal.',
        'tanggal_selesai.required_if'    => 'Tanggal selesai wajib diisi bila memakai rentang tanggal.',
        'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
    ]);

    $mode   = $data['mode'];
    $jenis  = $data['jenis_tanggal'];
    $aksi   = $data['aksi'];
    $sumber = (string) ($data['sumber'] ?? 'semua');

    $statusFilter = array_values(array_intersect(
        (array) ($data['status'] ?? []),
        ['Hadir', 'Izin', 'Sakit', 'Alpha']
    ));

    $tidakDitemukan = [];
    $siswa = $this->siswaSasaranValidasi(
        $mode,
        $data['nisn'] ?? '',
        $request->boolean('semua_periode'),
        $tidakDitemukan
    );

    if ($siswa->isEmpty()) {
        return back()->with('error', $mode === 'nisn'
            ? ('Tidak ada NISN yang cocok dengan data siswa PKL.'
                . (empty($tidakDitemukan) ? '' : ' NISN yang dicari: ' . implode(', ', array_slice($tidakDitemukan, 0, 10)) . '.'))
            : 'Tidak ada siswa PKL pada periode berjalan.');
    }

    $query = Absensi::query()->whereIn('siswa_id', $siswa->pluck('id'));
    $this->filterTanggalValidasi(
        $query,
        $jenis,
        $data['tanggal'] ?? null,
        $data['tanggal_mulai'] ?? null,
        $data['tanggal_selesai'] ?? null
    );

    if (! empty($statusFilter)) {
        $query->whereIn('status', $statusFilter);
    }

    if ($aksi === 'setujui') {
        // Hanya baris yang BELUM disetujui yang perlu diproses.
        if (in_array($sumber, ['draft', 'diajukan'], true)) {
            $query->where('status_validasi', $sumber);
        } else {
            $query->where(fn ($q) => $q->where('status_validasi', '!=', 'disetujui')
                                       ->orWhereNull('status_validasi'));
        }
    } else {
        $query->where('status_validasi', 'disetujui');
    }

    $baris = $query->get(['id', 'siswa_id']);

    if ($baris->isEmpty()) {
        return back()->with('error', $aksi === 'setujui'
            ? 'Tidak ada absensi yang perlu divalidasi pada filter tersebut (semuanya sudah tervalidasi atau datanya belum ada).'
            : 'Tidak ada absensi tervalidasi yang bisa dibatalkan pada filter tersebut.');
    }

    $isi = $aksi === 'setujui'
        ? ['status_validasi' => 'disetujui', 'validated_by_guru_id' => Auth::id(), 'validated_at' => now()]
        : ['status_validasi' => 'draft', 'validated_by_guru_id' => null, 'validated_at' => null];

    DB::transaction(function () use ($baris, $isi) {
        foreach ($baris->pluck('id')->chunk(500) as $potongan) {
            Absensi::whereIn('id', $potongan->all())->update($isi);
        }
    });

    $totalBaris     = $baris->count();
    $siswaTerdampak = $baris->pluck('siswa_id')->unique()->count();

    $cakupan = match ($jenis) {
        'tanggal' => 'tanggal ' . Carbon::parse($data['tanggal'])->format('d/m/Y'),
        'rentang' => 'rentang ' . Carbon::parse($data['tanggal_mulai'])->format('d/m/Y')
            . ' - ' . Carbon::parse($data['tanggal_selesai'])->format('d/m/Y'),
        default   => 'seluruh riwayat absensi',
    };

    $lingkup = $siswaTerdampak === 1
        ? ('siswa ' . $siswa->firstWhere('id', $baris->first()->siswa_id)?->name . ' (NISN ' . $siswa->firstWhere('id', $baris->first()->siswa_id)?->nisn . ')')
        : ($siswaTerdampak . ' siswa');

    $pesan = $aksi === 'setujui'
        ? "Validasi absensi berhasil: {$totalBaris} baris milik {$lingkup} kini TERVALIDASI (disetujui) pada {$cakupan}."
        : "Validasi dibatalkan: {$totalBaris} baris milik {$lingkup} dikembalikan ke draft pada {$cakupan}.";

    if (! empty($statusFilter)) {
        $pesan .= ' Hanya status ' . implode(', ', $statusFilter) . ' yang diproses.';
    }

    $pesan .= ' Status kehadiran, jam absen, dan foto bukti tidak diubah.';

    if (! empty($tidakDitemukan)) {
        $contoh = implode(', ', array_slice($tidakDitemukan, 0, 5));
        $sisa   = count($tidakDitemukan) - 5;
        $pesan .= ' NISN tidak ditemukan (' . count($tidakDitemukan) . '): ' . $contoh . ($sisa > 0 ? " dan {$sisa} lainnya." : '.');
    }

    return back()->with('success', $pesan);
}

/**
 * ATUR JUMLAH ABSENSI (Hadir / Izin / Sakit / Alpha) + JADWAL HARI KERJA.
 *
 * CATATAN: fitur ini TIDAK ikut memvalidasi absensi. Status validasi baris
 * lama dibiarkan apa adanya dan baris baru dibuat sebagai 'draft'. Gunakan
 * fitur "Validasi Absensi" untuk menyetujui / membatalkan validasi.
 *
 * Dipakai admin untuk MENIMPA rekap absensi:
 *  - mode "nisn"  : satu ATAU beberapa siswa sekaligus (NISN boleh lebih dari
 *                   satu, dipisah koma / titik koma / spasi / baris baru)
 *  - mode "semua" : seluruh siswa PKL periode berjalan
 *
 * Aturan penting:
 *  1. Jumlah boleh diisi sebagian saja. Mengisi Hadir 20 dan sisanya 0 itu SAH.
 *  2. Mengisi SEMUA jumlah dengan 0 juga SAH: seluruh absensi pada rentang
 *     dihapus sehingga rekap siswa kembali 0. Bila "reset_total" dicentang,
 *     SELURUH riwayat absensi siswa (semua tanggal) yang dihapus.
 *  3. Hari yang BUKAN hari kerja tidak pernah diisi. Jadwal Senin-Jumat berarti
 *     Sabtu & Minggu dilewati: dikosongkan tanpa baris absensi, dan tidak akan
 *     ditandai Alpha otomatis oleh sistem.
 *  4. Pada mode "nisn", jadwal hari kerja siswa itu sendiri bisa diubah
 *     (mis. siswa yang tetap masuk sampai Sabtu). Pada mode "semua", jadwal
 *     yang diubah adalah jadwal GLOBAL sekolah.
 */
public function aturRekapAbsensi(Request $request)
{
    $data = $request->validate([
        'mode'            => ['required', Rule::in(['nisn', 'semua'])],
        // Boleh berisi BANYAK NISN sekaligus (dipisah koma/spasi/baris baru).
        'nisn'            => ['required_if:mode,nisn', 'nullable', 'string', 'max:5000'],
        'tanggal_mulai'   => ['required', 'date'],
        'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        'jumlah_hadir'    => ['required', 'integer', 'min:0', 'max:400'],
        'jumlah_izin'     => ['required', 'integer', 'min:0', 'max:400'],
        'jumlah_sakit'    => ['required', 'integer', 'min:0', 'max:400'],
        'jumlah_alpha'    => ['required', 'integer', 'min:0', 'max:400'],
        'status_sisa'     => ['nullable', Rule::in(['', 'Hadir', 'Izin', 'Sakit', 'Alpha'])],
        // '' = biarkan jadwal apa adanya, selain itu ubah jadwal hari kerja.
        'hari_kerja'      => ['nullable', Rule::in(array_merge([''], User::daftarHariKerja()))],
        'reset_total'     => ['nullable', 'boolean'],
        // Hanya berlaku bila rentang = 1 hari: paksa tanggal itu walau bukan
        // hari kerja (mis. Sabtu/Minggu yang absensinya dibuka admin).
        'paksa_tanggal'   => ['nullable', 'boolean'],
    ], [
        'nisn.required_if'               => 'NISN wajib diisi untuk mode per siswa.',
        'tanggal_mulai.required'         => 'Tanggal mulai wajib diisi.',
        'tanggal_selesai.required'       => 'Tanggal selesai wajib diisi.',
        'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
    ]);

    $mulai   = Carbon::parse($data['tanggal_mulai'])->startOfDay();
    $selesai = Carbon::parse($data['tanggal_selesai'])->startOfDay();

    // Batas wajar 1 tahun: mencegah salah ketik tahun menghasilkan puluhan ribu baris.
    if ($mulai->diffInDays($selesai) > 366) {
        return back()->with('error', 'Rentang tanggal terlalu panjang. Maksimal 1 tahun.');
    }

    $jumlah = [
        'Hadir' => (int) $data['jumlah_hadir'],
        'Izin'  => (int) $data['jumlah_izin'],
        'Sakit' => (int) $data['jumlah_sakit'],
        'Alpha' => (int) $data['jumlah_alpha'],
    ];
    $totalDiminta = array_sum($jumlah);
    $statusSisa   = (string) ($data['status_sisa'] ?? '');
    $resetTotal   = $request->boolean('reset_total');
    $jadwalBaru   = (string) ($data['hari_kerja'] ?? '');

    // Mode SATU HARI: tanggal mulai = tanggal selesai. Berguna untuk mengedit
    // atau membuat absensi khusus satu tanggal saja (mis. hari ini).
    $satuHari     = $mulai->isSameDay($selesai);
    $paksaTanggal = $satuHari && $request->boolean('paksa_tanggal');

    // ---- Tentukan siswa sasaran ----
    $nisnTidakDitemukan = [];

    if ($data['mode'] === 'nisn') {
        // BOLEH LEBIH DARI SATU NISN: dipisah koma, titik koma, spasi, atau
        // baris baru (bisa langsung ditempel dari Excel).
        $daftarNisn = $this->pecahDaftar($data['nisn'] ?? '');

        if (empty($daftarNisn)) {
            return back()->with('error', 'Masukkan minimal satu NISN.');
        }

        if (count($daftarNisn) > 300) {
            return back()->with('error', 'Terlalu banyak NISN sekaligus. Maksimal 300 NISN per proses.');
        }

        $sasaran = User::siswa()->withoutTrashed()
            ->whereIn('nisn', $daftarNisn)
            ->orderBy('name')
            ->get();

        $nisnTidakDitemukan = array_values(array_diff(
            $daftarNisn,
            $sasaran->pluck('nisn')->map(fn ($n) => (string) $n)->all()
        ));

        if ($sasaran->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang cocok dengan NISN: '
                . implode(', ', array_slice($daftarNisn, 0, 10))
                . (count($daftarNisn) > 10 ? ' ...' : '') . '.');
        }

        // Jadwal khusus untuk siswa yang dipilih (mis. tetap masuk sampai Sabtu).
        if ($jadwalBaru !== '') {
            foreach ($sasaran as $siswaSasaran) {
                if ($jadwalBaru !== (string) ($siswaSasaran->hari_kerja ?? '')) {
                    $siswaSasaran->hari_kerja = $jadwalBaru;
                    $siswaSasaran->save();
                }
            }
        }
    } else {
        // Operasi tulis massal: SENGAJA dibatasi siswa periode berjalan agar
        // arsip angkatan lama tidak ikut ditimpa.
        $sasaran = User::siswaBerjalan()->get();

        if ($sasaran->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa PKL pada periode berjalan.');
        }

        // Mode semua: yang diubah adalah jadwal GLOBAL sekolah. Jadwal khusus
        // per siswa sengaja TIDAK dihapus supaya pengecualian tetap terjaga.
        if ($jadwalBaru !== '') {
            Pengaturan::simpan('absensi_hari_kerja', $jadwalBaru);
            $sasaran->each(fn ($s) => $s->setAttribute('hari_kerja', $s->hari_kerja));
        }
    }

    $now            = now();
    $adminId        = Auth::id();
    $periodeAktifId = Absensi::periodeAktifId();

    $totalBaris   = 0; // baris BARU yang dibuat
    $totalUbah    = 0; // baris LAMA yang hanya diubah statusnya
    $totalHapus   = 0;
    $siswaDiproses = 0;
    $siswaDilewati = [];

    // Hari kerja dihitung PER SISWA karena jadwalnya bisa berbeda-beda.
    $hariKerjaSiswa = function (User $siswa) use ($mulai, $selesai, $paksaTanggal): array {
        // Satu hari yang dipaksa admin: tanggal itu selalu boleh diisi.
        if ($paksaTanggal) {
            return [$mulai->format('Y-m-d')];
        }

        $tanggal = [];

        for ($d = $mulai->copy(); $d->lte($selesai); $d->addDay()) {
            if (! $siswa->adalahHariKerja($d)) {
                continue; // Sabtu/Minggu di luar jadwal: dilewati, tanpa baris.
            }
            $tanggal[] = $d->format('Y-m-d');
        }

        return $tanggal;
    };

    // Validasi awal khusus mode NISN supaya pesannya jelas (bukan "dilewati").
    // Dengan banyak NISN, jadwal hari kerja tiap siswa bisa berbeda: dipakai
    // siswa dengan hari kerja TERBANYAK sebagai acuan. Siswa lain yang hari
    // kerjanya tidak mencukupi tetap dilaporkan sebagai "dilewati".
    if ($data['mode'] === 'nisn') {
        $maksHariKerja = 0;
        $siswaAcuan    = $sasaran->first();

        foreach ($sasaran as $siswaCek) {
            $tersedia = count($hariKerjaSiswa($siswaCek));

            if ($tersedia > $maksHariKerja) {
                $maksHariKerja = $tersedia;
                $siswaAcuan    = $siswaCek;
            }
        }

        if ($totalDiminta > $maksHariKerja) {
            return back()->with('error', 'Total ' . $totalDiminta . ' hari melebihi ' . $maksHariKerja
                . ' hari kerja yang tersedia pada rentang ' . $mulai->format('d/m/Y') . ' - ' . $selesai->format('d/m/Y')
                . ' (jadwal ' . $siswaAcuan->labelHariKerja() . '). Perlebar rentang tanggal atau kurangi jumlahnya.');
        }
    }

    // Semua jumlah 0 DAN sisa dikosongkan = permintaan PENGOSONGAN rekap
    // (satu-satunya keadaan di mana baris absensi benar-benar dihapus).
    // Selain itu operasinya bersifat EDIT: status diubah di tempat, foto bukti
    // dan jam absen yang sudah ada TIDAK ikut hilang.
    $modeKosong = ($totalDiminta === 0 && $statusSisa === '');

    DB::transaction(function () use (
        $sasaran, $hariKerjaSiswa, $jumlah, $totalDiminta, $statusSisa, $resetTotal, $modeKosong,
        $mulai, $selesai, $now, $adminId, $periodeAktifId,
        &$totalBaris, &$totalUbah, &$totalHapus, &$siswaDiproses, &$siswaDilewati
    ) {
        foreach ($sasaran as $siswa) {
            $tanggalTersedia = $hariKerjaSiswa($siswa);

            // Jumlah yang diminta tidak muat pada hari kerja siswa ini.
            if ($totalDiminta > count($tanggalTersedia)) {
                $siswaDilewati[] = $siswa->name . ' (' . $siswa->nisn . ')';
                continue;
            }

            $jamMasuk  = substr($siswa->jamMasukEfektif(), 0, 5);
            $jamPulang = substr($siswa->jamPulangEfektif(), 0, 5);
            $periodeId = $siswa->periode_id ?: $periodeAktifId;

            // ---------------------------------------------------------------
            // 1) PENGHAPUSAN (hanya bila diminta secara eksplisit)
            //    - reset_total  : seluruh riwayat absensi siswa dibuang.
            //    - modeKosong   : rentang terpilih dibuang sehingga rekap 0.
            //    Di luar dua keadaan ini TIDAK ADA baris yang dihapus, sehingga
            //    foto bukti dan jam absen siswa tetap utuh.
            // ---------------------------------------------------------------
            if ($resetTotal || $modeKosong) {
                $queryLama = Absensi::where('siswa_id', $siswa->id);

                if (! $resetTotal) {
                    $queryLama->whereDate('tanggal', '>=', $mulai->format('Y-m-d'))
                              ->whereDate('tanggal', '<=', $selesai->format('Y-m-d'));
                }

                $lama = $queryLama->get(['id', 'foto_bukti']);

                foreach ($lama as $row) {
                    if ($row->foto_bukti) {
                        Storage::disk('public')->delete($row->foto_bukti);
                    }
                }

                if ($lama->isNotEmpty()) {
                    Absensi::whereIn('id', $lama->pluck('id'))->delete();
                    $totalHapus += $lama->count();
                }
            }

            if ($modeKosong) {
                $siswaDiproses++;
                \Illuminate\Support\Facades\Cache::forget("sinkron_alpa:{$siswa->id}:" . $now->format('Y-m-d'));
                continue;
            }

            // ---------------------------------------------------------------
            // 2) RENCANA STATUS PER TANGGAL
            //    Urutan pengisian: Hadir dulu (hari paling awal), lalu Izin,
            //    Sakit, Alpha. Sisa hari kerja hanya ikut diubah bila admin
            //    memilih "status sisa"; bila dikosongkan, hari-hari itu
            //    dibiarkan apa adanya (tidak dihapus, tidak diubah).
            // ---------------------------------------------------------------
            $rencana = [];
            $i       = 0;

            foreach ($jumlah as $status => $banyak) {
                for ($n = 0; $n < $banyak; $n++, $i++) {
                    $rencana[$tanggalTersedia[$i]] = $status;
                }
            }

            if ($statusSisa !== '') {
                for (; $i < count($tanggalTersedia); $i++) {
                    $rencana[$tanggalTersedia[$i]] = $statusSisa;
                }
            }

            // ---------------------------------------------------------------
            // 3) BARIS LAMA PADA RENTANG -> dipetakan per tanggal.
            //    whereDate() dipakai (bukan updateOrCreate) karena cast 'date'
            //    menuliskan 'Y-m-d H:i:s' yang tidak cocok dengan TEXT SQLite.
            // ---------------------------------------------------------------
            $peta = [];

            if (! empty($rencana)) {
                $lamaRentang = Absensi::where('siswa_id', $siswa->id)
                    ->whereDate('tanggal', '>=', $mulai->format('Y-m-d'))
                    ->whereDate('tanggal', '<=', $selesai->format('Y-m-d'))
                    ->get();

                foreach ($lamaRentang as $row) {
                    $peta[Carbon::parse($row->tanggal)->format('Y-m-d')] = $row;
                }
            }

            // ---------------------------------------------------------------
            // 4) EDIT baris yang sudah ada + BUAT BARU untuk tanggal kosong.
            // ---------------------------------------------------------------
            $baris = [];

            foreach ($rencana as $tanggal => $status) {
                $row = $peta[$tanggal] ?? null;

                if ($row) {
                    // EDIT: hanya status (dan pelengkapnya) yang disentuh.
                    // foto_bukti + catatan_instruktur SENGAJA tidak diubah.
                    $row->status = $status;

                    // Jam absen lama dipertahankan. Bila kosong dan statusnya
                    // Hadir, diisi otomatis dengan jam tepat waktu siswa.
                    if ($status === 'Hadir') {
                        if (empty($row->jam_masuk)) {
                            $row->jam_masuk = $jamMasuk;
                        }
                        if (empty($row->jam_pulang)) {
                            $row->jam_pulang = $jamPulang;
                        }
                    }

                    // PENTING: status VALIDASI SENGAJA TIDAK DISENTUH di sini.
                    // "Atur Jumlah" hanya mengurus jumlah hari tiap status
                    // kehadiran. Menyetujui / membatalkan validasi dikerjakan
                    // terpisah lewat fitur "Validasi Absensi".

                    $row->save();
                    $totalUbah++;

                    continue;
                }

                // BUAT BARU: jam masuk/pulang otomatis tepat waktu, foto bukti
                // dibiarkan kosong karena bersifat opsional bagi admin.
                $baris[] = [
                    'siswa_id'             => $siswa->id,
                    'periode_id'           => $periodeId,
                    'tanggal'              => $tanggal,
                    'status'               => $status,
                    'jam_masuk'            => $status === 'Hadir' ? $jamMasuk  : null,
                    'jam_pulang'           => $status === 'Hadir' ? $jamPulang : null,
                    'foto_bukti'           => null,
                    // Baris baru dibuat sebagai DRAFT (belum tervalidasi).
                    // Validasi dilakukan lewat fitur "Validasi Absensi".
                    'status_validasi'      => 'draft',
                    'validated_by_guru_id' => null,
                    'validated_at'         => null,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }

            if (! empty($baris)) {
                // insert() massal MELEWATI event model, karena itu periode_id
                // diisi manual di atas (trait MilikPeriodePkl tidak ikut jalan).
                foreach (array_chunk($baris, 500) as $potongan) {
                    Absensi::insertOrIgnore($potongan);
                }
                $totalBaris += count($baris);
            }

            $siswaDiproses++;

            // Hapus penanda cache agar penandaan Alpha otomatis dihitung ulang
            // dari data yang baru saja disimpan.
            \Illuminate\Support\Facades\Cache::forget("sinkron_alpa:{$siswa->id}:" . $now->format('Y-m-d'));
        }
    });

    if ($siswaDiproses === 0) {
        return back()->with('error', 'Tidak ada siswa yang bisa diproses. '
            . 'Jumlah yang diminta melebihi hari kerja yang tersedia pada rentang tanggal tersebut.');
    }

    $lingkup = $sasaran->count() === 1
        ? ('siswa ' . $sasaran->first()->name . ' (NISN ' . $sasaran->first()->nisn . ')')
        : ($siswaDiproses . ' siswa');

    // Semua jumlah 0 dan sisa dikosongkan = pengosongan rekap.
    if ($totalDiminta === 0 && $statusSisa === '') {
        $cakupan = $resetTotal
            ? 'SELURUH riwayat absensi'
            : ('absensi ' . $mulai->format('d/m/Y') . ' - ' . $selesai->format('d/m/Y'));

        return back()->with('success', "Rekap {$lingkup} dikosongkan: {$cakupan} dihapus ({$totalHapus} baris). "
            . 'Hadir, Izin, Sakit, dan Alpha kini 0.');
    }

    $ringkas = "Hadir {$jumlah['Hadir']}, Izin {$jumlah['Izin']}, Sakit {$jumlah['Sakit']}, Alpha {$jumlah['Alpha']}";

    $pesan = "Rekap absensi {$lingkup} berhasil diatur ({$ringkas}) pada "
        . $mulai->format('d/m/Y') . ' - ' . $selesai->format('d/m/Y')
        . ". {$totalUbah} baris lama diubah statusnya (foto bukti & jam absen tetap utuh), "
        . "{$totalBaris} baris baru dibuat dengan jam tepat waktu."
        . ' Status validasi TIDAK diubah (baris baru berstatus draft) -- gunakan menu "Validasi Absensi" untuk memvalidasi.';

    if ($totalHapus > 0) {
        $pesan .= " {$totalHapus} baris lama dihapus karena opsi reset dicentang.";
    }

    if (! empty($siswaDilewati)) {
        $contoh = implode(', ', array_slice($siswaDilewati, 0, 3));
        $sisa   = count($siswaDilewati) - 3;
        $pesan .= ' Dilewati ' . count($siswaDilewati) . ' siswa karena hari kerjanya tidak mencukupi: '
            . $contoh . ($sisa > 0 ? " dan {$sisa} lainnya." : '.');
    }

    if (! empty($nisnTidakDitemukan)) {
        $contoh = implode(', ', array_slice($nisnTidakDitemukan, 0, 5));
        $sisa   = count($nisnTidakDitemukan) - 5;
        $pesan .= ' NISN tidak ditemukan (' . count($nisnTidakDitemukan) . '): '
            . $contoh . ($sisa > 0 ? " dan {$sisa} lainnya." : '.');
    }

    return back()->with('success', $pesan);
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
