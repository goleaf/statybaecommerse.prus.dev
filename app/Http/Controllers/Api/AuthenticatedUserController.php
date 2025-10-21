<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShowAuthenticatedUserRequest;
use App\Models\User;
use App\Support\ApiErrorResponse;
use App\Support\Contracts\Entities\UserContract;
use App\Support\ErrorCodes;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;

final class AuthenticatedUserController extends Controller
{
    use HandlesContentNegotiation;

    public function __invoke(ShowAuthenticatedUserRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            // Fallback to a structured problem response when the guard does not yield a user model.
            return ApiErrorResponse::problem(
                request: $request,
                errorCode: ErrorCodes::PROFILE_UNAVAILABLE,
                detail: __('errors.messages.profile_unavailable'),
                status: 503,
                title: ApiErrorResponse::titleFor(ErrorCodes::PROFILE_UNAVAILABLE),
            );
        }

        $payload = UserContract::forUser($user);

        return $this->respondWithContract($request, $payload);
    }
}
