<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePartnerApiKey
{
    public function handle(Request $request, Closure $next, string ...$requiredScopes): Response
    {
        $providedKey = $this->resolveProvidedKey($request);

        if ($providedKey === null) {
            $this->reject('Missing partner API key.', Response::HTTP_UNAUTHORIZED);
        }

        $apiKey = $this->findActiveKey($providedKey);

        if ($apiKey === null) {
            $this->reject('Invalid or inactive partner API key.', Response::HTTP_FORBIDDEN);
        }

        if ($requiredScopes !== [] && ! $apiKey->hasAnyScope($requiredScopes)) {
            $this->reject('Insufficient partner API permissions.', Response::HTTP_FORBIDDEN);
        }

        $apiKey->forceFill(['last_used_at' => now()])->saveQuietly();

        $abilities = $apiKey->resolvedScopes();

        $request->attributes->set('partner_api_key', $apiKey);
        $request->attributes->set('partner_api_abilities', $abilities);

        if ($requiredScopes !== []) {
            $request->attributes->set('partner_api_required_scopes', array_values($requiredScopes));
        }

        return $next($request);
    }

    private function resolveProvidedKey(Request $request): ?string
    {
        $headerName = (string) config('services.partner_api.header', 'X-Api-Key');
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
            ->where('active', true)
            ->first();

        return $apiKey;
    }

    private function reject(string $message, int $status): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => $message,
            ], $status)
        );
    }
}
