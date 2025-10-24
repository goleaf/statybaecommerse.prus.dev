<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Auth;

use App\Support\Security\Captcha\CaptchaManager;
use App\Support\Security\SuspiciousIpMonitor;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.base')]
final class ForgotPassword extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('nullable|string')]
    public ?string $captchaToken = null;

    #[Validate('nullable|string')]
    public ?string $captchaResponse = null;

    public ?string $captchaQuestion = null;

    public function mount(CaptchaManager $captchaManager): void
    {
        // Seed the initial CAPTCHA state so the view can render the current challenge if one is required.
        $this->syncCaptchaState($captchaManager);
    }

    public function sendPasswordResetLink(): void
    {
        $this->validate();

        $captchaManager = app(CaptchaManager::class);
        $monitor = app(SuspiciousIpMonitor::class);
        $decaySeconds = $this->decaySeconds();

        if (! $this->ensureIsNotRateLimited($captchaManager, $monitor)) {
            return;
        }

        try {
            if ($captchaManager->shouldChallenge($this->throttleKey(), 'auth.password_reset')) {
                $this->syncCaptchaState($captchaManager);

                $this->validate([
                    'captchaToken'    => ['required', 'string'],
                    'captchaResponse' => ['required', 'string'],
                ]);

                if (! $captchaManager->verify($this->throttleKey(), 'auth.password_reset', (string) $this->captchaToken, (string) $this->captchaResponse)) {
                    $this->syncCaptchaState($captchaManager, true);
                    $this->captchaResponse = '';

                    throw ValidationException::withMessages([
                        'captchaResponse' => __('The security check response did not match. Please try again.'),
                    ]);
                }
            } else {
                $this->syncCaptchaState($captchaManager);
            }
        } catch (ValidationException $exception) {
            $attempts = $this->recordRateLimitAttempt($monitor, $decaySeconds);

            if ($attempts > $this->maxAttempts()) {
                $this->handleRateLimitViolation($captchaManager, $monitor, primeTimer: false);
            }

            throw $exception;
        }

        $attempts = $this->recordRateLimitAttempt($monitor, $decaySeconds);

        if ($attempts > $this->maxAttempts()) {
            $this->handleRateLimitViolation($captchaManager, $monitor, primeTimer: false);

            return;
        }

        $broker = Password::broker();

        // Remove any existing tokens for the user so each permitted attempt dispatches a notification
        // before the rate limiter triggers and stops further emails from being sent.
        $user = $broker->getUser($this->only('email'));
        if ($user !== null) {
            $broker->deleteToken($user);
        }

        $status = $broker->sendResetLink($this->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            $this->syncCaptchaState($captchaManager, true);

            return;
        }

        session()->flash('status', __($status));

        $this->syncCaptchaState($captchaManager, true);
        $this->refreshAttemptExpiry($this->throttleKey(), $decaySeconds);
    }

    public function hydrate(CaptchaManager $captchaManager): void
    {
        $this->syncCaptchaState($captchaManager);
    }

    public function refreshCaptcha(CaptchaManager $captchaManager): void
    {
        $this->syncCaptchaState($captchaManager, true);
    }

    public function render(): View
    {
        return view('livewire.pages.auth.forgot-password');
    }

    private function ensureIsNotRateLimited(CaptchaManager $captchaManager, SuspiciousIpMonitor $monitor): bool
    {
        $throttleKey = $this->throttleKey();
        $maxAttempts = $this->maxAttempts();

        $tooManyAttempts = RateLimiter::tooManyAttempts($throttleKey, $maxAttempts);
        $hasReachedThreshold = $this->getAttemptCounter($throttleKey) >= $maxAttempts
            || RateLimiter::attempts($throttleKey) >= $maxAttempts;

        if (! $tooManyAttempts && ! $hasReachedThreshold) {
            return true;
        }

        $this->handleRateLimitViolation($captchaManager, $monitor, primeTimer: ! $tooManyAttempts);

        return false;
    }

    private function handleRateLimitViolation(
        CaptchaManager $captchaManager,
        SuspiciousIpMonitor $monitor,
        bool $primeTimer
    ): void {
        if ($primeTimer) {
            RateLimiter::hit($this->throttleKey(), $this->decaySeconds());
        }

        $captchaManager->markRequired($this->throttleKey(), 'auth.password_reset');
        $monitor->record($this->ipAddress(), 'password-reset-rate-limit', [
            'email'        => $this->email,
            'attempts'     => max($this->getAttemptCounter($this->throttleKey()), RateLimiter::attempts($this->throttleKey())),
            'max_attempts' => $this->maxAttempts(),
        ]);
        $this->syncCaptchaState($captchaManager, true);

        $this->refreshAttemptExpiry($this->throttleKey(), $this->decaySeconds());

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        if ($seconds <= 0) {
            $seconds = $this->decaySeconds();
        }

        $message = trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => (int) ceil($seconds / 60),
        ]);

        $this->addError('email', $message);

        if (! $this->shouldThrowRateLimitException()) {
            return;
        }

        $exception = ValidationException::withMessages([
            'email' => $message,
        ]);

        $exception->status = 429;

        throw $exception;
    }

    private function syncCaptchaState(CaptchaManager $captchaManager, bool $forceRefresh = false): void
    {
        if (! $captchaManager->shouldChallenge($this->throttleKey(), 'auth.password_reset')) {
            $this->resetCaptcha();

            return;
        }

        $challenge = $captchaManager->challenge($this->throttleKey(), 'auth.password_reset', $forceRefresh);

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

    private function throttleKey(): string
    {
        $ip = request()->ip();
        $ipAddress = is_string($ip) && $ip !== '' ? $ip : 'unknown';

        return Str::transliterate('password-reset|' . Str::lower($this->email) . '|' . $ipAddress);
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
        $candidates = [];

        $global = config('security.rate_limiting.password_reset.max_attempts');
        if (is_numeric($global)) {
            $value = (int) $global;
            if ($value > 0) {
                $candidates[] = $value;
            }
        }

        $auth = config('security.rate_limiting.auth.password_reset.max_attempts');
        if (is_numeric($auth)) {
            $value = (int) $auth;
            if ($value > 0) {
                $candidates[] = $value;
            }
        }

        if ($candidates === []) {
            return 5;
        }

        return max(1, min($candidates));
    }

    private function decaySeconds(): int
    {
        $configured = config('security.rate_limiting.auth.password_reset.decay_seconds');

        if ($configured === null) {
            $configured = config('security.rate_limiting.password_reset.decay_seconds');
        }

        return max(1, (int) ($configured ?? 300));
    }

    private function incrementAttemptCounter(string $throttleKey, int $decaySeconds): int
    {
        // Persist attempts in the file cache so array-backed stores used during tests do not reset the counter
        // between Livewire requests. This mirrors the runtime fallback used by the login form.
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

        $limiterAttempts = RateLimiter::attempts($throttleKey);
        $limiterAvailableIn = RateLimiter::availableIn($throttleKey);

        if ($limiterAttempts === 0 && $limiterAvailableIn === 0) {
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

        // Keep the expiry aligned with the configured decay so UI messaging can reflect the real lockout window.
        Cache::store('file')->put(
            $this->attemptCacheKey($throttleKey),
            [
                'count'      => $count,
                'expires_at' => now()->getTimestamp() + $decaySeconds,
            ],
            $decaySeconds
        );
    }

    private function recordRateLimitAttempt(SuspiciousIpMonitor $monitor, int $decaySeconds): int
    {
        $rateLimiterAttempts = RateLimiter::hit($this->throttleKey(), $decaySeconds);
        $sessionAttempts = $this->incrementAttemptCounter($this->throttleKey(), $decaySeconds);
        $attempts = max($rateLimiterAttempts, $sessionAttempts);

        $monitor->record($this->ipAddress(), 'password-reset', [
            'email'        => $this->email,
            'attempts'     => $attempts,
            'max_attempts' => $this->maxAttempts(),
        ]);

        return $attempts;
    }

    private function attemptCacheKey(string $throttleKey): string
    {
        return 'auth:password-reset-attempts:' . $throttleKey;
    }

    private function rateLimiterUsesEphemeralStore(): bool
    {
        $defaultStore = config('cache.default');

        if (! is_string($defaultStore) || $defaultStore === '') {
            return false;
        }

        $driver = config("cache.stores.{$defaultStore}.driver");

        return in_array($driver, ['array', 'null'], true);
    }

    private function shouldThrowRateLimitException(): bool
    {
        return true;
    }
}
