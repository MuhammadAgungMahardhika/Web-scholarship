<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if (Auth::check()) {
            // Cek apakah permintaan menginginkan JSON
            if ($request->wantsJson()) {
                return response()->json(['two_factor' => false]);
            }
            $user = Auth::user();

            // --- PERBAIKAN DI SINI ---
            // Cek apakah pengguna memiliki SETIDAKNYA SATU peran (asumsi model User memiliki relasi 'roles')
            if ($user->roles()->exists()) {
                return redirect()->to('scholarship'); // Redirect ke dashboard Filament/LIMS
            }
            // ------------------------

            // Jika pengguna tidak memiliki role yang sesuai (meskipun terautentikasi)
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda tidak memiliki peran akses yang valid. Silakan hubungi administrator.'
            ]);
        }
        // Jika pengguna tidak terautentikasi
        return redirect()->route('login');
    }
}
