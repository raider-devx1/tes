<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jurnal;
use App\Models\PeriodePkl;
use App\Models\Absensi;
use App\Support\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    
   // Daftar siswa bimbingan (tabel lengkap + filter + pagination)
public function index(Request $request)
{
    $query = User::siswa()
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif') // hanya siswa yang sedang aktif PKL
        ->with(['perusahaan', 'periode']);

    // Filter pencarian teks: nama, NISN, kelas, jurusan
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%")
                ->orWhere('kelas', 'like', "%{$q}%")
                ->orWhere('jurusan', 'like', "%{$q}%");
        });
    }

    // Filter dropdown: Periode PKL.
    // Scope periode() sudah mengabaikan nilai kosong dengan sendirinya,
    // sehingga tidak perlu lagi dibungkus pengecekan filled().
    $query->periode($request->input('periode_id'));

    $siswas = $query->orderBy('name')->paginate(15)->withQueryString();

    $periodes = PeriodePkl::orderByDesc('tahun_ajaran')->orderBy('nama')->get();

    // Rekap seluruh siswa bimbingan (tidak terpengaruh filter/pagination)
    $rekapQuery = User::siswa()->where('guru_id', Auth::id());

    $rekap = [
        'total'   => (clone $rekapQuery)->count(),
        'aktif'   => (clone $rekapQuery)->where('status_pkl', 'aktif')->count(),
        'belum'   => (clone $rekapQuery)->where('status_pkl', 'belum')->count(),
        'selesai' => (clone $rekapQuery)->where('status_pkl', 'selesai')->count(),
    ];

    return view('guru.siswa.index', compact('siswas', 'periodes', 'rekap'));
}

    

    /*
    |--------------------------------------------------------------------------
    | MONITORING 1: LIHAT JURNAL (hanya-baca, semua siswa bimbingan)
    |--------------------------------------------------------------------------
    */
  public function monitoringJurnal(Request $request)
{
    $siswaIds = User::siswa()
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->pluck('id');

    $jurnals = Jurnal::with(['siswa', 'items'])
        ->whereIn('siswa_id', $siswaIds)
        ->when($request->filled('q'), function ($query) use ($request) {
            $q = $request->q;
            $query->whereHas('siswa', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        })
        ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
        ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('hari_tanggal', $request->tanggal))
        ->orderByDesc('hari_tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'total'     => Jurnal::whereIn('siswa_id', $siswaIds)->count(),
        'disetujui' => Jurnal::whereIn('siswa_id', $siswaIds)->where('status', 'disetujui')->count(),
        'diajukan'  => Jurnal::whereIn('siswa_id', $siswaIds)->where('status', 'diajukan')->count(),
        'draft'     => Jurnal::whereIn('siswa_id', $siswaIds)->where('status', 'draft')->count(),
    ];

    $siswas = User::siswa()->where('guru_id', Auth::id())->where('status_pkl', 'aktif')->orderBy('name')->get();

    return view('guru.monitoring.jurnal', compact('jurnals', 'rekap', 'siswas'));
}


    /*
    |--------------------------------------------------------------------------
    | MONITORING 2: ABSENSI (hanya-baca, semua siswa bimbingan)
    |--------------------------------------------------------------------------
    */
  public function monitoringAbsensi(Request $request)
{
    $siswaIds = User::siswa()
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->pluck('id');

    // Tutup sendiri pembukaan absensi yang batas waktunya sudah lewat, supaya
    // guru tidak perlu menekan tombol "Tutup Absensi" lagi (pengganti cron).
    User::tutupBukaKedaluwarsa($siswaIds->all());

    // Tandai otomatis Alpha (logika controller, menggantikan scheduler).
    User::whereIn('id', $siswaIds)->get()
        ->each(fn ($s) => Absensi::sinkronkanAlpa($s));

    $absensi = Absensi::with('siswa')
        ->whereIn('siswa_id', $siswaIds)
        ->when($request->filled('q'), function ($query) use ($request) {
            $q = $request->q;
            $query->whereHas('siswa', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%");
            });
        })
        ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
        ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal', $request->tanggal))
        ->orderByDesc('tanggal')
        ->paginate(15)
        ->withQueryString();

    $rekap = [
        'Hadir' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Hadir')->count(),
        'Izin'  => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Izin')->count(),
        'Sakit' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Sakit')->count(),
        'Alpha' => Absensi::whereIn('siswa_id', $siswaIds)->where('status', 'Alpha')->count(),
    ];

    $siswas = User::siswa()->where('guru_id', Auth::id())->where('status_pkl', 'aktif')->orderBy('name')->get();

    // Daftar usulan jam kerja industri yang menunggu validasi guru.
    $usulanJam = $siswas->where('status_jam_usulan', 'diajukan')->values();

    // Pengaturan jam global admin (referensi untuk guru).
    $jamAdmin = [
        'masuk'  => \App\Models\Pengaturan::ambil('absensi_jam_masuk', '08:00'),
        'pulang' => \App\Models\Pengaturan::ambil('absensi_jam_pulang', '16:00'),
    ];

    return view('guru.monitoring.absensi', compact('absensi', 'rekap', 'siswas', 'usulanJam', 'jamAdmin'));
}

/**
 * Guru membuka / menutup absensi siswa BIMBINGANNYA tanpa mengikuti jadwal jam.
 *  - mode "semua" : semua siswa bimbingan guru ini.
 *  - mode "nisn"  : satu siswa (dicocokkan NISN & harus bimbingannya).
 *  - aksi "buka"  : terbuka di luar jadwal; "tutup" : kembali ikut jadwal.
 *
 * BATAS WAKTU BUKA:
 * Saat membuka, guru menentukan absensi dibuka berapa MENIT (bawaan 30 menit).
 * Tenggatnya disimpan di users.absensi_dibuka_sampai, lalu absensi menutup
 * sendiri begitu tenggat lewat sehingga guru tidak perlu menekan tombol
 * "Tutup Absensi" lagi. Centang "tanpa batas waktu" untuk perilaku lama.
 */
