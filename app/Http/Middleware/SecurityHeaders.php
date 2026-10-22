<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menambahkan header keamanan ke setiap respons dan
 * menyembunyikan penanda versi teknologi (X-Powered-By).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Sembunyikan versi PHP dari header (expose_php sering aktif di paket dev).
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        $response = $next($request);

        // Perlindungan dasar untuk semua halaman.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // CSP minimalis: hanya mengunci framing & object agar tidak mengganggu
        // inline script/style/CDN yang sudah dipakai aplikasi.
        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self'; object-src 'none'; base-uri 'self'"
        );

        $response->headers->remove('X-Powered-By');

        // HSTS hanya dikirim saat koneksi memang HTTPS.
        if ($this->isHttps($request)) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function isHttps(Request $request): bool
    {
        if ($request->secure()) {
            return true;
        }

        // Di belakang reverse proxy/tunnel (Cloudflare) skema asli ada di header ini.
        $proto = $request->header('X-Forwarded-Proto');

        return is_string($proto) && str_contains(strtolower($proto), 'https');
    }
}
