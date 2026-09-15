<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared-secret check for server-to-server calls.
 *
 * The fleet's maintenance state should not be public, but a full OAuth setup is
 * more than two servers under the same owner need, so a static token is used.
 */
class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.integration.token');

        if (empty($expected)) {
            return response()->json([
                'error' => 'API access is not configured.',
            ], 503);
        }

        $provided = $request->bearerToken() ?: $request->header('X-Api-Token');

        // Constant-time comparison so the token cannot be guessed by timing.
        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json([
                'error' => 'Invalid or missing API token.',
            ], 401);
        }

        return $next($request);
    }
}
