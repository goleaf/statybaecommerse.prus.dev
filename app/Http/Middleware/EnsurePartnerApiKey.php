<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        if ($normalizedScopes !== []) {
            $request->attributes->set('partner_api_required_scopes', $normalizedScopes);
        }

        return $next($request);
    }

    private function resolveProvidedKey(Request $request): ?string
    {
        // Attempt to resolve the key from every supported header name so both legacy and
        // current partner integrations continue to authenticate without configuration tweaks.
        foreach ($this->resolveHeaderNames() as $headerName) {
            $header = $request->headers->get($headerName);

            if (! is_string($header)) {
                continue;
            }

            $trimmed = trim($header);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    private function findActiveKey(string $plainText): ?ApiKey
    {
        $candidates = Collection::make([
            $plainText,
            // Hash the provided key so plain text values can authenticate against stored digests.
            ApiKey::hashKey($plainText),
        ])
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->unique()
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        /** @var ApiKey|null $apiKey */
        $apiKey = ApiKey::query()
            ->whereIn('key', $candidates->all())
            ->where(function ($query): void {
                $query->where('is_active', true)->orWhere('active', true);
            })
            ->first();

        return $apiKey;
    }

    /**
     * @return array<int, string>
     */
    private function resolveHeaderNames(): array
    {
        return Collection::make([
            $this->normalizeHeaderName(config('services.partner_api.header', 'X-Api-Key')),
            $this->normalizeHeaderName(config('services.partner_api.key_header', 'X-Partner-Key')),
        ])
            ->filter(static fn (?string $value): bool => $value !== null)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeHeaderName(null|string $candidate): ?string
    {
        if (! is_string($candidate)) {
            return null;
        }

        $trimmed = trim($candidate);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function reject(string $message, int $status): Response
    {
        // Build a small JSON payload to keep client assertions deterministic.
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
