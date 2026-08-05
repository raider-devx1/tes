<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\TandaTangan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EvaluasiController extends Controller
{
    /** Opsi filter kelas & jurusan dari seluruh siswa PKL. */
    private function opsiFilter(): array
    {
        // Opsi diambil dari SELURUH periode supaya kelas/jurusan angkatan lama
        // tetap bisa dipilih walau periode aktif sudah berganti.
        $kelasList = User::siswa()->withoutTrashed()
            ->whereNotNull('kelas')->where('kelas', '!=', '')
            ->distinct()->orderBy('kelas')->pluck('kelas');

        $jurusanList = User::siswa()->withoutTrashed()
            ->whereNotNull('jurusan')->where('jurusan', '!=', '')
            ->distinct()->orderBy('jurusan')->pluck('jurusan');

        return [$kelasList, $jurusanList];
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

    /** Daftar siswa PKL untuk pencocokan NISN pada modal tambah/edit. */
    private function siswaList()
    {
        // Semua status (belum / aktif / selesai) DAN semua periode disertakan.
        // Siswa angkatan lama tetap perlu bisa dinilai / diobservasi susulan.
        return User::siswa()->withoutTrashed()
            ->orderByRaw("CASE status_pkl WHEN 'aktif' THEN 1 WHEN 'belum' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get(['id', 'name', 'nisn', 'status_pkl']);
    }

    /*
    |--------------------------------------------------------------------------
    | OBSERVASI — Evaluasi Lembar Observasi Guru
    |--------------------------------------------------------------------------
    */
   /*
|--------------------------------------------------------------------------
| EVALUASI — LEMBAR OBSERVASI (admin: full akses, sama seperti guru)
|--------------------------------------------------------------------------
*/

public function observasi(Request $request)
{
    [$kelasList, $jurusanList] = $this->opsiFilter();

    $statusPkl = $this->statusPklValid($request->get('status_pkl', ''));

    $query = Observasi::with(['user.perusahaan', 'guru', 'items'])
        ->whereHas('user', fn ($u) => $u->siswa()->withoutTrashed())
        ->when($request->filled('q'), function ($q) use ($request) {
            $cari = trim($request->q);
            $q->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$cari}%")
                ->orWhere('nisn', 'like', "%{$cari}%"));
        })
        ->when($request->filled('kelas'), fn ($q) => $q
            ->whereHas('user', fn ($u) => $u->where('kelas', $request->kelas)))
        ->when($request->filled('jurusan'), fn ($q) => $q
            ->whereHas('user', fn ($u) => $u->where('jurusan', $request->jurusan)))
        ->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status === '1') {
                $q->where('status', 'tervalidasi');
            } elseif ($request->status === '0') {
                $q->where('status', '!=', 'tervalidasi');
            }
        })
        ->when($statusPkl, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('status_pkl', $statusPkl)))
        // Urutan utama: siswa AKTIF dulu, lalu BELUM, terakhir SELESAI.
        ->orderByRaw($this->prioritasStatusSql(Observasi::class, 'user_id'))
        ->latest('hari_tanggal');

    $observasi = (clone $query)->paginate(15)->withQueryString();

    $baseRekap = Observasi::whereHas('user', fn ($u) => $u->siswa()->withoutTrashed());
    $rekap = [
        'total'     => (clone $baseRekap)->count(),
        'disetujui' => (clone $baseRekap)->where('status', 'tervalidasi')->count(),
        'menunggu'  => (clone $baseRekap)->where('status', '!=', 'tervalidasi')->count(),
    ];

    $jumlahGuru = User::where('role', 'guru')->count();
    $siswaList  = $this->siswaList();   // <-- INI yang kurang

    return view('admin.evaluasi.observasi', compact(
        'observasi', 'rekap', 'jumlahGuru', 'kelasList', 'jurusanList', 'siswaList', 'statusPkl'
    ));
}

