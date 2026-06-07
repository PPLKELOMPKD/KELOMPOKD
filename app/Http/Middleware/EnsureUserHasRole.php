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

        if ($request->user() && $request->user()->role !== 'admin' && in_array($request->user()->status, ['inactive', 'banned'])) {
            $status = $request->user()->status;
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $status === 'banned' 
                ? 'Akun Anda telah diblokir karena pelanggaran. Silakan hubungi administrator.'
                : 'Akun Anda saat ini dinonaktifkan (pending verifikasi). Silakan hubungi administrator.';

            return redirect('/login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
