<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePartnerApiScope
{
    public function handle(Request $request, Closure $next, string ...$scopes): Response
    {
        if ($scopes === []) {
            return $next($request);
        }

        $apiKey = $request->attributes->get('partner_api_key');

        if (! $apiKey instanceof ApiKey) {
            return $this->reject('Missing partner API key.', Response::HTTP_UNAUTHORIZED);
        }

        if (! $apiKey->hasAnyScope($scopes)) {
            return $this->reject('Insufficient partner API permissions.', Response::HTTP_FORBIDDEN);
        }

        $request->attributes->set('partner_api_required_scopes', array_values($scopes));

        return $next($request);
    }

    /**
     * Return a consistent JSON structure when scope validation fails.
     */
    private function reject(string $message, int $status): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
