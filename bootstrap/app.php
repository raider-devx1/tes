<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    // Daftarkan alias middleware kustom Anda di sini
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);

    // CATATAN: middleware pencatat aktivitas (audit log) SUDAH DIHAPUS.
    // Sebelumnya setiap aksi simpan/ubah/hapus menulis 1 baris ke tabel
    // activity_logs. Di shared hosting hal itu membuat setiap penyimpanan
    // data melakukan query INSERT tambahan dan tabelnya membengkak terus,
    // sehingga database menjadi berat. Fitur Riwayat Aktivitas dihapus.
})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
