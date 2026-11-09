<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:  ->middleware(['auth:sanctum', 'role:admin,super_admin'])
 * Must run AFTER auth:sanctum, so $request->user() exists.
 */
class EnsureUserHasRole
{
  public function handle(Request $request, Closure $next, string ...$roles): Response
  {
    $user = $request->user();

    // Convert the route strings to enums. An unknown string (typo in routes)
    // becomes null and is removed, so a typo can never accidentally allow access.
    $allowed = array_filter(array_map(fn(string $r) => UserRole::tryFrom($r), $roles));

    if (! $user || ! $user->hasRole(...$allowed)) {
      abort(403, 'You are not allowed to perform this action.');
    }

    return $next($request);
  }
}
