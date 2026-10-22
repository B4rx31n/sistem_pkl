<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\NormalizeInput;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        // api: __DIR__.'/../routes/api.php', // Comment dulu jika tidak pakai API
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Header keamanan untuk SEMUA respons (termasuk halaman error).
        $middleware->append(SecurityHeaders::class);

        // Buang parameter query berbentuk array (semua filter bernilai tunggal).
        $middleware->append(NormalizeInput::class);

        // Paksa HTTPS + cookie Secure saat diakses lewat HTTPS (tunnel/proxy).
        $middleware->append(ForceHttps::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
