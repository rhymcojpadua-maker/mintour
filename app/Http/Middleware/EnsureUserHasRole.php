<?php
namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage:  ->middleware('role:admin')          // any admin-level role
     *         ->middleware('role:super_admin')    // only super admins
     *         ->middleware('role:admin,staff')    // admin OR staff
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Account is inactive or not authenticated.');
        }

        // Special short-hand: 'admin' means any admin-level role
        if (in_array('admin', $roles, true) && $user->role->canAccessAdmin()) {
            return $next($request);
        }

        $allowed = array_map(fn ($r) => Role::from($r), $roles);
        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
