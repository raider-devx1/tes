<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika user belum login, atau role user tidak ada di dalam daftar parameter yang diizinkan
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        return $next($request);
    }
}