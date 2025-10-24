<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePartnerApiScope
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string ...$scopes): Response
    {
        if ($scopes === []) {
            return $next($request);
        }

        $pipeline = (string) $request->attributes->get('partner_api_pipeline', 'modern');
        $apiKey = $request->attributes->get('partner_api_key');

        if (! $apiKey instanceof ApiKey) {
            // Exit early with an unauthorized response when the middleware pipeline lacks a key.
            $message = $pipeline === 'legacy'
                ? 'Unauthorized.'
                : 'Missing partner API key.';

            return $this->reject($message, Response::HTTP_UNAUTHORIZED);
        }

        $normalizedScopes = array_values($scopes);

        if (! $apiKey->hasAnyScope($normalizedScopes)) {
            // Communicate that the key exists but does not satisfy the requested scope set.
            $message = $pipeline === 'legacy'
                ? 'Forbidden.'
                : 'Insufficient partner API permissions.';

            return $this->reject($message, Response::HTTP_FORBIDDEN);
        }

        $request->attributes->set('partner_api_required_scopes', $normalizedScopes);

        return $next($request);
    }

    private function reject(string $message, int $status): Response
    {
        // Mirror the API response contract consumed by partner integrations.
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
