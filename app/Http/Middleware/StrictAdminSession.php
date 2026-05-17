<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StrictAdminSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            // Logika tambahan untuk keamanan admin bisa ditambahkan di sini
            // Contoh: Mengecek apakah IP berubah, atau session lifetime yang lebih pendek
            
            // Kita bisa menambahkan header keamanan tambahan khusus untuk Admin
            $response = $next($request);
            $response->headers->set('X-Admin-Security-Level', 'High');
            return $response;
        }

        return $next($request);
    }
}
