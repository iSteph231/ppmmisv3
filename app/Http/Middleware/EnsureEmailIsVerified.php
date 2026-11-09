<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && ! auth()->user()->is_verified) {
            return redirect()->route(auth()->user()->must_change_password ? 'password.setup' : 'password.request')
                ->with('error', 'Please use your password setup link to confirm your email address.');
        }

        return $next($request);
    }
}
