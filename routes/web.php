<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Admin\InformasiController as AdminInformasiController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\PerusahaanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CatatanController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\InformasiPublikController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ObservasiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifikasi (semua peran)
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');

    // Panduan / Informasi umum (semua peran, read-only)
    Route::get('/panduan', [InformasiPublikController::class, 'index'])->name('informasi.index');
    Route::get('/panduan/{informasi}', [InformasiPublikController::class, 'show'])->name('informasi.show');

    /* ===================== JURNAL ===================== */
    Route::middleware('role:siswa_pkl,instruktur_industri,guru_pembimbing')->group(function () {
        Route::get('/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
    });
    Route::middleware('role:siswa_pkl')->group(function () {
        Route::get('/jurnal/tambah', [JurnalController::class, 'create'])->name('jurnal.create');
        Route::post('/jurnal', [JurnalController::class, 'store'])->name('jurnal.store');
        Route::delete('/jurnal/{jurnal}', [JurnalController::class, 'destroy'])->name('jurnal.destroy');
    });
    Route::middleware('role:instruktur_industri')->put('/jurnal/{jurnal}/approve', [JurnalController::class, 'approve'])->name('jurnal.approve');

    /* ===================== CATATAN KEGIATAN ===================== */
    Route::middleware('role:siswa_pkl,instruktur_industri,guru_pembimbing')->get('/catatan', [CatatanController::class, 'index'])->name('catatan.index');
    Route::middleware('role:siswa_pkl')->group(function () {
        Route::get('/catatan/tambah', [CatatanController::class, 'create'])->name('catatan.create');
        Route::post('/catatan', [CatatanController::class, 'store'])->name('catatan.store');
        Route::delete('/catatan/{catatan}', [CatatanController::class, 'destroy'])->name('catatan.destroy');
    });
    Route::middleware('role:instruktur_industri')->put('/catatan/{catatan}/approve', [CatatanController::class, 'approve'])->name('catatan.approve');

    /* ===================== OBSERVASI ===================== */
    Route::middleware('role:guru_pembimbing,siswa_pkl,instruktur_industri')->get('/observasi', [ObservasiController::class, 'index'])->name('observasi.index');
    Route::middleware('role:guru_pembimbing')->group(function () {
        Route::get('/observasi/tambah', [ObservasiController::class, 'create'])->name('observasi.create');
        Route::post('/observasi', [ObservasiController::class, 'store'])->name('observasi.store');
        Route::delete('/observasi/{observasi}', [ObservasiController::class, 'destroy'])->name('observasi.destroy');
    });
    Route::middleware('role:instruktur_industri')->put('/observasi/{observasi}/approve', [ObservasiController::class, 'approve'])->name('observasi.approve');

    /* ===================== ABSENSI ===================== */
    Route::middleware('role:instruktur_industri,siswa_pkl,guru_pembimbing')->group(function () {
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    });
    Route::middleware('role:instruktur_industri')->post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');

    /* ===================== NILAI ===================== */
    Route::middleware('role:instruktur_industri,siswa_pkl,guru_pembimbing')->get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
    Route::middleware('role:instruktur_industri')->post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');

    /* ===================== DOKUMEN ===================== */
    Route::middleware('role:siswa_pkl,guru_pembimbing,instruktur_industri')->group(function () {
        Route::get('/dokumen', [DokumenController::class, 'index'])->name('dokumen.index');
    });
    Route::middleware('role:siswa_pkl')->group(function () {
        Route::post('/dokumen', [DokumenController::class, 'store'])->name('dokumen.store');
        Route::delete('/dokumen/{dokumen}', [DokumenController::class, 'destroy'])->name('dokumen.destroy');
    });

    /* ===================== CETAK PDF ===================== */
    Route::prefix('cetak')->name('cetak.')->group(function () {
        Route::get('/jurnal/{siswa?}',    [CetakController::class, 'jurnal'])->name('jurnal');
        Route::get('/catatan/{siswa?}',   [CetakController::class, 'catatan'])->name('catatan');
        Route::get('/observasi/{siswa?}', [CetakController::class, 'observasi'])->name('observasi');
        Route::get('/nilai/{siswa?}',     [CetakController::class, 'nilai'])->name('nilai');
    });

    /* ===================== ADMIN ===================== */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}/mapping', [UserController::class, 'updateMapping'])->name('users.mapping');
        Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::resource('perusahaan', PerusahaanController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('periode', PeriodeController::class)->only(['index', 'store', 'destroy']);
        Route::put('/periode/{periode}/aktifkan', [PeriodeController::class, 'aktifkan'])->name('periode.aktifkan');
        Route::resource('informasi', AdminInformasiController::class)->except(['show']);
        Route::get('/pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
    });
});

require __DIR__ . '/auth.php';
