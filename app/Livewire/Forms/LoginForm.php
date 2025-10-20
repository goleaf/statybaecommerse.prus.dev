<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    public function authenticate(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $decaySeconds = $this->decaySeconds();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($this->throttleKey(), $decaySeconds);

            throw ValidationException::withMessages([
                'loginForm.email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts())) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'loginForm.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        $ip = request()->ip();
        $ipAddress = is_string($ip) && $ip !== '' ? $ip : 'unknown';

        return Str::transliterate(Str::lower($this->email).'|'.$ipAddress);
    }

    private function maxAttempts(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.login'), 'max_attempts', 5));
    }

    private function decaySeconds(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.login'), 'decay_seconds', 60));
    }
}
