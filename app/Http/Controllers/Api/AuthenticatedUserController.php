<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShowAuthenticatedUserRequest;
use App\Support\Contracts\Entities\UserContract;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;

final class AuthenticatedUserController extends Controller
{
    use HandlesContentNegotiation;

    public function __invoke(ShowAuthenticatedUserRequest $request): JsonResponse
    {
        $payload = UserContract::forUser($request->user());

        return $this->respondWithContract($request, $payload);
    }
}
