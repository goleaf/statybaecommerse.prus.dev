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
use Illuminate\Support\Facades\Log;
use Throwable;

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

        if ($user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => __('errors.' . ErrorCodes::NOT_FOUND),
            ], 404);
        }

        try {
            $user->refresh();
            $payload = UserContract::forUser($user);

            $response = $this->respondWithContract($request, $payload);

            return $response instanceof JsonResponse
                ? $response
                : response()->json($payload);
        } catch (Throwable $exception) {
            Log::warning('Fell back to minimal authenticated user payload.', [
                'exception' => $exception,
                'user_id'   => $user->getKey(),
            ]);

            return response()->json([
                'contract' => UserContract::CONTRACT,
                'version'  => UserContract::VERSION,
                'data'     => [
                    'id'        => $user->getKey(),
                    'full_name' => $user->full_name,
                    'contact'   => [
                        'email' => $user->email,
                        'phone' => $user->phone_number,
                    ],
                ],
                'meta' => [
                    'generated_at' => now()->toISOString(),
                    'is_partial'   => true,
                ],
            ]);
        }
    }
}
