<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CatatanController;
use App\Http\Controllers\CetakPdfController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\GuruPembimbingController;
use App\Http\Controllers\InformasiController;
use App\Http\Controllers\InstrukturController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\ObservasiController;
use App\Http\Controllers\PeriodePklController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\SiswaController;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $role = auth()->user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($role === 'guru_pembimbing') {
        return redirect()->route('guru.dashboard');
    }
    if ($role === 'siswa_pkl') {
        return redirect()->route('siswa.dashboard');
    }
    if ($role === 'instruktur_industri') {
        return redirect()->route('instruktur.dashboard');
    }

    return abort(403);
})->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // ---- PROFILE (Breeze) ----
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---- INFORMASI & PANDUAN PKL (semua role bisa melihat) ----
    Route::get('/informasi', [InformasiController::class, 'index'])->name('informasi.index');

    // ---- CETAK PDF (semua role: siswa/guru/instruktur/admin) ----
    // siswa: tanpa param (otomatis dirinya). guru/instruktur/admin: sertakan id siswa.
   Route::get('/cetak/jurnal/{siswa_id?}', [CetakPdfController::class, 'cetakJurnal'])->name('cetak.jurnal');
Route::get('/cetak/jurnal-semua', [CetakPdfController::class, 'cetakJurnalSemua'])->name('cetak.jurnal.semua');
    Route::get('/cetak/catatan/{siswa_id?}', [CetakPdfController::class, 'cetakCatatan'])->name('cetak.catatan');
Route::get('/cetak/catatan-semua', [CetakPdfController::class, 'cetakCatatanSemua'])->name('cetak.catatan.semua');
    Route::get('/cetak/observasi/{siswa_id?}', [CetakPdfController::class, 'cetakObservasi'])->name('cetak.observasi');
Route::get('/cetak/observasi-semua', [CetakPdfController::class, 'cetakObservasiSemua'])->name('cetak.observasi.semua');
    Route::get('/cetak/nilai/{siswa_id?}', [CetakPdfController::class, 'cetakNilai'])->name('cetak.nilai');
Route::get('/cetak/nilai-semua', [CetakPdfController::class, 'cetakNilaiSemua'])->name('cetak.nilai.semua');

    // Surat Tugas (global)
    Route::get('/dokumen/surat-tugas/lihat', [DokumenController::class, 'lihatSuratTugas'])->name('dokumen.surat-tugas.lihat');
    Route::get('/dokumen/surat-tugas/download', [DokumenController::class, 'downloadSuratTugas'])->name('dokumen.surat-tugas.download');

    // Dokumen per-siswa (surat_penerimaan & laporan_akhir)
    Route::get('/dokumen/{siswa}/{jenis}/lihat', [DokumenController::class, 'lihat'])->name('dokumen.lihat');
    Route::get('/dokumen/{siswa}/{jenis}/download', [DokumenController::class, 'download'])->name('dokumen.download');

    // ============================================================
    // 1. ADMIN
    // ============================================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // ---- PENGATURAN: RIWAYAT AKTIVITAS ----
        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');

// ---- NOTIFIKASI SISTEM ----
        Route::get('/notifikasi', [AdminController::class, 'notifikasi'])->name('notifikasi.index');

// ---- BULK: ubah status PKL semua siswa dalam satu periode ----
        Route::post('/periode/update-status-siswa', [PeriodePklController::class, 'updateStatusSiswa'])
            ->name('periode.update-status-siswa');

        // ---- KELOLA INFORMASI PKL ----
        Route::get('/informasi', [InformasiController::class, 'adminIndex'])->name('informasi.index');
        Route::get('/informasi/create', [InformasiController::class, 'create'])->name('informasi.create');
        Route::post('/informasi', [InformasiController::class, 'store'])->name('informasi.store');
        Route::get('/informasi/{informasi}/edit', [InformasiController::class, 'edit'])->name('informasi.edit');
        Route::put('/informasi/{informasi}', [InformasiController::class, 'update'])->name('informasi.update');
        Route::delete('/informasi/{informasi}', [InformasiController::class, 'destroy'])->name('informasi.destroy');

        // ---- IMPORT / EXPORT SISWA ----
        Route::get('/siswa/export/excel', [SiswaController::class, 'exportExcel'])->name('siswa.export.excel');
        Route::get('/siswa/export/pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export.pdf');
        Route::get('/siswa/template', [SiswaController::class, 'template'])->name('siswa.template');
        Route::post('/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');

        // ---- MASTER DATA: PERIODE PKL ----
        Route::resource('periode', PeriodePklController::class)->except(['show']);
        Route::put('/periode/{periode}/aktifkan', [PeriodePklController::class, 'aktifkan'])
            ->name('periode.aktifkan');

