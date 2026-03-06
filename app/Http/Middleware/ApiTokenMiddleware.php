<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('app.openclaw_api_token');

        if (empty($configuredToken)) {
            return response()->json(['error' => 'API token not configured on server.'], 500);
        }

        $provided = $request->bearerToken();

        if (!$provided || !hash_equals($configuredToken, $provided)) {
            return response()->json(['error' => 'Unauthorized. Invalid or missing API token.'], 401);
        }

        return $next($request);
    }
}
