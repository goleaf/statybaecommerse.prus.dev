<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use Illuminate\Http\JsonResponse;

final class InventoryController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'inventory' => [],
            ],
        ]);
    }
}
