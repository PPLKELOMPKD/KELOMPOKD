<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Ensure the authenticated user has one of the required roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $role = $this->resolveUserRole($user);

        abort_if(
            ! $user || ($roles !== [] && ! in_array($role, $roles, true)),
            Response::HTTP_FORBIDDEN
        );

        return $next($request);
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
