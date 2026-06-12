<?php

namespace App\Http\Middleware;

use App\Models\QsApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QsApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $rawKey = $request->header('X-API-Key')
               ?? $request->bearerToken()
               ?? $request->query('api_key');

        if (!$rawKey) {
            return response()->json([
                'error' => 'API key required. Pass it as X-API-Key header.',
            ], 401);
        }

        $keyRecord = QsApiKey::findByRawKey($rawKey);

        if (!$keyRecord) {
            return response()->json([
                'error' => 'Invalid or inactive API key.',
            ], 403);
        }

        // Update last used timestamp
        $keyRecord->update(['last_used_at' => now()]);

        return $next($request);
    }
}
