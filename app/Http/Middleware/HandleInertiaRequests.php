<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => data_get($user, 'id'),
                    'name' => data_get($user, 'name'),
                    'email' => data_get($user, 'email'),
                    'role' => $this->resolveUserRole($user),
                ] : null,
            ],
            'notifications' => [
                'unreadCount' => 0,
            ],
        ];
    }

    /**
     * Resolve the user's role once the role column exists.
     */
    protected function resolveUserRole(mixed $user): mixed
    {
        if (! $user || ! method_exists($user, 'getTable') || ! method_exists($user, 'getAttribute')) {
            return null;
        }

        if (! Schema::hasColumn($user->getTable(), 'role')) {
            return null;
        }

        return $user->getAttribute('role');
    }
}
