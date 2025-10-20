<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

final class SecurityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('form-login', function (Request $request) {
            $email = (string) $request->input('loginForm.email', $request->input('email'));
            $key = Str::transliterate(Str::lower($email).'|'.$request->ip());

            $maxAttempts = (int) config('security.rate_limiting.login.max_attempts', 5);
            $decaySeconds = (int) config('security.rate_limiting.login.decay_seconds', 60);

            return Limit::perSeconds($decaySeconds, $maxAttempts)->by($key);
        });

        RateLimiter::for('form-password-reset', function (Request $request) {
            $email = (string) $request->input('email');
            $key = Str::transliterate(Str::lower($email).'|'.$request->ip());

            $maxAttempts = (int) config('security.rate_limiting.password_reset.max_attempts', 3);
            $decaySeconds = (int) config('security.rate_limiting.password_reset.decay_seconds', 600);

            return Limit::perSeconds($decaySeconds, $maxAttempts)->by('password-reset:'.$key);
        });

        RateLimiter::for('api', function (Request $request) {
            $identifier = $request->bearerToken()
                ?? $request->header('X-API-KEY')
                ?? optional($request->user())->getAuthIdentifier()
                ?? $request->ip();

            $maxAttempts = (int) config('security.rate_limiting.api.max_attempts', 60);
            $decaySeconds = (int) config('security.rate_limiting.api.decay_seconds', 60);

            return Limit::perSeconds($decaySeconds, $maxAttempts)->by((string) $identifier);
        });
    }
}
