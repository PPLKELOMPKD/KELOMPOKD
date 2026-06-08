<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan user perusahaan sudah diverifikasi oleh admin (status = active)
 * sebelum dapat mengakses menu-menu utama (Dashboard, Pelamar, Lowongan, dsb).
 * 
 * Jika belum diverifikasi, diarahkan ke halaman pending verification.
 */
class EnsureCompanyIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPerusahaan() && $user->status !== 'active') {
            // Arahkan ke halaman pending verifikasi admin
            return redirect()->route('perusahaan.pending-verification');
        }

        return $next($request);
    }
}
