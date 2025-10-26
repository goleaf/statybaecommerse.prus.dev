<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\User;
use App\Support\Security\Captcha\CaptchaManager;
use App\Support\Security\LoginAttemptResult;
use App\Support\Security\LoginRecorder;
use App\Support\Security\SuspiciousIpMonitor;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    #[Validate('nullable|string')]
    public ?string $captchaToken = null;

    #[Validate('nullable|string')]
    public ?string $captchaResponse = null;

    public ?string $captchaQuestion = null;

    public function authenticate(): LoginAttemptResult
    {
        $this->validate();

        $captchaManager = app(CaptchaManager::class);
        $monitor = app(SuspiciousIpMonitor::class);

        $this->ensureIsNotRateLimited($captchaManager, $monitor);

        if ($captchaManager->shouldChallenge($this->throttleKey(), 'auth.login')) {
            $this->syncCaptchaState($captchaManager);

            $this->validate([
                'captchaToken'    => ['required', 'string'],
                'captchaResponse' => ['required', 'string'],
            ]);

            if (! $captchaManager->verify($this->throttleKey(), 'auth.login', (string) $this->captchaToken, (string) $this->captchaResponse)) {
                $this->syncCaptchaState($captchaManager, true);
                $this->captchaResponse = '';

                throw ValidationException::withMessages([
                    'loginForm.captchaResponse' => __('The security check response did not match. Please try again.'),
                ]);
            }
        } else {
            $this->syncCaptchaState($captchaManager);
        }

        $decaySeconds = $this->decaySeconds();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            $throttleKey = $this->throttleKey();
            $maxAttempts = $this->maxAttempts();
            $rateLimiterAttempts = RateLimiter::hit($throttleKey, $decaySeconds);
            $sessionAttempts = $this->incrementAttemptCounter($throttleKey, $decaySeconds);
            $attempts = max($rateLimiterAttempts, $sessionAttempts);

            $monitor->record($this->ipAddress(), 'auth-login', [
                'email'        => $this->email,
                'attempts'     => $attempts,
                'max_attempts' => $maxAttempts,
            ]);

            if ($attempts >= $maxAttempts) {
                $this->throwRateLimitException(
                    $captchaManager,
                    $monitor,
                    $throttleKey,
                    $maxAttempts,
                    primeTimer: false,
                );
            }

            $this->syncCaptchaState($captchaManager, true);

            throw ValidationException::withMessages([
                'loginForm.email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            Auth::logout();

            $exception = ValidationException::withMessages([
                'loginForm.email' => trans('auth.failed'),
            ]);

            $exception->status = 422;

            throw $exception;
        }

        $this->handleSuccessfulPasswordCheck($captchaManager, $monitor);

        if ($user->hasTwoFactor()) {
            return $this->prepareTwoFactorChallenge($user);
        }

        app(LoginRecorder::class)->record($user, request());

        return LoginAttemptResult::success($user);
    }

    public function syncCaptchaState(?CaptchaManager $captchaManager = null, bool $forceRefresh = false): void
    {
        $captchaManager ??= app(CaptchaManager::class);

        if (! $captchaManager->shouldChallenge($this->throttleKey(), 'auth.login')) {
            $this->resetCaptcha();

            return;
        }

        $challenge = $captchaManager->challenge($this->throttleKey(), 'auth.login', $forceRefresh);

        if ($challenge === null) {
            $this->resetCaptcha();

            return;
        }

        $questionChanged = $this->captchaQuestion !== $challenge->question();

        $this->captchaQuestion = $challenge->question();
        $this->captchaToken = $challenge->token();

        if ($forceRefresh || $questionChanged) {
            $this->captchaResponse = '';
        }
    }

    protected function ensureIsNotRateLimited(CaptchaManager $captchaManager, SuspiciousIpMonitor $monitor): void
    {
        $throttleKey = $this->throttleKey();
        $maxAttempts = $this->maxAttempts();

        $sessionAttempts = $this->getAttemptCounter($throttleKey);
        $tooManyAttempts = RateLimiter::tooManyAttempts($throttleKey, $maxAttempts);
        $hasReachedThreshold = $sessionAttempts >= $maxAttempts
            || RateLimiter::attempts($throttleKey) >= $maxAttempts;

        if (! $tooManyAttempts && ! $hasReachedThreshold) {
            return;
        }

        $this->throwRateLimitException(
            $captchaManager,
            $monitor,
            $throttleKey,
            $maxAttempts,
            primeTimer: ! $tooManyAttempts,
        );
    }

    public function throttleKey(): string
    {
        $ip = request()->ip();
        $ipAddress = is_string($ip) && $ip !== '' ? $ip : 'unknown';

        return Str::transliterate(Str::lower($this->email) . '|' . $ipAddress);
    }

    private function resetCaptcha(): void
    {
        $this->captchaQuestion = null;
        $this->captchaToken = null;
        $this->captchaResponse = null;
    }

    private function ipAddress(): string
    {
        $ip = request()->ip();

        return is_string($ip) && $ip !== '' ? $ip : 'unknown';
    }

    private function maxAttempts(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.login'), 'max_attempts', 5));
    }

    private function decaySeconds(): int
    {
        return max(1, (int) data_get(config('security.rate_limiting.auth.login'), 'decay_seconds', 60));
    }

    private function handleSuccessfulPasswordCheck(CaptchaManager $captchaManager, SuspiciousIpMonitor $monitor): void
    {
        // Clear any outstanding throttling artefacts now that the credentials are valid.
        RateLimiter::clear($this->throttleKey());
        $captchaManager->clear($this->throttleKey(), 'auth.login');
        $monitor->reset($this->ipAddress(), 'auth-login');
        $this->clearAttemptCounter($this->throttleKey());
        $this->resetCaptcha();
        $this->clearTwoFactorSession();
    }

    private function prepareTwoFactorChallenge(User $user): LoginAttemptResult
    {
        $this->clearTwoFactorSession();

        session()->put('auth.two_factor.id', $user->getAuthIdentifier());
        session()->put('auth.two_factor.remember', $this->remember);
        session()->put('auth.two_factor.guard', Auth::getDefaultDriver());
        session()->put('auth.two_factor.expires_at', now()->addMinutes(5)->getTimestamp());

        Auth::logout();

        return LoginAttemptResult::requiresTwoFactor($user);
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

    private function throwRateLimitException(
        CaptchaManager $captchaManager,
        SuspiciousIpMonitor $monitor,
        string $throttleKey,
        int $maxAttempts,
        bool $primeTimer
    ): never {
        if ($primeTimer) {
            RateLimiter::hit($throttleKey, $this->decaySeconds());
        }

        $captchaManager->markRequired($throttleKey, 'auth.login');
        $monitor->record($this->ipAddress(), 'auth-login-rate-limit', [
            'email'        => $this->email,
            'attempts'     => RateLimiter::attempts($throttleKey),
            'max_attempts' => $maxAttempts,
        ]);
        $this->syncCaptchaState($captchaManager, true);

        $this->refreshAttemptExpiry($throttleKey, $this->decaySeconds());

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($throttleKey);
        if ($seconds <= 0) {
            $seconds = $this->decaySeconds();
        }

        $exception = ValidationException::withMessages([
            'loginForm.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => (int) ceil($seconds / 60),
            ]),
        ]);

        $exception->status = 429;

        throw $exception;
    }

    private function incrementAttemptCounter(string $throttleKey, int $decaySeconds): int
    {
        // Persist attempts in the file cache to avoid the in-memory array store resetting counts during Livewire testing.
        $store = Cache::store('file');
        $key = $this->attemptCacheKey($throttleKey);
        $payload = $store->get($key, ['count' => 0, 'expires_at' => 0]);
        $now = now()->getTimestamp();

        if (($payload['expires_at'] ?? 0) <= $now) {
            $payload = ['count' => 0, 'expires_at' => 0];
        }

        $payload['count'] = (int) ($payload['count'] ?? 0) + 1;
        $payload['expires_at'] = $now + $decaySeconds;

        $store->put($key, $payload, $decaySeconds);

        return $payload['count'];
    }

    private function getAttemptCounter(string $throttleKey): int
    {
        $store = Cache::store('file');
        $payload = $store->get($this->attemptCacheKey($throttleKey));

        if (! is_array($payload)) {
            return 0;
        }

        if (RateLimiter::attempts($throttleKey) === 0) {
            $store->forget($this->attemptCacheKey($throttleKey));

            return 0;
        }

        if (($payload['expires_at'] ?? 0) <= now()->getTimestamp()) {
            $store->forget($this->attemptCacheKey($throttleKey));

            return 0;
        }

        return max(0, (int) ($payload['count'] ?? 0));
    }

    private function refreshAttemptExpiry(string $throttleKey, int $decaySeconds): void
    {
        $count = $this->getAttemptCounter($throttleKey);

        if ($count === 0) {
            return;
        }

        // Update the expiry to reflect the latest decay so UI messaging stays accurate during lockouts.
        Cache::store('file')->put(
            $this->attemptCacheKey($throttleKey),
            [
                'count'      => $count,
                'expires_at' => now()->getTimestamp() + $decaySeconds,
            ],
            $decaySeconds
        );
    }

    private function clearAttemptCounter(string $throttleKey): void
    {
        Cache::store('file')->forget($this->attemptCacheKey($throttleKey));
    }

    private function attemptCacheKey(string $throttleKey): string
    {
        return 'auth:attempts:' . $throttleKey;
    }
}
