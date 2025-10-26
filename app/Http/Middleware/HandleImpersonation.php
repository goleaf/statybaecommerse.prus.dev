<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('impersonate') || ! Auth::check()) {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        $impersonateData = session('impersonate');
        $impersonatedUserId = is_array($impersonateData)
            ? ($impersonateData['impersonated_user_id'] ?? null)
            : null;

        if (! is_numeric($impersonatedUserId)) {
            // Clear malformed session data to avoid looping over invalid identifiers.
            session()->forget(['impersonate', 'original_user']);

            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        /** @var User|null $impersonatedUser */
        $impersonatedUser = User::find((int) $impersonatedUserId);

        if ($impersonatedUser === null) {
            // Drop the impersonation flag when the target account no longer exists.
            session()->forget(['impersonate', 'original_user']);

            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        if (Auth::id() !== $impersonatedUser->getKey()) {
            // Store the original user once so the admin can revert later on.
            if (! session()->has('original_user')) {
                session(['original_user' => Auth::id()]);
            }

            Auth::login($impersonatedUser);
        }

        $originalUser = null;
        $originalId = session('original_user');
        if (is_numeric($originalId)) {
            $originalUser = User::find((int) $originalId);
        }

        // Always share the impersonation context so banners render on every request.
        view()->share('impersonating', [
            'user'          => $impersonatedUser,
            'original_user' => $originalUser,
        ]);

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
