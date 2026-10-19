<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginIndustriController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login_industri');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Batasi percobaan login per IP+identitas (anti brute-force)
        $throttleKey = 'login-industri:' . md5(strtolower($request->email) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = \App\Models\User::where('email', $request->email)
            ->where('role', 'industri')
            ->first();

        // Pesan error diseragamkan agar email valid/tidak tidak bisa dibedakan
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('login_mode', 'regular');

        return redirect()->intended(route('industri.dashboard'));
    }
}