public function storeObservasi(Request $request)
{
    $validated = $request->validate([
        'user_id'              => 'required|exists:users,id',
        'hari_tanggal'         => 'required|date',
        'pekerjaan_projek'     => 'nullable|string|max:255',
        'items'                => 'required|array|min:1',
        'items.*.permasalahan' => 'required|string',
        'items.*.solusi'       => 'required|string',
    ], [
        'user_id.required'              => 'Siswa (NISN) wajib dipilih.',
        'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
        'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
        'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
    ]);

    $siswa = User::where('id', $validated['user_id'])
        ->siswa()
        ->firstOrFail();

    DB::transaction(function () use ($validated, $siswa) {
        $observasi = Observasi::create([
            'user_id'          => $siswa->id,
            'guru_id'          => $siswa->guru_id,
            'hari_tanggal'     => $validated['hari_tanggal'],
            'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
            'status'           => 'draft',
        ]);

        foreach ($validated['items'] as $item) {
            $observasi->items()->create([
                'permasalahan' => $item['permasalahan'],
                'solusi'       => $item['solusi'],
            ]);
        }
    });

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Lembar observasi berhasil ditambahkan (status: menunggu). Lakukan validasi untuk mengesahkan.');
}

public function updateObservasi(Request $request, Observasi $observasi)
{
    $validated = $request->validate([
        'user_id'              => 'required|exists:users,id',
        'hari_tanggal'         => 'required|date',
        'pekerjaan_projek'     => 'nullable|string|max:255',
        'items'                => 'required|array|min:1',
        'items.*.permasalahan' => 'required|string',
        'items.*.solusi'       => 'required|string',
    ], [
        'user_id.required'              => 'Siswa (NISN) wajib dipilih.',
        'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
        'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
        'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
    ]);

    $siswa = User::where('id', $validated['user_id'])
        ->siswa()
        ->firstOrFail();

    DB::transaction(function () use ($observasi, $validated, $siswa) {
        // Isi diubah -> status kembali ke menunggu & validasi lama dibatalkan
        $observasi->update([
            'user_id'              => $siswa->id,
            'guru_id'              => $siswa->guru_id,
            'hari_tanggal'         => $validated['hari_tanggal'],
            'pekerjaan_projek'     => $validated['pekerjaan_projek'] ?? null,
            'status'               => 'draft',
            'validated_by_guru_id' => null,
            'validated_at'         => null,
        ]);

        $observasi->items()->delete();
        foreach ($validated['items'] as $item) {
            $observasi->items()->create([
                'permasalahan' => $item['permasalahan'],
                'solusi'       => $item['solusi'],
            ]);
        }
    });

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Lembar observasi diperbarui. Status kembali ke menunggu dan perlu divalidasi ulang.');
}

/**
 * VALIDASI oleh Admin — unggah BUKTI FOTO OBSERVASI + bubuhkan PARAF
 * DIGITAL guru pembimbing & instruktur langsung di layar (keduanya OPSIONAL).
 *
 * Paraf digital menggantikan unggahan foto lembar fisik berparaf pada alur
 * lama. Kolom foto_lembar_observasi tetap dipertahankan agar data lama
 * masih bisa dilihat & dicetak. Status -> tervalidasi.
 */
