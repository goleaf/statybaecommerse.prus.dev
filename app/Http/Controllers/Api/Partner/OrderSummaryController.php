<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use Illuminate\Http\JsonResponse;

final class OrderSummaryController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'orders' => [],
            ],
        ]);
    }
}
