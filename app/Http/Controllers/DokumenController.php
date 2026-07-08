<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Nilai;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DokumenController extends Controller
{


    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    /** Dashboard rekap dokumen semua siswa (hanya-baca). */
  /** Dashboard rekap dokumen semua siswa (hanya-baca). */
public function adminIndex(Request $request)
{
    $q       = trim($request->get('q', ''));
    $kelas   = $request->get('kelas');
    $jurusan = $request->get('jurusan');
    $status  = $request->get('status'); // lengkap | sebagian | belum

    $siswa = $this->querySiswa($q, $kelas, $jurusan, $status)
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'totalSiswa'      => User::where('role', 'siswa_pkl')->count(),
        'laporan'         => Dokumen::whereNotNull('laporan_akhir')->count(),
        'suratPenerimaan' => Dokumen::whereNotNull('surat_penerimaan')->count(),
        'lengkap'         => Dokumen::whereNotNull('laporan_akhir')
                                ->whereNotNull('surat_penerimaan')->count(),
        'suratTugas'      => Pengaturan::ambil('surat_tugas') ? 'Tersedia' : 'Belum',
    ];

    [$kelasList, $jurusanList] = $this->opsiFilter();

    return view('admin.dokumen.index', compact(
        'siswa', 'q', 'kelas', 'jurusan', 'status', 'rekap', 'kelasList', 'jurusanList'
    ));
}

    /*
    |--------------------------------------------------------------------------
    | ADMIN — SURAT TUGAS (GLOBAL: satu berkas untuk semua siswa)
    |--------------------------------------------------------------------------
    */

    /** Halaman admin: form unggah Surat Tugas tunggal. */
    public function suratTugasIndex()
    {
        $suratTugas = Pengaturan::ambil('surat_tugas'); // path atau null
        return view('admin.dokumen.surat-tugas', compact('suratTugas'));
    }

    /** Simpan satu Surat Tugas global (mengganti yang lama bila ada). */
    public function uploadSuratTugas(Request $request)
    {
        $request->validate(['surat_tugas' => 'required|mimes:pdf|max:2048']);

        // hapus berkas lama agar tidak menumpuk
        $lama = Pengaturan::ambil('surat_tugas');
        if ($lama && Storage::disk('public')->exists($lama)) {
            Storage::disk('public')->delete($lama);
        }

        $path = $request->file('surat_tugas')->store('dokumen_pkl', 'public');
        Pengaturan::simpan('surat_tugas', $path);

        return back()->with('success', 'Surat Tugas berhasil diunggah & berlaku untuk semua siswa.');
    }

    /** Admin: unggah / ganti Surat Penerimaan & Laporan Akhir milik siswa tertentu. */
public function adminStore(Request $request, int $siswa)
{
    $request->validate([
        'surat_penerimaan' => 'nullable|mimes:pdf|max:2048',
        'laporan_akhir'    => 'nullable|mimes:pdf|max:5120',
    ]);

    $siswaModel = User::where('role', 'siswa_pkl')->findOrFail($siswa);
    $dokumen    = Dokumen::firstOrNew(['siswa_id' => $siswaModel->id]);

    foreach (['surat_penerimaan', 'laporan_akhir'] as $jenis) {
        if ($request->hasFile($jenis)) {
            // hapus berkas lama bila ada agar tidak menumpuk
            if ($dokumen->{$jenis} && Storage::disk('public')->exists($dokumen->{$jenis})) {
                Storage::disk('public')->delete($dokumen->{$jenis});
            }
            $dokumen->{$jenis} = $request->file($jenis)->store('dokumen_pkl', 'public');
        }
    }

    $dokumen->save();
    return back()->with('success', 'Dokumen siswa berhasil disimpan.');
}