// ---- IMPORT / EXPORT GURU ----
        Route::get('/guru/export/excel', [GuruPembimbingController::class, 'exportExcel'])->name('guru.export.excel');
        Route::get('/guru/export/pdf', [GuruPembimbingController::class, 'exportPdf'])->name('guru.export.pdf');
        Route::get('/guru/template', [GuruPembimbingController::class, 'template'])->name('guru.template');
        Route::post('/guru/import', [GuruPembimbingController::class, 'import'])->name('guru.import');

        // ---- MASTER DATA: AKUN GURU PEMBIMBING ----
        Route::resource('guru', GuruPembimbingController::class)->except(['show']);
// ---- MASTER DATA: AKUN INSTRUKTUR INDUSTRI ----
        Route::resource('instruktur', InstrukturController::class)->except(['show']);
// ---- MASTER DATA: DATA SISWA PKL ----
        Route::resource('siswa', SiswaController::class)->except(['show']);

// ---- MONITORING (read-only) ----
        Route::get('/monitoring/jurnal', [MonitoringController::class, 'jurnal'])->name('monitoring.jurnal');
        Route::get('/monitoring/catatan', [MonitoringController::class, 'catatan'])->name('monitoring.catatan');
        Route::get('/monitoring/absensi', [MonitoringController::class, 'absensi'])->name('monitoring.absensi');

// ---- EVALUASI & NILAI (read-only) ----
        Route::get('/evaluasi/observasi', [EvaluasiController::class, 'observasi'])->name('evaluasi.observasi');
        Route::get('/evaluasi/penilaian', [EvaluasiController::class, 'penilaian'])->name('evaluasi.penilaian');

