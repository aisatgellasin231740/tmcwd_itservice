<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * If the authenticated user has must_change_password = true,
     * redirect them to the mandatory change-password screen.
     *
     * Exemptions (routes that don't trigger this redirect):
     *  - The change-password screen itself (infinite loop prevention)
     *  - The logout route
     *  - Any Breeze password/auth routes
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->must_change_password
            && ! $request->routeIs('password.force-change')
            && ! $request->routeIs('password.force-change.update')
            && ! $request->routeIs('logout')
        ) {
            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
