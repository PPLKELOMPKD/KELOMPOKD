<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user()?->loadMissing('mahasiswaProfile');
        $authUser = $user?->only(['id', 'name', 'email', 'role']);

        if ($user) {
            $authUser['profile_photo_url'] = $user->mahasiswaProfile?->photo_path
                ? '/storage/'.$user->mahasiswaProfile->photo_path
                : null;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $authUser,
            ],
            'notifications' => [
                'unreadCount' => $user ? $user->notificationsFeed()->whereNull('read_at')->count() : 0,
            ],
            'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
        ];
    }
}
