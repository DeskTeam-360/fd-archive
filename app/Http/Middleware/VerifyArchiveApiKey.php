<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyArchiveApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.archive_api.key');

        // No key configured means the API stays closed, never open.
        if ($expected === '') {
            return response()->json(['error' => 'API is not configured'], 503);
        }

        $given = (string) ($request->header('X-API-Key') ?: $request->bearerToken());

        if ($given === '' || ! hash_equals($expected, $given)) {
            return response()->json(['error' => 'Invalid or missing API key'], 401);
        }

        return $next($request);
    }
}
