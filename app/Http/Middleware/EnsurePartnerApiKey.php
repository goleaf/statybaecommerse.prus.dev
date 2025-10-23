<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePartnerApiKey
{
    private const API_KEY_HEADER = 'X-Api-Key';

    /**
     * Handle an incoming partner API request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->registerRequestMacros();

        $keyValue = trim((string) $request->header(self::API_KEY_HEADER, ''));

        if ($keyValue === '') {
            return response()->json([
                'message' => 'Missing partner API key.',
            ], 401);
        }

        $apiKey = ApiKey::query()
            ->active()
            ->where('key', $keyValue)
            ->first();

        if (! $apiKey || $apiKey->isExpired()) {
            return response()->json([
                'message' => 'The provided API key is invalid or inactive.',
            ], 403);
        }

        $apiKey->markAsUsed();

        $abilities = $apiKey->resolvedAbilities();

        $request->attributes->set('partner.api_key', $apiKey);
        $request->attributes->set('partner.api_abilities', $abilities);

        return $next($request);
    }

    private function registerRequestMacros(): void
    {
        if (! Request::hasMacro('partnerApiKey')) {
            Request::macro('partnerApiKey', static function (): ?ApiKey {
                /** @var Request $this */
                return $this->attributes->get('partner.api_key');
            });
        }

        if (! Request::hasMacro('partnerApiAbilities')) {
            Request::macro('partnerApiAbilities', static function (): array {
                /** @var Request $this */
                return $this->attributes->get('partner.api_abilities', []);
            });
        }

        if (! Request::hasMacro('partnerCan')) {
            Request::macro('partnerCan', static function (string $ability): bool {
                /** @var Request $this */
                $abilities = $this->partnerApiAbilities();

                return \in_array('*', $abilities, true) || \in_array($ability, $abilities, true);
            });
        }
    }
}