// ---- MONITORING DOKUMEN ----
        Route::get('/dokumen', [DokumenController::class, 'adminIndex'])->name('dokumen.index');
        Route::get('/dokumen/surat-tugas', [DokumenController::class, 'suratTugasIndex'])->name('dokumen.surat-tugas.index');
        Route::post('/dokumen/surat-tugas', [DokumenController::class, 'uploadSuratTugas'])->name('dokumen.surat-tugas'); // ← global, tanpa {siswa}
        

    });

    // ============================================================
    // 2. GURU PEMBIMBING
    // ============================================================
    Route::middleware(['role:guru_pembimbing'])->prefix('guru')->name('guru.')->group(function () {

        Route::get('/dashboard', function () {
            $guruId = Auth::id();

            // Mengambil semua data statistik bimbingan dalam 1 kali query ke database
            $stats = User::where('role', 'siswa_pkl')
                ->where('guru_id', $guruId)
                ->selectRaw("
            COUNT(*) as bimbingan,
            SUM(CASE WHEN status_pkl = 'aktif' THEN 1 ELSE 0 END) as aktif,
            SUM(CASE WHEN status_pkl = 'belum' THEN 1 ELSE 0 END) as belum,
            SUM(CASE WHEN status_pkl = 'selesai' THEN 1 ELSE 0 END) as selesai
        ")
                ->first();

            return view('guru.dashboard', [
                'siswaBimbingan' => $stats->bimbingan ?? 0,
                'siswaAktif'     => $stats->aktif ?? 0,
                'siswaBelum'     => $stats->belum ?? 0,
                'siswaSelesai'   => $stats->selesai ?? 0,
            ]);
        })->name('dashboard');

        Route::get('/siswa', [GuruController::class, 'index'])->name('siswa.index');
        Route::get('/siswa/{id}/detail', [GuruController::class, 'detailSiswa'])->name('siswa.detail');

        // ---- MONITORING (dipisah: Jurnal & Absensi) ----
        Route::get('/monitoring/jurnal', [GuruController::class, 'monitoringJurnal'])->name('monitoring.jurnal');
        Route::get('/monitoring/absensi', [GuruController::class, 'monitoringAbsensi'])->name('monitoring.absensi');

        // Observasi (dikelola ObservasiController, bukan GuruController lagi)
Route::get('/observasi', [ObservasiController::class, 'indexGuru'])->name('observasi.index');
Route::get('/observasi/create', [ObservasiController::class, 'createGuru'])->name('observasi.create');
Route::post('/observasi', [ObservasiController::class, 'storeGuru'])->name('observasi.store');
Route::get('/observasi/{id}/edit', [ObservasiController::class, 'editGuru'])->name('observasi.edit');
Route::put('/observasi/{id}', [ObservasiController::class, 'updateGuru'])->name('observasi.update');
Route::delete('/observasi/{id}', [ObservasiController::class, 'destroyGuru'])->name('observasi.destroy');

        Route::get('/catatan', [CatatanController::class, 'indexGuru'])->name('catatan.index');
        // sebelumnya: Route::get('/nilai', [NilaiController::class, 'indexGuru'])->name('nilai.index');
        Route::get('/nilai', [NilaiController::class, 'indexGuru'])->name('nilai.index');
        Route::post('/nilai', [NilaiController::class, 'storeGuru'])->name('nilai.store');

        Route::get('/dokumen', [DokumenController::class, 'guruIndex'])->name('dokumen.index');

    });

    // ============================================================
    // 3. SISWA PKL
    // ============================================================
    Route::middleware(['role:siswa_pkl'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', function () {
            $jumlahJurnal    = Jurnal::where('siswa_id', Auth::id())->count();
            $jurnalDisetujui = Jurnal::where('siswa_id', Auth::id())->where('status_persetujuan', 'disetujui')->count();
            return view('siswa.dashboard', compact('jumlahJurnal', 'jurnalDisetujui'));
        })->name('dashboard');

        Route::get('/jurnal', [JurnalController::class, 'indexSiswa'])->name('jurnal.index');
        Route::get('/jurnal/tambah', [JurnalController::class, 'createSiswa'])->name('jurnal.create');
        Route::post('/jurnal', [JurnalController::class, 'storeSiswa'])->name('jurnal.store');
        Route::delete('/jurnal/{id}', [JurnalController::class, 'destroySiswa'])->name('jurnal.destroy');

        Route::get('/catatan', [CatatanController::class, 'indexSiswa'])->name('catatan.index');
        Route::get('/catatan/create', [CatatanController::class, 'createSiswa'])->name('catatan.create');
        Route::post('/catatan', [CatatanController::class, 'storeSiswa'])->name('catatan.store');

        Route::get('/observasi', [ObservasiController::class, 'indexSiswa'])->name('observasi.index');
        Route::get('/nilai', [NilaiController::class, 'indexSiswa'])->name('nilai.index');

        Route::get('/dokumen', [DokumenController::class, 'siswaIndex'])->name('dokumen.index');
        Route::post('/dokumen', [DokumenController::class, 'siswaStore'])->name('dokumen.store');

        // Lihat rekap kehadiran sendiri
        Route::get('/absensi', [AbsensiController::class, 'indexSiswa'])->name('absensi.index');

    });

    // ============================================================
    // 4. INSTRUKTUR INDUSTRI
    // ============================================================
    Route::middleware(['role:instruktur_industri'])->prefix('instruktur')->name('instruktur.')->group(function () {

        Route::get('/dashboard', function () {
            $instrukturId = Auth::id();

            // Mengambil statistik siswa dan jumlah jurnal pending dalam 1 query terpadu
            $stats = User::where('role', 'siswa_pkl')
                ->where('instruktur_id', $instrukturId)
                ->leftJoin('jurnals', function ($join) {
                    $join->on('users.id', '=', 'jurnals.siswa_id')
                        ->where('jurnals.status_persetujuan', '=', 'pending');
                })
                ->selectRaw("
            COUNT(DISTINCT users.id) as bimbingan,
            COUNT(DISTINCT CASE WHEN users.status_pkl = 'aktif' THEN users.id END) as aktif,
            COUNT(DISTINCT CASE WHEN users.status_pkl = 'belum' THEN users.id END) as belum,
            COUNT(DISTINCT CASE WHEN users.status_pkl = 'selesai' THEN users.id END) as selesai,
            COUNT(jurnals.id) as pending
        ")
                ->first();

            return view('instruktur.dashboard', [
                'siswaBimbingan' => $stats->bimbingan ?? 0,
                'siswaAktif'     => $stats->aktif ?? 0,
                'siswaBelum'     => $stats->belum ?? 0,
                'siswaSelesai'   => $stats->selesai ?? 0,
                'jurnalPending'  => $stats->pending ?? 0,
            ]);
        })->name('dashboard');

        Route::get('/siswa', [InstrukturController::class, 'monitoringSiswa'])->name('siswa.index');

        Route::get('/jurnal', [JurnalController::class, 'indexInstruktur'])->name('jurnal.index');
        Route::put('/jurnal/{id}/update', [JurnalController::class, 'updateInstruktur'])->name('jurnal.update');

        Route::get('/absensi', [AbsensiController::class, 'indexInstruktur'])->name('absensi.index');
        Route::post('/absensi', [AbsensiController::class, 'storeInstruktur'])->name('absensi.store');

       Route::get('/catatan', [CatatanController::class, 'indexInstruktur'])->name('catatan.index');
Route::put('/catatan/{id}/approve', [CatatanController::class, 'approveInstruktur'])->name('catatan.approve');
Route::put('/catatan/{id}/batal', [CatatanController::class, 'batalApproveInstruktur'])->name('catatan.batal');
Route::put('/catatan/{id}/komentar', [CatatanController::class, 'komentarInstruktur'])->name('catatan.komentar');

       Route::get('/observasi', [ObservasiController::class, 'indexInstruktur'])->name('observasi.index');
Route::put('/observasi/{id}/approve', [ObservasiController::class, 'approveInstruktur'])->name('observasi.approve');
Route::put('/observasi/{id}/batal', [ObservasiController::class, 'batalApproveInstruktur'])->name('observasi.batal');

        Route::get('/nilai', [NilaiController::class, 'indexInstruktur'])->name('nilai.index');
        Route::get('/nilai/create', [NilaiController::class, 'createInstruktur'])->name('nilai.create');
        Route::post('/nilai', [NilaiController::class, 'storeInstruktur'])->name('nilai.store');
    });

});

require __DIR__ . '/auth.php';