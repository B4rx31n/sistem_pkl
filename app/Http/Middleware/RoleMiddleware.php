<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            // Untuk request data (POST/PUT/DELETE) redirect justru membuat
            // kesan "berhasil" — kembalikan 403.
            if ($request->expectsJson() || !$request->isMethod('GET')) {
                abort(403, 'Unauthorized access.');
            }

            return redirect('login');
        }

        $user = Auth::user();

        // Cek apakah user memiliki salah satu role yang diizinkan
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Akses ditolak: 403 untuk semua metode.
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
