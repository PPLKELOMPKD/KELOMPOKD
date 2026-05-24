<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleLandingPage
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // If the user's role is not the intended role for this landing page,
            // redirect them to their respective dashboard
            if ($user->role !== $role) {
                if ($user->isMahasiswa()) {
                    return redirect()->route('peserta');
                } elseif ($user->isPerusahaan()) {
                    return redirect()->route('perusahaan.dashboard');
                } elseif ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }
                
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
