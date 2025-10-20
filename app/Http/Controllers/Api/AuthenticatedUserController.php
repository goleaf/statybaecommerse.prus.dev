<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShowAuthenticatedUserRequest;
use Illuminate\Http\JsonResponse;

final class AuthenticatedUserController extends Controller
{
    public function __invoke(ShowAuthenticatedUserRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}
