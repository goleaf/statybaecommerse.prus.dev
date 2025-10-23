<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     * Keeping the guard in place prevents sensitive credentials from leaking into session flashes.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     * The closure allows us to hook additional logging or reporting later without
     * changing Laravel's default behaviour while restoring the missing handler class.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            // Intentionally left blank for now so default exception reporting remains intact.
            // Future enhancements can log domain specific context here without touching bootstrap.
        });
    }

    /**
     * Normalize authentication failures so guard-specific redirects work during browser and test flows.
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|Response
    {
        /** @var Request $request */
        if ($request->expectsJson()) {
            // Preserve the original behaviour for API consumers.
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        try {
            $loginRoute = route('filament.admin.auth.login');
        } catch (Throwable) {
            $loginRoute = null;
        }

        if ($loginRoute === null) {
            try {
                $loginRoute = route('login');
            } catch (Throwable) {
                // Last resort: send the user to the root URL to avoid bubbling the exception.
                $loginRoute = '/';
            }
        }

        // Gracefully redirect guests back to the intended login route without surfacing framework exceptions.
        return redirect()->guest($loginRoute);
    }
}
