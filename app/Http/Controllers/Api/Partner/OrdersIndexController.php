<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrdersIndexController
{
    public function __invoke(Request $request): JsonResponse
    {
        $abilities = $request->attributes->get('partner_api_abilities', []);

        return response()->json([
            'data' => [
                'orders' => [],
            ],
            'meta' => [
                'scopes' => array_values((array) $abilities),
            ],
        ]);
    }
}
