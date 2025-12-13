<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToRegister
{
    /**
     * Handle an incoming request.
     * Redirects unauthenticated users to register instead of login.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('register')
                ->with('info', 'Create an account to use Rattehin and claim your 10% discount at Micro Moto Garage!');
        }

        return $next($request);
    }
}
