<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Http\Request;

final class ResolveFilamentGuard
{
    public function handle(Request $request, Closure $next)
    {
        $guard = config('filament.auth.guard');

        if (is_string($guard) && $guard !== '') {
            $panel = Filament::getCurrentPanel();

            if (! $panel instanceof Panel) {
                $panel = Filament::getPanel('admin', false);
                if ($panel instanceof Panel) {
                    Filament::setCurrentPanel($panel);
                }
            }

            if ($panel instanceof Panel) {
                $panel->authGuard($guard);
            }

            auth()->shouldUse($guard);
        }

        return $next($request);
    }
}