public function bukaAbsensi(Request $request)
{
    $mode = $request->input('mode') === 'nisn' ? 'nisn' : 'semua';
    $buka = $request->input('aksi') === 'buka';

    $data = $request->validate([
        'durasi_menit' => ['nullable', 'integer', 'min:1', 'max:' . User::BUKA_MENIT_MAKS],
        'tanpa_batas'  => ['nullable', 'boolean'],
    ], [
        'durasi_menit.integer' => 'Lama absensi dibuka harus berupa angka menit.',
        'durasi_menit.min'     => 'Lama absensi dibuka minimal 1 menit.',
        'durasi_menit.max'     => 'Lama absensi dibuka maksimal ' . User::BUKA_MENIT_MAKS . ' menit (24 jam).',
    ]);

    $tanpaBatas = $request->boolean('tanpa_batas');
    $durasi     = (int) ($data['durasi_menit'] ?? User::BUKA_MENIT_DEFAULT);

    if ($durasi < 1 || $durasi > User::BUKA_MENIT_MAKS) {
        $durasi = User::BUKA_MENIT_DEFAULT;
    }

    // null = tanpa batas waktu; saat menutup, tenggat lama ikut dibersihkan.
    $sampai = ($buka && ! $tanpaBatas) ? now()->addMinutes($durasi) : null;

    $nilai = [
        'absensi_dibuka'        => $buka,
        'absensi_dibuka_sampai' => $sampai,
    ];

    // Keterangan tenggat untuk pesan flash.
    $ket = $sampai
        ? "selama {$durasi} menit (otomatis tertutup pukul " . $sampai->format('H:i') . ' WITA)'
        : 'tanpa batas waktu (perlu ditutup manual)';

    $base = User::siswa()->where('guru_id', Auth::id());

    if ($mode === 'semua') {
        (clone $base)->update($nilai);

        return back()->with('success', $buka
            ? "Absensi DIBUKA untuk semua siswa bimbingan Anda {$ket}."
            : 'Absensi ditutup untuk semua siswa bimbingan Anda. Kembali mengikuti jadwal.');
    }

    $nisn = trim((string) $request->input('nisn', ''));
    if ($nisn === '') {
        return back()->with('error', 'NISN wajib diisi untuk membuka/menutup absensi per siswa.');
    }

    $siswa = (clone $base)->where('nisn', $nisn)->first();
    if (! $siswa) {
        return back()->with('error', "Siswa bimbingan dengan NISN {$nisn} tidak ditemukan.");
    }

    $siswa->forceFill($nilai)->save();

    return back()->with('success', $buka
        ? "Absensi untuk {$siswa->name} (NISN {$nisn}) DIBUKA {$ket}."
        : "Absensi untuk {$siswa->name} (NISN {$nisn}) ditutup (kembali ikut jadwal).");
}

/*
|--------------------------------------------------------------------------
| TANDA TANGAN TERSIMPAN MILIK GURU PEMBIMBING
|--------------------------------------------------------------------------
| Guru mengunggah tanda tangannya SEKALI dari halaman Monitoring Absensi.
| Sesudah tersimpan, tanda tangan itu tinggal dipilih pada pop up "Beri
| Nilai" sehingga guru tidak perlu mengunggah berkas yang sama berulang.
|
| Berkasnya diproses sama seperti tanda tangan penilaian: latar diratakan
| ke putih, margin kosong dipangkas, lalu diperkecil. Dengan begitu ukuran
| cetaknya di PDF nanti sudah pasti seragam.
*/
public function simpanTtdTersimpan(Request $request)
{
    $guru = Auth::user();

    // ---- Hapus tanda tangan tersimpan ----
    if ($request->boolean('hapus')) {
        TandaTangan::hapus($guru->ttd_tersimpan);

        $guru->forceFill([
            'ttd_tersimpan'    => null,
            'ttd_tersimpan_at' => null,
        ])->save();

        return back()->with('success', 'Tanda tangan tersimpan sudah dihapus.');
    }

    // ---- Simpan / ganti tanda tangan ----
    $request->validate([
        'ttd_tersimpan' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
    ], [
        'ttd_tersimpan.required' => 'Pilih dulu berkas tanda tangan yang ingin disimpan.',
        'ttd_tersimpan.image'    => 'Tanda tangan harus berupa gambar (JPG/JPEG/PNG).',
        'ttd_tersimpan.mimes'    => 'Format tanda tangan harus JPG, JPEG, atau PNG.',
        'ttd_tersimpan.max'      => 'Ukuran tanda tangan maksimal 3 MB.',
    ]);

    $path = TandaTangan::simpanUnggahan($request->file('ttd_tersimpan'), 'ttd/guru/tersimpan');

    // Gagal diproses -> biarkan tanda tangan lama supaya tidak ikut hilang.
    if ($path === null) {
        return back()->with('error', 'Gambar tanda tangan tidak bisa diproses. Coba unggah foto lain (JPG/PNG, maks 3 MB).');
    }

    TandaTangan::hapus($guru->ttd_tersimpan);

    $guru->forceFill([
        'ttd_tersimpan'    => $path,
        'ttd_tersimpan_at' => now(),
    ])->save();

    return back()->with('success', 'Tanda tangan tersimpan diperbarui. Sekarang tinggal dipilih saat memberi nilai.');
}

}