public function validasiObservasi(Request $request, Observasi $observasi)
{
    // Paraf boleh kosong; bila diisi, isinya harus data URL kanvas yang sah.
    $aturanParaf = function ($atribut, $nilai, $gagal) {
        if (blank($nilai)) {
            return;
        }

        if (! TandaTangan::valid($nilai)) {
            $gagal('Paraf digital tidak terbaca. Ulangi coretan paraf pada kotak yang tersedia.');
        }
    };

    $request->validate([
        // Bukti foto observasi hanya wajib bila memang belum pernah diunggah.
        'foto_dokumentasi'     => [$observasi->foto_dokumentasi ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        // Paraf digital: OPSIONAL, boleh dibubuhkan menyusul.
        'ttd_guru'             => ['nullable', 'string', $aturanParaf],
        'ttd_instruktur'       => ['nullable', 'string', $aturanParaf],
        'hapus_ttd_guru'       => ['nullable', 'boolean'],
        'hapus_ttd_instruktur' => ['nullable', 'boolean'],
    ], [
        'foto_dokumentasi.required' => 'Bukti foto observasi wajib diunggah.',
        'foto_dokumentasi.image'    => 'Bukti foto observasi harus berupa gambar.',
        'foto_dokumentasi.mimes'    => 'Format bukti foto observasi harus JPG, JPEG, atau PNG.',
        'foto_dokumentasi.max'      => 'Ukuran bukti foto observasi maksimal 3 MB.',
    ]);

    $isi = [
        'status'               => 'tervalidasi',
        'validated_by_guru_id' => $observasi->guru_id ?? Auth::id(),
        'validated_at'         => now(),
    ];

    // Foto baru menggantikan foto lama (bila ada).
    if ($request->hasFile('foto_dokumentasi')) {
        if ($observasi->foto_dokumentasi && Storage::disk('public')->exists($observasi->foto_dokumentasi)) {
            Storage::disk('public')->delete($observasi->foto_dokumentasi);
        }

        $isi['foto_dokumentasi'] = ImageCompressor::store($request->file('foto_dokumentasi'), 'observasi/dokumentasi');
    }

    // ---- Paraf digital GURU PEMBIMBING (opsional) ----
    if (filled($request->input('ttd_guru'))) {
        TandaTangan::hapus($observasi->ttd_guru);

        $isi['ttd_guru']           = TandaTangan::simpan($request->input('ttd_guru'), 'ttd/observasi/guru');
        $isi['ttd_guru_nama']      = $observasi->guru->name ?? (Auth::user()->name ?? null);
        $isi['ttd_guru_signed_at'] = now();
    } elseif ($request->boolean('hapus_ttd_guru')) {
        TandaTangan::hapus($observasi->ttd_guru);

        $isi['ttd_guru']           = null;
        $isi['ttd_guru_nama']      = null;
        $isi['ttd_guru_signed_at'] = null;
    }

    // ---- Paraf digital INSTRUKTUR (opsional) ----
    if (filled($request->input('ttd_instruktur'))) {
        TandaTangan::hapus($observasi->ttd_instruktur);

        $isi['ttd_instruktur']           = TandaTangan::simpan($request->input('ttd_instruktur'), 'ttd/observasi/instruktur');
        $isi['ttd_instruktur_nama']      = $this->namaInstrukturSiswa($observasi->user);
        $isi['ttd_instruktur_signed_at'] = now();
    } elseif ($request->boolean('hapus_ttd_instruktur')) {
        TandaTangan::hapus($observasi->ttd_instruktur);

        $isi['ttd_instruktur']           = null;
        $isi['ttd_instruktur_nama']      = null;
        $isi['ttd_instruktur_signed_at'] = null;
    }

    $observasi->update($isi);

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Lembar observasi berhasil divalidasi. Hasil cetak kini menampilkan keterangan "SUDAH DIVALIDASI".');
}

/**
 * BATALKAN VALIDASI oleh Admin — status kembali ke menunggu.
 * Foto tetap disimpan agar mudah divalidasi ulang.
 */
public function batalValidasiObservasi(Observasi $observasi)
{
    $observasi->update([
        'status'               => 'draft',
        'validated_by_guru_id' => null,
        'validated_at'         => null,
    ]);

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Validasi lembar observasi dibatalkan. Status kembali ke menunggu.');
}

public function destroyObservasi(Observasi $observasi)
{
    if ($observasi->foto_dokumentasi && Storage::disk('public')->exists($observasi->foto_dokumentasi)) {
        Storage::disk('public')->delete($observasi->foto_dokumentasi);
    }
    if ($observasi->foto_lembar_observasi && Storage::disk('public')->exists($observasi->foto_lembar_observasi)) {
        Storage::disk('public')->delete($observasi->foto_lembar_observasi);
    }

    // Berkas paraf digital ikut dibersihkan.
    TandaTangan::hapus($observasi->ttd_guru);
    TandaTangan::hapus($observasi->ttd_instruktur);

    $observasi->delete();

    return redirect()->route('admin.evaluasi.observasi')
        ->with('success', 'Data observasi berhasil dihapus.');
}

/*
|--------------------------------------------------------------------------
| VALIDASI OBSERVASI MASSAL (beberapa NISN sekaligus / semua siswa)
|--------------------------------------------------------------------------
| Mengikuti pola halaman Monitoring Jurnal admin: satu tombol yang bisa
| memvalidasi lembar observasi milik BEBERAPA NISN sekaligus atau SEMUA
| siswa, dengan filter hari tertentu / rentang tanggal / seluruh riwayat.
*/

/** Nama instruktur (pembimbing industri) milik seorang siswa. */
private function namaInstrukturSiswa($siswa): ?string
{
    if (! $siswa instanceof User) {
        $siswa = $siswa ? User::find($siswa) : null;
    }

    $nama = $siswa ? ($siswa->instruktur->name ?? null) : null;

    return ($nama && $nama !== 'Belum Diatur') ? $nama : null;
}

/** Pecah daftar NISN yang dipisah koma / titik koma / spasi / baris baru. */
private function pecahDaftar($nilai): array
{
    if (is_array($nilai)) {
        $nilai = implode(',', $nilai);
    }

    $pecah  = preg_split('/[^0-9A-Za-z]+/', (string) $nilai) ?: [];
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
 * Filter tanggal untuk validasi massal observasi.
 *  - 'tanggal' : hanya SATU hari tertentu
 *  - 'rentang' : dari tanggal mulai s.d. tanggal selesai
 *  - 'semua'   : seluruh riwayat (tanpa batas tanggal)
 */
private function filterTanggalValidasi($query, string $jenis, ?string $tanggal, ?string $mulai, ?string $selesai, string $kolom = 'hari_tanggal')
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
 * Kumpulkan siswa sasaran validasi massal.
 * NISN yang tidak ditemukan dikembalikan lewat $tidakDitemukan.
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

    // Mode 'semua': bawaannya hanya siswa periode BERJALAN supaya arsip
    // angkatan lama tidak ikut tersentuh; admin bisa melebarkannya sendiri.
    $query = $semuaPeriode ? User::siswa()->withoutTrashed() : User::siswaBerjalan();

    return $query->orderBy('name')->get(['id', 'name', 'nisn', 'kelas', 'jurusan', 'status_pkl']);
}

/**
 * PRATINJAU VALIDASI OBSERVASI (JSON, dipanggil modal admin).
 * Menghitung dulu berapa lembar observasi yang akan terkena aksi.
 */
public function pratinjauValidasiObservasi(Request $request)
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

    $ringkasan = ['total' => 0, 'draft' => 0, 'diajukan' => 0, 'tervalidasi' => 0, 'belum' => 0, 'siswa' => 0];

    if ($siswa->isEmpty()) {
        return response()->json([
            'ok'              => true,
            'jumlah_siswa'    => 0,
            'daftar'          => [],
            'ringkasan'       => $ringkasan,
            'tidak_ditemukan' => $tidakDitemukan,
        ]);
    }

    $query = Observasi::query()->whereIn('user_id', $siswa->pluck('id'));
    $this->filterTanggalValidasi($query, $jenis, $tanggal, $mulai, $selesai, 'hari_tanggal');

    $rekap = $query->selectRaw('user_id, status, COUNT(*) as jumlah')
        ->groupBy('user_id', 'status')
        ->get();

    // Status lama bisa NULL / tak dikenal: dihitung sebagai draft.
    $peta = [];
    foreach ($rekap as $row) {
        $st = in_array($row->status, ['draft', 'diajukan', 'tervalidasi'], true) ? $row->status : 'draft';

        $peta[$row->user_id][$st] = (int) ($peta[$row->user_id][$st] ?? 0) + (int) $row->jumlah;
    }

    $daftar = [];

    foreach ($siswa as $s) {
        $draft       = (int) ($peta[$s->id]['draft'] ?? 0);
        $diajukan    = (int) ($peta[$s->id]['diajukan'] ?? 0);
        $tervalidasi = (int) ($peta[$s->id]['tervalidasi'] ?? 0);
        $total       = $draft + $diajukan + $tervalidasi;

        $ringkasan['total']       += $total;
        $ringkasan['draft']       += $draft;
        $ringkasan['diajukan']    += $diajukan;
        $ringkasan['tervalidasi'] += $tervalidasi;

        if ($total > 0) {
            $ringkasan['siswa']++;
        }

        $daftar[] = [
            'nisn'        => (string) $s->nisn,
            'name'        => $s->name,
            'kelas'       => $s->kelas ?? '-',
            'status_pkl'  => $s->status_pkl ?? '-',
            'total'       => $total,
            'draft'       => $draft,
            'diajukan'    => $diajukan,
            'tervalidasi' => $tervalidasi,
            'belum'       => $draft + $diajukan,
        ];
    }

    $ringkasan['belum'] = $ringkasan['draft'] + $ringkasan['diajukan'];

    return response()->json([
        'ok'              => true,
        'jumlah_siswa'    => $siswa->count(),
        // Mode 'semua' bisa berisi ratusan siswa: kirim contoh 50 teratas saja.
        'daftar'          => $mode === 'semua' ? array_slice($daftar, 0, 50) : $daftar,
        'ringkasan'       => $ringkasan,
        'tidak_ditemukan' => $tidakDitemukan,
    ]);
}

