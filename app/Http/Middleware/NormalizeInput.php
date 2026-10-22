<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Netralkan parameter query yang berupa array.
 *
 * Aplikasi ini hanya memakai filter query bernilai tunggal (search, status,
 * tanggal, jurusan, redirect, dst). Bila penyerang mengirim bentuk array
 * (mis. ?search[]=x), nilai tersebut dibuang agar tidak memicu error 500
 * maupun perilaku tak terduga di view/controller.
 */
class NormalizeInput
{
    public function handle(Request $request, Closure $next): Response
    {
        foreach ($request->query->all() as $key => $value) {
            if (is_array($value)) {
                $request->query->remove($key);
            }
        }

        return $next($request);
    }
}
