<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'roles' => [
                ['value' => User::ROLE_MAHASISWA, 'label' => 'Mahasiswa'],
                ['value' => User::ROLE_PERUSAHAAN, 'label' => 'Perusahaan'],
                ['value' => User::ROLE_ADMIN, 'label' => 'Admin'],
            ],
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $settings = \Illuminate\Support\Facades\Cache::get('global_settings', []);
        
        if (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === 'true') {
            if ($request->role !== User::ROLE_ADMIN) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Sistem sedang dalam pemeliharaan. Hanya admin yang dapat login saat ini.',
                ]);
            }
        }

        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Log the login activity
        ActivityLogger::log(
            'Login',
            "Pengguna {$user->name} berhasil login ke sistem",
            'auth',
        );

        if ($user->isMahasiswa()) {
            return redirect()->intended(route('peserta', absolute: false));
        } elseif ($user->isPerusahaan()) {
            return redirect()->intended(route('perusahaan.dashboard', absolute: false));
        } elseif ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Log the logout activity before destroying the session
        if ($user) {
            ActivityLogger::log(
                'Logout',
                "Pengguna {$user->name} keluar dari sistem",
                'auth',
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