/**
 * VALIDASI OBSERVASI MASSAL.
 *
 * Lingkup:
 *  - mode 'nisn'  : BEBERAPA NISN sekaligus (dipisah koma/spasi/baris baru)
 *  - mode 'semua' : seluruh siswa PKL periode berjalan (opsional semua periode)
 *
 * Aksi:
 *  - 'setujui'  : status -> tervalidasi
 *  - 'batalkan' : status -> draft
 *
 * Isi observasi, bukti foto, dan paraf digital TIDAK diubah.
 */
public function validasiObservasiMassal(Request $request)
{
    $data = $request->validate([
        'mode'            => ['required', Rule::in(['nisn', 'semua'])],
        'nisn'            => ['required_if:mode,nisn', 'nullable', 'string', 'max:5000'],
        'jenis_tanggal'   => ['required', Rule::in(['tanggal', 'rentang', 'semua'])],
        'tanggal'         => ['required_if:jenis_tanggal,tanggal', 'nullable', 'date'],
        'tanggal_mulai'   => ['required_if:jenis_tanggal,rentang', 'nullable', 'date'],
        'tanggal_selesai' => ['required_if:jenis_tanggal,rentang', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
        'aksi'            => ['required', Rule::in(['setujui', 'batalkan'])],
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

    $query = Observasi::query()->whereIn('user_id', $siswa->pluck('id'));
    $this->filterTanggalValidasi(
        $query,
        $jenis,
        $data['tanggal'] ?? null,
        $data['tanggal_mulai'] ?? null,
        $data['tanggal_selesai'] ?? null,
        'hari_tanggal'
    );

    if ($aksi === 'setujui') {
        if (in_array($sumber, ['draft', 'diajukan'], true)) {
            $query->where('status', $sumber);
        } else {
            $query->where(fn ($q) => $q->where('status', '!=', 'tervalidasi')
                                       ->orWhereNull('status'));
        }
    } else {
        $query->where('status', 'tervalidasi');
    }

    $baris = $query->get(['id', 'user_id']);

    if ($baris->isEmpty()) {
        return back()->with('error', $aksi === 'setujui'
            ? 'Tidak ada lembar observasi yang perlu divalidasi pada filter tersebut (semuanya sudah tervalidasi atau datanya belum ada).'
            : 'Tidak ada lembar observasi tervalidasi yang bisa dibatalkan pada filter tersebut.');
    }

    $isi = $aksi === 'setujui'
        ? [
            'status'               => 'tervalidasi',
            'validated_by_guru_id' => DB::raw('COALESCE(guru_id, ' . (int) Auth::id() . ')'),
            'validated_at'         => now(),
        ]
        : [
            'status'               => 'draft',
            'validated_by_guru_id' => null,
            'validated_at'         => null,
        ];

    DB::transaction(function () use ($baris, $isi) {
        foreach ($baris->pluck('id')->chunk(500) as $potongan) {
            Observasi::whereIn('id', $potongan->all())->update($isi);
        }
    });

    $totalBaris     = $baris->count();
    $siswaTerdampak = $baris->pluck('user_id')->unique()->count();

    $cakupan = match ($jenis) {
        'tanggal' => 'tanggal ' . Carbon::parse($data['tanggal'])->format('d/m/Y'),
        'rentang' => 'rentang ' . Carbon::parse($data['tanggal_mulai'])->format('d/m/Y')
            . ' - ' . Carbon::parse($data['tanggal_selesai'])->format('d/m/Y'),
        default   => 'seluruh riwayat observasi',
    };

    $siswaPertama = $siswa->firstWhere('id', $baris->first()->user_id);

    $lingkup = $siswaTerdampak === 1
        ? ('siswa ' . $siswaPertama?->name . ' (NISN ' . $siswaPertama?->nisn . ')')
        : ($siswaTerdampak . ' siswa');

    $pesan = $aksi === 'setujui'
        ? "Validasi observasi berhasil: {$totalBaris} lembar observasi milik {$lingkup} kini TERVALIDASI pada {$cakupan}."
        : "Validasi dibatalkan: {$totalBaris} lembar observasi milik {$lingkup} dikembalikan ke draft pada {$cakupan}.";

    if ($aksi === 'setujui' && in_array($sumber, ['draft', 'diajukan'], true)) {
        $pesan .= ' Hanya observasi berstatus ' . $sumber . ' yang diproses.';
    }

    $pesan .= ' Isi observasi, bukti foto, dan paraf digital tidak diubah.';

    if (! empty($tidakDitemukan)) {
        $contoh = implode(', ', array_slice($tidakDitemukan, 0, 5));
        $sisa   = count($tidakDitemukan) - 5;
        $pesan .= ' NISN tidak ditemukan (' . count($tidakDitemukan) . '): ' . $contoh . ($sisa > 0 ? " dan {$sisa} lainnya." : '.');
    }

    return back()->with('success', $pesan);
}

    /*
    |--------------------------------------------------------------------------
    | PENILAIAN — Rekap & Penilaian Siswa PKL (sistem guru 6 komponen 0–100)
    |--------------------------------------------------------------------------
    */
    public function penilaian(Request $request)
    {
        [$kelasList, $jurusanList] = $this->opsiFilter();

        $q       = trim($request->get('q', ''));
        $kelas   = $request->get('kelas');
        $jurusan = $request->get('jurusan');
        $status    = $request->get('status'); // 'sudah' | 'belum'
        $statusPkl = $this->statusPklValid($request->get('status_pkl', ''));

        // Basis rekap mengikuti filter Status PKL yang sedang aktif supaya
        // angka kartu ringkasan selalu sama dengan isi tabel di bawahnya.
        $basisRekap = fn () => User::siswa()->withoutTrashed()
            ->when($statusPkl, fn ($u) => $u->where('status_pkl', $statusPkl));

        $total = $basisRekap()->count();
        $sudah = $basisRekap()
            ->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir'))->count();

        $rekap = [
            'total'         => $total,
            'sudah'         => $sudah,
            'belum'         => $total - $sudah,
            'siswa_aktif'   => User::siswa()->withoutTrashed()->where('status_pkl', 'aktif')->count(),
            'siswa_belum'   => User::siswa()->withoutTrashed()->where('status_pkl', 'belum')->count(),
            'siswa_selesai' => User::siswa()->withoutTrashed()->where('status_pkl', 'selesai')->count(),
        ];

        // LINTAS PERIODE: tanpa scope berjalan(), sehingga siswa periode lama
        // tetap muncul di daftar penilaian apa pun periode yang sedang aktif.
        $siswa = User::siswa()->withoutTrashed()
            ->when($statusPkl, fn ($query) => $query->where('status_pkl', $statusPkl))
            ->with(['nilai', 'guru'])
            ->when($q !== '', fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas, fn ($query) => $query->where('kelas', $kelas))
            ->when($jurusan, fn ($query) => $query->where('jurusan', $jurusan))
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir')))
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('nilai_akhir'))))
            // Urutan utama: siswa AKTIF dulu, lalu BELUM, terakhir SELESAI.
            ->orderByRaw("CASE status_pkl WHEN 'aktif' THEN 1 WHEN 'belum' THEN 2 WHEN 'selesai' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $siswaList = $this->siswaList();

        return view('admin.evaluasi.penilaian', compact(
            'siswa', 'rekap', 'kelasList', 'jurusanList', 'siswaList', 'statusPkl'
        ));
    }

    /** Nilai akhir = rata-rata 6 komponen (0–100). Null bila belum lengkap. */
    private function hitungRataRataAkhir(Nilai $nilai): ?float
    {
        $daftarSkor = [
            $nilai->skor_soft_skill,
            $nilai->skor_hard_skill,
            $nilai->skor_pengembangan,
            $nilai->skor_kewirausahaan,
            $nilai->skor_laporan,
            $nilai->skor_presentasi,
        ];

        if (in_array(null, $daftarSkor, true)) {
            return null;
        }

        return round(array_sum($daftarSkor) / count($daftarSkor), 2);
    }

    private function aturanPenilaian(): array
    {
        return [
            'user_id'                 => 'required|exists:users,id',
            'skor_soft_skill'         => 'required|numeric|between:0,100',
            'deskripsi_soft_skill'    => 'required|string',
            'skor_hard_skill'         => 'required|numeric|between:0,100',
            'deskripsi_hard_skill'    => 'required|string',
            'skor_pengembangan'       => 'required|numeric|between:0,100',
            'deskripsi_pengembangan'  => 'required|string',
            'skor_kewirausahaan'      => 'required|numeric|between:0,100',
            'deskripsi_kewirausahaan' => 'required|string',
            'skor_laporan'            => 'required|numeric|between:0,100',
            'deskripsi_laporan'       => 'required|string',
            'skor_presentasi'         => 'required|numeric|between:0,100',
            'deskripsi_presentasi'    => 'required|string',
            'catatan_guru'            => 'nullable|string',
            'foto_lembar_instruktur'  => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ];
    }

    /** Isi seluruh komponen nilai (sistem guru) + hitung nilai akhir. */
    private function isiNilai(Nilai $nilai, Request $request, User $siswa): void
    {
        $nilai->user_id = $siswa->id;
        // Instruktur kini bukan akun; guru penilai diambil dari data siswa bila belum tercatat.
        $nilai->guru_id = $nilai->guru_id ?? $siswa->guru_id;

        $nilai->skor_soft_skill         = $request->skor_soft_skill;
        $nilai->deskripsi_soft_skill    = $request->deskripsi_soft_skill;
        $nilai->skor_hard_skill         = $request->skor_hard_skill;
        $nilai->deskripsi_hard_skill    = $request->deskripsi_hard_skill;
        $nilai->skor_pengembangan       = $request->skor_pengembangan;
        $nilai->deskripsi_pengembangan  = $request->deskripsi_pengembangan;
        $nilai->skor_kewirausahaan      = $request->skor_kewirausahaan;
        $nilai->deskripsi_kewirausahaan = $request->deskripsi_kewirausahaan;
        $nilai->skor_laporan            = $request->skor_laporan;
        $nilai->deskripsi_laporan       = $request->deskripsi_laporan;
        $nilai->skor_presentasi         = $request->skor_presentasi;
        $nilai->deskripsi_presentasi    = $request->deskripsi_presentasi;
        $nilai->catatan_guru            = $request->catatan_guru;

        if ($request->hasFile('foto_lembar_instruktur')) {
            if ($nilai->foto_lembar_instruktur && Storage::disk('public')->exists($nilai->foto_lembar_instruktur)) {
                Storage::disk('public')->delete($nilai->foto_lembar_instruktur);
            }
            $nilai->foto_lembar_instruktur = ImageCompressor::store($request->file('foto_lembar_instruktur'), 'nilai/lembar-instruktur');
        }

        // Nilai akhir = rata-rata 6 komponen (0–100)
        $nilai->nilai_akhir   = $this->hitungRataRataAkhir($nilai);
        $nilai->nilai_guru    = $nilai->nilai_akhir;    // kompatibilitas kolom lama
        $nilai->nilai_laporan = $request->skor_laporan; // kompatibilitas kolom lama
    }

    public function storePenilaian(Request $request)
    {
        $request->validate($this->aturanPenilaian());
        $siswa = User::siswa()->findOrFail($request->user_id);

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);
        $this->isiNilai($nilai, $request, $siswa);
        $nilai->save();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Penilaian siswa berhasil disimpan.');
    }

    public function updatePenilaian(Request $request, Nilai $nilai)
    {
        $request->validate($this->aturanPenilaian());
        $siswa = User::siswa()->findOrFail($request->user_id);

        $this->isiNilai($nilai, $request, $siswa);
        $nilai->save();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Penilaian siswa berhasil diperbarui.');
    }

    public function destroyPenilaian(Nilai $nilai)
    {
        if ($nilai->foto_lembar_instruktur && Storage::disk('public')->exists($nilai->foto_lembar_instruktur)) {
            Storage::disk('public')->delete($nilai->foto_lembar_instruktur);
        }
        $nilai->delete();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Data penilaian berhasil dihapus.');
    }
}