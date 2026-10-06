<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Only allow users with an active role of the given name, e.g. `role:Instructeur`.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check() || !auth()->user()->roles()->where('name', $role)->where('is_active', true)->exists()) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
