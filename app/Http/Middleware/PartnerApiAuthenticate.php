<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PartnerApiAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = config('services.partner_api.key_header', 'X-Partner-Key');
        $secretHeader = config('services.partner_api.secret_header', 'X-Partner-Secret');

        $keyValue = $request->header($header) ?? $request->bearerToken();
        $secretValue = $request->header($secretHeader);

        if (! is_string($keyValue) || $keyValue === '') {
            return $this->unauthorizedResponse();
        }

        $query = ApiKey::query()->where('key', $keyValue)->where('is_active', true);

        if ($secretValue !== null) {
            $query->where(function ($builder) use ($secretValue): void {
                $builder->whereNull('secret')->orWhere('secret', $secretValue);
            });
        }

        /** @var ApiKey|null $apiKey */
        $apiKey = $query->first();

        if (! $apiKey || ($apiKey->expires_at && $apiKey->expires_at->isPast())) {
            return $this->unauthorizedResponse();
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('partner_api_key', $apiKey);

        return $next($request);
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthorized.',
        ], 401);
    }
}