/** Admin: hapus salah satu berkas dokumen (surat_penerimaan / laporan_akhir) milik siswa. */
public function adminDestroy(int $siswa, string $jenis)
{
    abort_unless(
        in_array($jenis, ['surat_penerimaan', 'laporan_akhir'], true),
        404, 'Jenis dokumen tidak dikenal.'
    );

    $siswaModel = User::where('role', 'siswa_pkl')->findOrFail($siswa);
    $dokumen    = Dokumen::where('siswa_id', $siswaModel->id)->first();

    if ($dokumen && $dokumen->{$jenis}) {
        if (Storage::disk('public')->exists($dokumen->{$jenis})) {
            Storage::disk('public')->delete($dokumen->{$jenis});
        }
        $dokumen->{$jenis} = null;
        $dokumen->save();
    }

    return back()->with('success', 'Dokumen berhasil dihapus.');
}

    /*
    |--------------------------------------------------------------------------
    | SISWA
    |--------------------------------------------------------------------------
    */

    /** Halaman dokumen milik siswa yang login. */
    public function siswaIndex()
    {
        $dokumen    = Dokumen::where('siswa_id', Auth::id())->first();
        $nilai      = Nilai::where('user_id', Auth::id())->first();
        $suratTugas = Pengaturan::ambil('surat_tugas'); // berkas global dari admin

        return view('siswa.dokumen.index', compact('dokumen', 'nilai', 'suratTugas'));
    }

    /** Upload Surat Penerimaan & Laporan Akhir (khusus siswa). */
    public function siswaStore(Request $request)
    {
        $request->validate([
            'surat_penerimaan' => 'nullable|mimes:pdf|max:2048',
            'laporan_akhir'    => 'nullable|mimes:pdf|max:5120',
        ]);

        $siswa   = Auth::user();
        $dokumen = Dokumen::firstOrNew(['siswa_id' => $siswa->id]);

        foreach (['surat_penerimaan', 'laporan_akhir'] as $jenis) {
            if ($request->hasFile($jenis) && Dokumen::boleh('upload', $jenis, $siswa, $siswa)) {
                $dokumen->{$jenis} = $request->file($jenis)->store('dokumen_pkl', 'public');
            }
        }

        $dokumen->save();
        return back()->with('success', 'Dokumen berhasil diunggah!');
    }

    /*
    |--------------------------------------------------------------------------
    | GURU
    |--------------------------------------------------------------------------
    */

    /** Daftar dokumen siswa bimbingan untuk dilihat/diunduh guru. */
