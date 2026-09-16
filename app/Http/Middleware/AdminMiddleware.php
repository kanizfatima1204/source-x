<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->isAdmin()) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}