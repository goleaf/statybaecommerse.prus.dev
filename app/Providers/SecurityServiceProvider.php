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
        RateLimiter::for('auth.login', function (Request $request): Limit {
            $config = (array) config('security.rate_limiting.auth.login');

            return Limit::perMinutes(
                $this->asMinutes((int) ($config['decay_seconds'] ?? 60)),
                max(1, (int) ($config['max_attempts'] ?? 5))
            )->by($this->loginIdentifier($request));
        });

        RateLimiter::for('auth.password-reset', function (Request $request): Limit {
            $config = (array) config('security.rate_limiting.auth.password_reset');

            return Limit::perMinutes(
                $this->asMinutes((int) ($config['decay_seconds'] ?? 300)),
                max(1, (int) ($config['max_attempts'] ?? 5))
            )->by($this->passwordResetIdentifier($request));
        });

        RateLimiter::for('auth.two-factor', function (Request $request): Limit {
            $config = (array) config('security.rate_limiting.auth.two_factor');

            return Limit::perMinutes(
                $this->asMinutes((int) ($config['decay_seconds'] ?? 60)),
                max(1, (int) ($config['max_attempts'] ?? 5))
            )->by($this->twoFactorIdentifier($request));
        });
    }

    private function asMinutes(int $decaySeconds): int
    {
        return max(1, (int) ceil($decaySeconds / 60));
    }

    private function loginIdentifier(Request $request): string
    {
        $ip = $this->ipAddress($request);
        $email = Str::of((string) $request->input('email', ''))->lower()->value();

        return Str::transliterate('auth-login|' . $email . '|' . $ip);
    }

    private function passwordResetIdentifier(Request $request): string
    {
        $ip = $this->ipAddress($request);
        $email = Str::of((string) $request->input('email', ''))->lower()->value();

        return Str::transliterate('password-reset|' . $email . '|' . $ip);
    }

    private function twoFactorIdentifier(Request $request): string
    {
        // Couple the pending authentication session with the requester IP so the
        // limiter remains effective even if multiple users share a browser instance.
        $ip = $this->ipAddress($request);
        $userId = (string) $request->session()->get('auth.two_factor.id', 'guest');

        return Str::transliterate('two-factor|' . $userId . '|' . $ip);
    }

    private function ipAddress(Request $request): string
    {
        $ip = $request->ip();

        return is_string($ip) && $ip !== '' ? $ip : 'unknown';
    }
}
