<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePartnerApiKey
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string ...$requiredScopes): Response
    {
        $providedKey = $this->resolveProvidedKey($request);

        if ($providedKey === null) {
            // Return an explicit JSON error response so downstream handlers see the expected status code.
            return $this->reject('Missing partner API key.', Response::HTTP_UNAUTHORIZED);
        }

        $apiKey = $this->findActiveKey($providedKey);

        if ($apiKey === null) {
            // Stop processing when the provided key cannot be matched to an active record.
            return $this->reject('Invalid or inactive partner API key.', Response::HTTP_FORBIDDEN);
        }

        $normalizedScopes = array_values($requiredScopes);

        if ($normalizedScopes !== [] && ! $apiKey->hasAnyScope($normalizedScopes)) {
            // Surface a forbidden response whenever the key lacks the required scopes.
            return $this->reject('Insufficient partner API permissions.', Response::HTTP_FORBIDDEN);
        }

        $apiKey->forceFill(['last_used_at' => now()])->saveQuietly();

        $abilities = $apiKey->resolvedScopes();

        $request->attributes->set('partner_api_key', $apiKey);
        $request->attributes->set('partner_api_abilities', $abilities);
        $request->attributes->set('partner_api_pipeline', 'modern');

        if ($normalizedScopes !== []) {
            $request->attributes->set('partner_api_required_scopes', $normalizedScopes);
        }

        return $next($request);
    }

    private function resolveProvidedKey(Request $request): ?string
    {
        $configuredHeader = config('services.partner_api.header', 'X-Api-Key');
        $headerName = is_string($configuredHeader) && $configuredHeader !== ''
            ? $configuredHeader
            : 'X-Api-Key';
        $header = $request->headers->get($headerName);

        if (! is_string($header)) {
            return null;
        }

        $trimmed = trim($header);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function findActiveKey(string $plainText): ?ApiKey
    {
        $hashed = ApiKey::hashKey($plainText);

        /** @var ApiKey|null $apiKey */
        $apiKey = ApiKey::query()
            ->where('key', $hashed)
            ->where(function ($query): void {
                $query->where('is_active', true)->orWhere('active', true);
            })
            ->first();

        return $apiKey;
    }

    private function reject(string $message, int $status): Response
    {
        // Build a small JSON payload to keep client assertions deterministic.
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
