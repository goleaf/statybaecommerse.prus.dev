<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Contracts\Entities\UserContract;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class UserProfileController extends Controller
{
    use HandlesContentNegotiation;

    public function __invoke(Request $request): JsonResponse|View|Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        if ($user->trashed()) {
            abort(404);
        }

        $user->refresh();

        $payload = UserContract::forUser($user);

        return $this->respondWithContract($request, $payload);
    }
}
