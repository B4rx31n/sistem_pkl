<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Jangan izinkan hapus akun yang masih punya penempatan PKL —
        // mencegah riwayat PKL (jurnal, nilai) ikut terhapus.
        $hasPenempatan = ($user->guru && $user->guru->penempatan()->exists())
            || ($user->siswa && $user->siswa->penempatan()->exists())
            || ($user->industri && $user->industri->penempatan()->exists());

        if ($hasPenempatan) {
            return Redirect::route('profile.edit')
                ->with('error', 'Akun tidak dapat dihapus karena masih memiliki penempatan PKL. Hubungi admin terlebih dahulu.');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
