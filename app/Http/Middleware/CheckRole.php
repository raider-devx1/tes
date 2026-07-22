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
        $user = $request->user();

        // Belum login sama sekali.
        if (!$user) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        // Role user cocok dengan salah satu role yang diizinkan.
        $diizinkan = in_array($user->role, $roles);

        // Guru pembimbing yang ditetapkan admin boleh mengakses halaman admin.
        if (!$diizinkan && in_array('admin', $roles) && $user->is_admin) {
            $diizinkan = true;
        }

        if (!$diizinkan) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        return $next($request);
    }
}