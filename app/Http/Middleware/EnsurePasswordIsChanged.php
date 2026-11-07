<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password
            && ! $request->routeIs('password.*', 'logout')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You must change your password before continuing.'], 403);
            }

            return redirect()->route('password.setup');
        }

        return $next($request);
    }
}
