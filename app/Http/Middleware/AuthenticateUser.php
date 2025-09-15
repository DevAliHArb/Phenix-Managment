<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            // If a bearer token is present, you can proceed with the request
            return $next($request);
        }

        // If no bearer token is present, return an unauthorized response
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
