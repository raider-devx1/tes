<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CatatanController;
use App\Http\Controllers\CetakPdfController;
use App\Http\Controllers\DokumenSiswaController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\ObservasiController;
use App\Http\Controllers\ProfileController;
use App\Models\Jurnal;
use App\Models\Perusahaan;
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

    // ---- CETAK PDF (global) ----
    Route::get('/cetak/jurnal/{siswa_id}', [CetakPdfController::class, 'cetakJurnal'])->name('cetak.jurnal');
    Route::get('/cetak/nilai/{siswa_id}', [CetakPdfController::class, 'cetakNilai'])->name('cetak.nilai');

    // ============================================================
    // 1. ADMIN
    // ============================================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            $jumlahSiswa      = User::where('role', 'siswa_pkl')->count();
            $jumlahGuru       = User::where('role', 'guru_pembimbing')->count();
            $jumlahInstruktur = User::where('role', 'instruktur_industri')->count();
            $jumlahPerusahaan = Perusahaan::count();
            return view('admin.dashboard', compact('jumlahSiswa', 'jumlahGuru', 'jumlahInstruktur', 'jumlahPerusahaan'));
        })->name('dashboard');

        Route::get('/siswa', [AdminController::class, 'indexSiswa'])->name('siswa.index');
        Route::put('/siswa/mapping/{id}', [AdminController::class, 'updateMapping'])->name('siswa.mapping');
    });

    // ============================================================
    // 2. GURU PEMBIMBING
    // ============================================================
    Route::middleware(['role:guru_pembimbing'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', function () {
            $siswaBimbingan = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->count();
            return view('guru.dashboard', compact('siswaBimbingan'));
        })->name('dashboard');

        // Manajemen siswa bimbingan
        Route::get('/siswa', [GuruController::class, 'index'])->name('siswa.index');
        Route::get('/siswa/{id}/detail', [GuruController::class, 'detailSiswa'])->name('siswa.detail');

        // Observasi (guru: isi)
        Route::get('/observasi', [ObservasiController::class, 'indexGuru'])->name('observasi.index');
        Route::get('/observasi/create', [ObservasiController::class, 'createGuru'])->name('observasi.create');
        Route::post('/observasi', [ObservasiController::class, 'storeGuru'])->name('observasi.store');

        // Catatan (guru: pantau)
        Route::get('/catatan', [CatatanController::class, 'indexGuru'])->name('catatan.index');

        // Nilai (guru: rekap)
        Route::get('/nilai', [NilaiController::class, 'indexGuru'])->name('nilai.index');
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

        // Jurnal (siswa: isi)
        Route::get('/jurnal', [JurnalController::class, 'indexSiswa'])->name('jurnal.index');
        Route::get('/jurnal/tambah', [JurnalController::class, 'createSiswa'])->name('jurnal.create');
        Route::post('/jurnal', [JurnalController::class, 'storeSiswa'])->name('jurnal.store');
        Route::delete('/jurnal/{id}', [JurnalController::class, 'destroySiswa'])->name('jurnal.destroy');

        // Catatan (siswa: isi)
        Route::get('/catatan', [CatatanController::class, 'indexSiswa'])->name('catatan.index');
        Route::get('/catatan/create', [CatatanController::class, 'createSiswa'])->name('catatan.create');
        Route::post('/catatan', [CatatanController::class, 'storeSiswa'])->name('catatan.store');

        // Observasi (siswa: lihat)
        Route::get('/observasi', [ObservasiController::class, 'indexSiswa'])->name('observasi.index');

        // Nilai (siswa: lihat)
        Route::get('/nilai', [NilaiController::class, 'indexSiswa'])->name('nilai.index');

        // Dokumen (siswa: upload)
        Route::get('/dokumen', [DokumenSiswaController::class, 'index'])->name('dokumen.index');
        Route::post('/dokumen', [DokumenSiswaController::class, 'store'])->name('dokumen.store');

        // Cetak PDF (siswa)
        Route::get('/cetak-catatan', [CetakPdfController::class, 'cetakCatatan'])->name('cetak.catatan');
        Route::get('/cetak-observasi', [CetakPdfController::class, 'cetakObservasi'])->name('cetak.observasi');
        Route::get('/cetak-nilai', [CetakPdfController::class, 'cetakNilai'])->name('cetak.nilai');
    });

    // ============================================================
    // 4. INSTRUKTUR INDUSTRI
    // ============================================================
    Route::middleware(['role:instruktur_industri'])->prefix('instruktur')->name('instruktur.')->group(function () {
        Route::get('/dashboard', function () {
            $siswaBimbingan = User::where('role', 'siswa_pkl')->where('instruktur_id', Auth::id())->count();
            $siswaIds       = User::where('instruktur_id', Auth::id())->pluck('id');
            $jurnalPending  = Jurnal::whereIn('siswa_id', $siswaIds)->where('status_persetujuan', 'pending')->count();
            return view('instruktur.dashboard', compact('siswaBimbingan', 'jurnalPending'));
        })->name('dashboard');

        // Jurnal (instruktur: persetujuan)
        Route::get('/jurnal', [JurnalController::class, 'indexInstruktur'])->name('jurnal.index');
        Route::put('/jurnal/{id}/update', [JurnalController::class, 'updateInstruktur'])->name('jurnal.update');

        // Absensi (instruktur: isi)
        Route::get('/absensi', [AbsensiController::class, 'indexInstruktur'])->name('absensi.index');
        Route::post('/absensi', [AbsensiController::class, 'storeInstruktur'])->name('absensi.store');

        // Catatan (instruktur: persetujuan)
        Route::get('/catatan', [CatatanController::class, 'indexInstruktur'])->name('catatan.index');
        Route::put('/catatan/{id}/approve', [CatatanController::class, 'approveInstruktur'])->name('catatan.approve');

        // Observasi (instruktur: persetujuan)
        Route::get('/observasi', [ObservasiController::class, 'indexInstruktur'])->name('observasi.index');
        Route::put('/observasi/{id}/approve', [ObservasiController::class, 'approveInstruktur'])->name('observasi.approve');

        // Nilai (instruktur: isi)
        Route::get('/nilai', [NilaiController::class, 'indexInstruktur'])->name('nilai.index');
        Route::get('/nilai/create', [NilaiController::class, 'createInstruktur'])->name('nilai.create');
        Route::post('/nilai', [NilaiController::class, 'storeInstruktur'])->name('nilai.store');
    });

});

require __DIR__ . '/auth.php';