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
    public function adminIndex(Request $request)
    {
        $q     = trim($request->get('q', ''));
        $siswa = $this->querySiswa($q)->paginate(15)->withQueryString();

        $rekap = [
            'totalSiswa'      => User::where('role', 'siswa_pkl')->count(),
            'laporan'         => Dokumen::whereNotNull('laporan_akhir')->count(),
            'suratPenerimaan' => Dokumen::whereNotNull('surat_penerimaan')->count(),
            'suratTugas'      => Pengaturan::ambil('surat_tugas') ? 'Tersedia' : 'Belum', // global
        ];

        return view('admin.dokumen.index', compact('siswa', 'q', 'rekap'));
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
        $q = trim($request->get('q', ''));

        $siswa = $this->querySiswa($q)
            ->where('guru_id', Auth::id())   // hanya bimbingannya
            ->paginate(15)->withQueryString();

        return view('guru.dokumen.index', compact('siswa', 'q'));
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

    /** Query dasar daftar siswa PKL + filter pencarian. */
    private function querySiswa(string $q = '')
    {
        return User::query()
            ->where('role', 'siswa_pkl')
            ->with('dokumen')
            ->when($q, fn ($query) => $query->where(fn ($w) =>
                $w->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->orderBy('name');
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