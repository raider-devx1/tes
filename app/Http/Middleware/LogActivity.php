<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /** Hanya aksi yang mengubah data yang dicatat. */
    private array $trackedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $userBefore = Auth::user();      // simpan (agar logout tetap terekam)

        $response = $next($request);

        $user = Auth::user() ?? $userBefore;

        if ($user && in_array($request->method(), $this->trackedMethods, true)) {
            ActivityLog::create([
                'user_id'     => $user->id,
                'description' => $this->describe($request),
                'method'      => $request->method(),
                'route_name'  => optional($request->route())->getName(),
                'url'         => $request->path(),
                'ip'          => $request->ip(),
            ]);
        }

        return $response;
    }

    /** Susun deskripsi aktivitas yang mudah dibaca (Bahasa Indonesia). */
    private function describe(Request $request): string
    {
        $routeName = optional($request->route())->getName();

        // Kasus khusus autentikasi
        if ($routeName === 'login')  return 'Login ke sistem';
        if ($routeName === 'logout') return 'Logout dari sistem';

        $verb = match ($request->method()) {
            'POST'         => 'Menambahkan',
            'PUT', 'PATCH' => 'Memperbarui',
            'DELETE'       => 'Menghapus',
            default        => 'Mengakses',
        };

        if (!$routeName) {
            return "{$verb} data ({$request->path()})";
        }

        $parts = explode('.', $routeName);

        // Verb khusus berdasarkan aksi terakhir
        $verb = match (end($parts)) {
            'approve'  => 'Menyetujui',
            'import'   => 'Mengimpor',
            'aktifkan' => 'Mengaktifkan',
            'upload'   => 'Mengunggah',
            'store'    => 'Menambahkan',
            'update'   => 'Memperbarui',
            'destroy'  => 'Menghapus',
            default    => $verb,
        };

        // Buang prefix role di depan
        $rolePrefixes = ['admin', 'guru', 'siswa', 'instruktur'];
        if (count($parts) > 1 && in_array($parts[0], $rolePrefixes, true)) {
            array_shift($parts);
        }

        // Buang suffix aksi di belakang
        $actions = ['store', 'update', 'destroy', 'approve', 'import', 'aktifkan', 'upload'];
        if (count($parts) > 1 && in_array(end($parts), $actions, true)) {
            array_pop($parts);
        }

        $objek = str_replace(['-', '_', '.'], ' ', implode(' ', $parts));

        return trim("{$verb} data {$objek}");
    }
}