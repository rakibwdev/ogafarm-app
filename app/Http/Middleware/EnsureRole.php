<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side RBAC — spec §12: hidden UI never replaces API authorization.
 * Usage: ->middleware('role:farmer') or 'role:farmer,supplier'
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        foreach ($roles as $roleName) {
            if ($user->hasRole(Role::from($roleName))) {
                return $next($request);
            }
        }

        abort(403, 'Your account does not have access to this area.');
    }
}
