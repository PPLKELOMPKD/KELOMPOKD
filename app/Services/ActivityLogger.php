<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity.
     *
     * Automatically detects the user's role from the authenticated session.
     * Falls back to 'system' if no user is authenticated.
     *
     * @param string      $action      Short action label (e.g., "Login", "Membuat Lowongan")
     * @param string|null $description Detailed human-readable description
     * @param string|null $category    Category grouping (e.g., "auth", "lowongan", "event", "lms", "admin", "profile")
     * @param int|null    $userId      Override user ID (for system-triggered logs)
     * @param string|null $role        Override role (for unauthenticated contexts like registration)
     * @return void
     */
    public static function log(
        string  $action,
        ?string $description = null,
        ?string $category = null,
        ?int    $userId = null,
        ?string $role = null,
    ): void {
        $user = auth()->user();

        ActivityLog::create([
            'user_id'    => $userId ?? $user?->id,
            'role'       => $role ?? $user?->role ?? 'system',
            'action'     => $action,
            'category'   => $category,
            'description'=> $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
