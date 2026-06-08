<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403);
        }

        // Hanya banned yang di-logout secara paksa.
        // Perusahaan inactive (pending verifikasi admin) boleh masuk ke areanya sendiri,
        // aksesnya dibatasi oleh middleware EnsureCompanyIsVerified di route-route tertentu.
        if ($request->user() && $request->user()->status === 'banned') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors(['email' => 'Akun Anda telah diblokir karena pelanggaran. Silakan hubungi administrator.']);
        }

        return $next($request);
    }
}
