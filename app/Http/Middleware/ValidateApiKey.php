<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.cai_lmm.api_key');

        if (empty($expectedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API service key is not configured on the server.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $providedKey = $request->header('X-API-KEY') ?: $request->bearerToken();

        if (!$providedKey || !hash_equals((string) $expectedKey, (string) $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing API key.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
