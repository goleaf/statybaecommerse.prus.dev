<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use App\Support\RequestContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PingController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Expose the resolved scopes so integrations can verify their key provisioning at a glance.
        $grantedScopes = array_values((array) $request->attributes->get('partner_api_abilities', []));

        // Mirror the scope requirements that were enforced by middleware for better observability.
        $requiredScopes = array_values((array) $request->attributes->get('partner_api_required_scopes', []));

        // Capture a traceable correlation identifier to help partners align requests with support tickets.
        $correlationId = RequestContext::resolveTraceId($request);

        // Build a structured heartbeat payload that surfaces runtime metadata alongside a success indicator.
        return response()->json([
            'data' => [
                'status'      => 'ok',
                'message'     => 'Partner API is available.',
                'timestamp'   => now()->toIso8601String(),
                'environment' => app()->environment(),
                'version'     => config('contracts.version', 'v1'),
            ],
            'meta' => [
                'scopes'          => $grantedScopes,
                'required_scopes' => $requiredScopes,
                'correlation_id'  => $correlationId,
            ],
        ]);
    }
}
