<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyesuaikan aplikasi agar aman saat diakses lewat HTTPS
 * (mis. di belakang tunnel Cloudflare) tanpa merusak akses lokal.
 *
 * - Bila permintaan sebenarnya HTTPS: paksa URL & cookie sesi memakai Secure.
 * - Bila FORCE_HTTPS=true dan permintaan masih HTTP (host non-lokal):
 *   alihkan (301) ke HTTPS.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        $isLocal = $this->isLocalHost($request->getHost());
        $isHttps = $this->isHttps($request);

        if (! $isLocal && $isHttps) {
            // URL yang di-generate aplikasi harus https agar tidak mixed-content.
            URL::forceScheme('https');

            // Cookie sesi otomatis memakai flag Secure saat memang HTTPS,
            // tapi tetap longgar saat diakses lokal via http.
            config(['session.secure' => true]);
        }

        if (config('app.force_https') && ! $isLocal && ! $isHttps) {
            return redirect()->to(
                'https://' . $request->getHttpHost() . $request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }

    private function isLocalHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.localhost');
    }

    private function isHttps(Request $request): bool
    {
        if ($request->secure()) {
            return true;
        }

        $proto = $request->header('X-Forwarded-Proto');

        return is_string($proto) && str_contains(strtolower($proto), 'https');
    }
}
