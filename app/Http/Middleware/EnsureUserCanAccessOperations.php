<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessOperations
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canAccessOperations()) {
            // Redirect customers to Rattehin instead of showing 403
            if ($request->user() && $request->user()->isCustomer()) {
                return redirect()->route('rattehin.index')
                    ->with('error', 'You do not have permission to access that page.');
            }

            abort(403, 'You do not have permission to access the operations module.');
        }

        return $next($request);
    }
}