public function guruIndex(Request $request)
{
    $q      = trim($request->get('q', ''));
    $status = $request->get('status'); // lengkap | sebagian | belum

    $siswa = $this->querySiswa($q, null, null, $status)
        ->where('guru_id', Auth::id())     // hanya bimbingannya
        ->where('status_pkl', 'aktif')     // ⬅️ sembunyikan siswa yang sudah "selesai" / "belum"
        ->paginate(15)->withQueryString();

    // Rekap dokumen seluruh siswa bimbingan yang masih AKTIF (tidak terpengaruh filter/pagination)
    $rekapQuery = User::where('role', 'siswa_pkl')
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif');    // ⬅️ agar kartu rekap ikut konsisten

    $totalSiswa = (clone $rekapQuery)->count();

    $dokumenLengkap = (clone $rekapQuery)
        ->whereHas('dokumen', fn ($d) =>
            $d->whereNotNull('laporan_akhir')->whereNotNull('surat_penerimaan'))
        ->count();

    $dokumenSebagian = (clone $rekapQuery)
        ->whereHas('dokumen', fn ($d) =>
            $d->where(fn ($w) => $w->whereNotNull('laporan_akhir')->whereNull('surat_penerimaan'))
              ->orWhere(fn ($w) => $w->whereNull('laporan_akhir')->whereNotNull('surat_penerimaan')))
        ->count();

    $dokumenBelum = $totalSiswa - $dokumenLengkap - $dokumenSebagian;

    $rekap = [
        'total'    => $totalSiswa,
        'lengkap'  => $dokumenLengkap,
        'sebagian' => $dokumenSebagian,
        'belum'    => $dokumenBelum,
    ];

    return view('guru.dokumen.index', compact('siswa', 'q', 'status', 'rekap'));
}

    /*
    |--------------------------------------------------------------------------
    | AKSES SURAT TUGAS GLOBAL (semua role sesuai matriks)
    |--------------------------------------------------------------------------
    */

    /** Preview Surat Tugas global inline di browser. */
    public function lihatSuratTugas()
    {
        $path = $this->resolveSuratTugas('lihat');
        return Storage::disk('public')->response($path);
    }

    /** Download Surat Tugas global sebagai attachment PDF. */
    public function downloadSuratTugas()
    {
        $path = $this->resolveSuratTugas('download');
        return Storage::disk('public')->download($path, 'Surat-Tugas-PKL.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | AKSES DOKUMEN PER-SISWA (surat_penerimaan & laporan_akhir)
    |--------------------------------------------------------------------------
    */

    /** Preview dokumen per-siswa inline di browser. */
    public function lihat(int $siswa, string $jenis)
    {
        [$path] = $this->resolveFile('lihat', $siswa, $jenis);
        return Storage::disk('public')->response($path);
    }

    /** Download dokumen per-siswa sebagai attachment PDF. */
    public function download(int $siswa, string $jenis)
    {
        [$path, $siswaModel, $info] = $this->resolveFile('download', $siswa, $jenis);

        $namaFile = Str::slug($info['label'] . '-' . $siswaModel->name) . '.pdf';
        return Storage::disk('public')->download($path, $namaFile);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER PRIVATE
    |--------------------------------------------------------------------------
    */

   /** Query dasar daftar siswa PKL + filter pencarian, kelas, jurusan, status dokumen. */
private function querySiswa(
    string $q = '',
    ?string $kelas = null,
    ?string $jurusan = null,
    ?string $status = null
) {
    return User::query()
        ->where('role', 'siswa_pkl')
        ->with('dokumen')
        ->when($q, fn ($query) => $query->where(fn ($w) =>
            $w->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
        ->when($kelas,   fn ($query) => $query->where('kelas', $kelas))
        ->when($jurusan, fn ($query) => $query->where('jurusan', $jurusan))
        // Lengkap = punya kedua dokumen (laporan + surat penerimaan)
        ->when($status === 'lengkap', fn ($query) =>
            $query->whereHas('dokumen', fn ($d) =>
                $d->whereNotNull('laporan_akhir')->whereNotNull('surat_penerimaan')))
        // Sebagian = persis salah satu dokumen yang ada
        ->when($status === 'sebagian', fn ($query) =>
            $query->whereHas('dokumen', fn ($d) =>
                $d->where(fn ($w) => $w->whereNotNull('laporan_akhir')->whereNull('surat_penerimaan'))
                  ->orWhere(fn ($w) => $w->whereNull('laporan_akhir')->whereNotNull('surat_penerimaan'))))
        // Belum = tidak punya baris dokumen, ATAU kedua dokumen masih kosong
        ->when($status === 'belum', fn ($query) =>
            $query->where(fn ($u) =>
                $u->whereDoesntHave('dokumen')
                  ->orWhereHas('dokumen', fn ($d) =>
                      $d->whereNull('laporan_akhir')->whereNull('surat_penerimaan'))))
        ->orderBy('name');
}

/** Opsi dropdown Kelas & Jurusan dari data siswa PKL. */
private function opsiFilter(): array
{
    $kelasList = User::where('role', 'siswa_pkl')
        ->whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

    $jurusanList = User::where('role', 'siswa_pkl')
        ->whereNotNull('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan');

    return [$kelasList, $jurusanList];
}

    /** Pastikan user berhak (cek role + relasi kepemilikan); jika tidak, 403/404. */
    private function pastikanBoleh(string $aksi, string $jenis, User $siswa): void
    {
        abort_unless(isset(Dokumen::ATURAN[$jenis]), 404, 'Jenis dokumen tidak dikenal.');
        abort_unless(
            Dokumen::boleh($aksi, $jenis, Auth::user(), $siswa),
            403, 'Anda tidak punya akses untuk dokumen ini.'
        );
    }

    /** Cek akses Surat Tugas global (role saja, tanpa relasi) + ambil path. */
    private function resolveSuratTugas(string $aksi): string
    {
        $aturan = Dokumen::ATURAN['surat_tugas'];
        abort_unless(
            in_array(Auth::user()->role, $aturan[$aksi], true),
            403, 'Anda tidak punya akses untuk dokumen ini.'
        );

        $path = Pengaturan::ambil('surat_tugas');
        abort_if(!$path || !Storage::disk('public')->exists($path), 404, 'Surat Tugas belum diunggah.');

        return $path;
    }

    /** Validasi akses + ambil path file per-siswa. @return array{0:string,1:User,2:array} */
    private function resolveFile(string $aksi, int $siswaId, string $jenis): array
    {
        // Surat Tugas memakai endpoint global, bukan per-siswa.
        abort_if($jenis === 'surat_tugas', 404, 'Surat Tugas memakai endpoint global.');

        $info  = Dokumen::ATURAN[$jenis] ?? abort(404, 'Jenis dokumen tidak dikenal.');
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($siswaId);

        $this->pastikanBoleh($aksi, $jenis, $siswa);

        $path = optional(Dokumen::where('siswa_id', $siswa->id)->first())->{$jenis};
        abort_if(!$path || !Storage::disk('public')->exists($path), 404, 'File belum diunggah.');

        return [$path, $siswa, $info];
    }

    
}