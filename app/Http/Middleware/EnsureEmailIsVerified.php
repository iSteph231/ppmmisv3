<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_verified) {
            return redirect()->route('otp.verification.form')
                           ->with('error', 'Please verify your email address first.');
        }
        
        return $next($request);
    }
}