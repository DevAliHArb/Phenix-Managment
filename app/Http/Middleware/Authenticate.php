<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null; // Don't redirect if expecting JSON
        }

        // Get the requested URL path
        $path = $request->path();

        if (strpos($path, 'api/bookshop/') === 0) {
            return route('bookshop.login');
        } elseif (strpos($path, 'api/lynx/') === 0) {
            return route('lynx.login');
        } else {
            return route('login'); // Default login route
        }
    }
}
