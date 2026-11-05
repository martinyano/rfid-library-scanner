<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRfidApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('library.rfid_api_key');
        $providedKey = (string) ($request->header('X-RFID-Key') ?: $request->bearerToken());

        if ($configuredKey === '') {
            return response()->json([
                'message' => 'RFID API key is not configured on the server.',
            ], 503);
        }

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'Invalid or missing RFID API key.',
            ], 401);
        }

        return $next($request);
    }
}
