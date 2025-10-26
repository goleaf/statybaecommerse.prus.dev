<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use App\Support\Security\LoginRecorder;
use App\Support\Security\TwoFactor\TwoFactorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.base')]
final class TwoFactorChallenge extends Component
{
    #[Validate('nullable|string')]
    public string $code = '';

    #[Validate('nullable|string')]
    public string $recoveryCode = '';

    public string $maskedIdentifier = '';

    private ?User $cachedPendingUser = null;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectIntended(default: route('account', absolute: false), navigate: true);

            return;
        }

        $user = $this->pendingUser();

        if (! $user instanceof User) {
            $this->redirectRoute('login');

            return;
        }

        $this->maskedIdentifier = $this->maskEmail($user->email);
    }

    public function verify(TwoFactorService $twoFactorService, LoginRecorder $recorder): void
    {
        $user = $this->pendingUser();

        if (! $user instanceof User) {
            $this->redirectRoute('login');

            return;
        }

        $this->validate([
            'code'         => ['nullable', 'string', 'required_without:recoveryCode'],
            'recoveryCode' => ['nullable', 'string', 'required_without:code'],
        ]);

        $throttleKey = $this->throttleKey();
        $maxAttempts = $this->maxAttempts();

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            throw $this->buildThrottleException($throttleKey);
        }

        $submittedCode = $this->code !== '' ? $this->code : $this->recoveryCode;

        if (! $twoFactorService->verify($user, (string) $submittedCode)) {
            RateLimiter::hit($throttleKey, $this->decaySeconds());

            if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
                throw $this->buildThrottleException($throttleKey);
            }

            $exception = ValidationException::withMessages([
                'code' => trans('auth.failed'),
            ]);

            $exception->status = 422;

            throw $exception;
        }

        RateLimiter::clear($throttleKey);

        $this->completeAuthentication($user, $recorder);
    }

    public function render(): View
    {
        return view('livewire.auth.two-factor-challenge');
    }

    private function pendingUser(): ?User
    {
        if ($this->cachedPendingUser instanceof User) {
            return $this->cachedPendingUser;
        }

        $userId = session()->get('auth.two_factor.id');
        $expiresAt = (int) session()->get('auth.two_factor.expires_at', 0);

        if ($userId === null || ($expiresAt !== 0 && $expiresAt < now()->getTimestamp())) {
            $this->clearTwoFactorSession();

            return null;
        }

        $user = User::query()->find($userId);

        if (! $user instanceof User) {
            $this->clearTwoFactorSession();

            return null;
        }

        return $this->cachedPendingUser = $user;
    }

    private function completeAuthentication(User $user, LoginRecorder $recorder): void
    {
        $guard = (string) session()->get('auth.two_factor.guard', Auth::getDefaultDriver());
        $remember = (bool) session()->get('auth.two_factor.remember', false);

        Auth::guard($guard)->login($user, $remember);
        Auth::shouldUse($guard);

        session()->regenerate();

        $recorder->record($user, request());

        $this->clearTwoFactorSession();
        $this->code = '';
        $this->recoveryCode = '';

        $this->redirectIntended(default: route('account', absolute: false), navigate: true);
    }

    private function throttleKey(): string
    {
        $ip = request()->ip();
        $ipAddress = is_string($ip) && $ip !== '' ? $ip : 'unknown';
        $userId = (string) session()->get('auth.two_factor.id', 'guest');

        return Str::transliterate('two-factor|' . $userId . '|' . $ipAddress);
    }

    private function maxAttempts(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.two_factor'), 'max_attempts', 5));
    }

    private function decaySeconds(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.two_factor'), 'decay_seconds', 60));
    }

    private function buildThrottleException(string $throttleKey): ValidationException
    {
        $seconds = RateLimiter::availableIn($throttleKey);

        if ($seconds <= 0) {
            $seconds = $this->decaySeconds();
        }

        $exception = ValidationException::withMessages([
            'code' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ]);

        $exception->status = 429;

        return $exception;
    }

    private function maskEmail(?string $email): string
    {
        if (! is_string($email) || $email === '' || ! str_contains($email, '@')) {
            return '';
        }

        [$localPart, $domain] = explode('@', $email, 2);
        $length = Str::length($localPart);
        $visible = max(1, min(3, $length - 1));
        $maskedLocal = Str::substr($localPart, 0, $visible) . str_repeat('*', max(1, $length - $visible));

        return $maskedLocal . '@' . $domain;
    }

    private function clearTwoFactorSession(): void
    {
        session()->forget([
            'auth.two_factor.id',
            'auth.two_factor.remember',
            'auth.two_factor.guard',
            'auth.two_factor.expires_at',
        ]);
    }
}
