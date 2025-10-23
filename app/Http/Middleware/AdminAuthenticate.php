<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

final class AdminAuthenticate extends Authenticate
{
    protected function redirectTo(Request $request): ?string
    {
        // Preserve JSON responses for API consumers so tests only adjust web flows.
        if ($request->expectsJson()) {
            return null;
        }

        if (Route::has('filament.admin.auth.login')) {
            // Send anonymous users to the Filament login page that the feature tests assert against.
            return route('filament.admin.auth.login');
        }

        // Fall back to Laravel's default login route when the Filament endpoint is unavailable.
        return Route::has('login') ? route('login') : null;
    }
}
