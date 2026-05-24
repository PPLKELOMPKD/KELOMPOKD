<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class CheckDynamicSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Cache::rememberForever('global_settings', function () {
            if (!Schema::hasTable('settings')) {
                return [];
            }
            return Setting::all()->pluck('value', 'key')->toArray();
        });

        // Mode Pemeliharaan (Maintenance Mode)
        if (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === 'true') {
            if (\Illuminate\Support\Facades\Auth::check()) {
                if (!\Illuminate\Support\Facades\Auth::user()->isAdmin() && !$request->routeIs('logout')) {
                    return response()->view('errors.503', [], 503);
                }
            }
        }

        // Session Lifetime
        if (isset($settings['session_lifetime'])) {
            Config::set('session.lifetime', (int) $settings['session_lifetime']);
        }

        return $next($request);
    }
}
