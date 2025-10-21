<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use Illuminate\Http\JsonResponse;

final class PingController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'Partner API is available.',
        ]);
    }
}
