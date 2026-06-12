<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CetakPdfController;
use App\Http\Controllers\DokumenSiswaController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\InstrukturController;
use App\Http\Controllers\JurnalSiswaController;
use App\Http\Controllers\ProfileController;
use App\Models\Jurnal;
use App\Models\Perusahaan;

// Import Model untuk kebutuhan statistik di Dashboard
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return view('welcome');});

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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cetak/jurnal/{siswa_id}', [CetakPdfController::class, 'cetakJurnal'])->name('cetak.jurnal');
    Route::get('/cetak/nilai/{siswa_id}', [CetakPdfController::class, 'cetakNilai'])->name('cetak.nilai');

    // 1. ADMIN
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

    // 2. GURU PEMBIMBING
    Route::middleware(['role:guru_pembimbing'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', function () {
            $siswaBimbingan = User::where('role', 'siswa_pkl')->where('guru_id', Auth::id())->count();
            return view('guru.dashboard', compact('siswaBimbingan'));
        })->name('dashboard');

        Route::get('/siswa', [GuruController::class, 'index'])->name('siswa.index');
        Route::get('/siswa/{id}/detail', [GuruController::class, 'detailSiswa'])->name('siswa.detail');
        // RUTE OBSERVASI LAMA DIHAPUS AGAR TIDAK BENTROK
    });

    // 3. SISWA
    Route::middleware(['role:siswa_pkl'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', function () {
            $jumlahJurnal    = Jurnal::where('siswa_id', Auth::id())->count();
            $jurnalDisetujui = Jurnal::where('siswa_id', Auth::id())->where('status_persetujuan', 'disetujui')->count();
            return view('siswa.dashboard', compact('jumlahJurnal', 'jurnalDisetujui'));
        })->name('dashboard');

        Route::get('/jurnal', [JurnalSiswaController::class, 'index'])->name('jurnal.index');
        Route::get('/jurnal/tambah', [JurnalSiswaController::class, 'create'])->name('jurnal.create');
        Route::post('/jurnal', [JurnalSiswaController::class, 'store'])->name('jurnal.store');
        Route::delete('/jurnal/{id}', [JurnalSiswaController::class, 'destroy'])->name('jurnal.destroy');

        Route::get('/dokumen', [DokumenSiswaController::class, 'index'])->name('dokumen.index');
        Route::post('/dokumen', [DokumenSiswaController::class, 'store'])->name('dokumen.store');
    });

    // 4. INSTRUKTUR
    Route::middleware(['role:instruktur_industri'])->prefix('instruktur')->name('instruktur.')->group(function () {
        Route::get('/dashboard', function () {
            $siswaBimbingan = User::where('role', 'siswa_pkl')->where('instruktur_id', Auth::id())->count();
            $siswaIds       = User::where('instruktur_id', Auth::id())->pluck('id');
            $jurnalPending  = Jurnal::whereIn('siswa_id', $siswaIds)->where('status_persetujuan', 'pending')->count();
            return view('instruktur.dashboard', compact('siswaBimbingan', 'jurnalPending'));
        })->name('dashboard');

        Route::get('/jurnal', [InstrukturController::class, 'jurnalIndex'])->name('jurnal.index');
        Route::put('/jurnal/{id}/update', [InstrukturController::class, 'jurnalUpdate'])->name('jurnal.update');
        Route::get('/absensi', [InstrukturController::class, 'absensiIndex'])->name('absensi.index');
        Route::post('/absensi', [InstrukturController::class, 'absensiStore'])->name('absensi.store');

        Route::get('/nilai', [InstrukturController::class, 'nilaiIndex'])->name('nilai.index');
        Route::post('/nilai', [InstrukturController::class, 'nilaiStore'])->name('nilai.store');
    });

    // --- GRUP ROUTE CATATAN ---
    Route::middleware(['auth', 'role:siswa_pkl'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::resource('catatan', App\Http\Controllers\CatatanSiswaController::class)->only(['index', 'create', 'store']);
        Route::get('/cetak-catatan', [App\Http\Controllers\CetakPdfController::class, 'cetakCatatan'])->name('cetak.catatan');
    });
    Route::middleware(['auth', 'role:guru_pembimbing'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/catatan', [App\Http\Controllers\CatatanGuruController::class, 'index'])->name('catatan.index');
    });
    Route::middleware(['auth', 'role:instruktur_industri'])->prefix('instruktur')->name('instruktur.')->group(function () {
        Route::get('/catatan', [App\Http\Controllers\CatatanInstrukturController::class, 'index'])->name('catatan.index');
        Route::put('/catatan/{id}/approve', [App\Http\Controllers\CatatanInstrukturController::class, 'approve'])->name('catatan.approve');
    });

    // --- GRUP ROUTE OBSERVASI ---
    Route::middleware(['auth', 'role:guru_pembimbing'])->prefix('guru')->name('guru.')->group(function () {
        Route::resource('observasi', App\Http\Controllers\ObservasiGuruController::class)->except(['show']);
    });
    Route::middleware(['auth', 'role:siswa_pkl'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/observasi', [App\Http\Controllers\ObservasiSiswaController::class, 'index'])->name('observasi.index');
        Route::get('/cetak-observasi', [App\Http\Controllers\CetakPdfController::class, 'cetakObservasi'])->name('cetak.observasi');
    });
    Route::middleware(['auth', 'role:instruktur_industri'])->prefix('instruktur')->name('instruktur.')->group(function () {
        Route::get('/observasi', [App\Http\Controllers\ObservasiInstrukturController::class, 'index'])->name('observasi.index');
        Route::put('/observasi/{id}/approve', [App\Http\Controllers\ObservasiInstrukturController::class, 'approve'])->name('observasi.approve');
    });

// Rute khusus Peran Instruktur Industri (Input Data & Kelola Penilaian)
    Route::middleware(['auth', 'role:instruktur_industri'])->prefix('instruktur')->name('instruktur.')->group(function () {
        Route::resource('nilai', App\Http\Controllers\NilaiController::class)->except(['show']);
    });

// Rute khusus Peran Siswa (Lihat Nilai & Unduh Cetak Dokumen)
    Route::middleware(['auth', 'role:siswa_pkl'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/nilai', [App\Http\Controllers\NilaiController::class, 'siswaIndex'])->name('nilai.index');
        Route::get('/cetak-nilai', [App\Http\Controllers\CetakPdfController::class, 'cetakNilai'])->name('cetak.nilai');
    });

// Rute khusus Peran Guru Pembimbing (Monitoring Rekapitulasi Nilai)
    Route::middleware(['auth', 'role:guru_pembimbing'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/nilai', [App\Http\Controllers\NilaiController::class, 'guruIndex'])->name('nilai.index');
    });

});

require __DIR__ . '/auth.php';
