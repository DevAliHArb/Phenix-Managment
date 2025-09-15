<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApiEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->hasVerifiedEmail()) {
            return response()->json(['error' => 'Email not verified.'], 403);
        }

        return $next($request);
    }
}
