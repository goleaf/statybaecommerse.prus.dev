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
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('partner_api_key');

        if (! $apiKey instanceof ApiKey) {
            return $this->forbiddenResponse();
        }

        $granted = collect($scopes)
            ->filter(fn ($scope) => $scope !== '')
            ->every(fn ($scope) => in_array($scope, $apiKey->permissions ?? [], true));

        if (! $granted) {
            return $this->forbiddenResponse();
        }

        return $next($request);
    }

    private function forbiddenResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Forbidden.',
        ], 403);
    }
}
