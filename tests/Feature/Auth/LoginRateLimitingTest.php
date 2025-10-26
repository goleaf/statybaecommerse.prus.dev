<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class LoginRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_attempts_are_throttled_after_configured_limit(): void
    {
        config([
            'security.rate_limiting.auth.login.max_attempts' => 2,
            'security.rate_limiting.auth.login.decay_seconds' => 120,
        ]);

        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $key = Str::transliterate(Str::lower($user->email).'|127.0.0.1');
        RateLimiter::clear($key);

        $attempt = function () use ($user): void {
            Livewire::test(Login::class)
                ->set('loginForm.email', $user->email)
                ->set('loginForm.password', 'invalid-password')
                ->call('login')
                ->assertHasErrors(['loginForm.email']);
        };

        $attempt();
        $attempt();

        // Capture the limiter response so we can assert the correct HTTP status code is propagated.
        $finalException = null;

        try {
            Livewire::test(Login::class)
                ->set('loginForm.email', $user->email)
                ->set('loginForm.password', 'invalid-password')
                ->call('login');
        } catch (ValidationException $exception) {
            $finalException = $exception;
        }

        $this->assertInstanceOf(ValidationException::class, $finalException);
        $this->assertSame(429, $finalException?->status);
        $this->assertArrayHasKey('loginForm.email', $finalException?->errors() ?? []);

        $key = Str::transliterate(Str::lower($user->email).'|127.0.0.1');

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 2));
        $this->assertGreaterThan(0, RateLimiter::availableIn($key));

        RateLimiter::clear($key);
    }

    public function test_password_reset_requests_are_rate_limited(): void
    {
        config([
            'security.rate_limiting.auth.password_reset.max_attempts' => 1,
            'security.rate_limiting.auth.password_reset.decay_seconds' => 300,
        ]);

        Notification::fake();

        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $key = Str::transliterate('password-reset|'.Str::lower($user->email).'|127.0.0.1');
        RateLimiter::clear($key);

        Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);

        $rateLimitedAttempt = Livewire::test('pages.auth.forgot-password')
            ->set('email', $user->email);

        $thrown = null;

        try {
            $rateLimitedAttempt->call('sendPasswordResetLink');
        } catch (ValidationException $exception) {
            $thrown = $exception;
        }

        $this->assertInstanceOf(ValidationException::class, $thrown);
        $this->assertSame(429, $thrown?->status);
        $this->assertIsArray($thrown->errors());
        $this->assertArrayHasKey('email', $thrown->errors());

        $key = Str::transliterate('password-reset|'.Str::lower($user->email).'|127.0.0.1');

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 1));
        $this->assertGreaterThan(0, RateLimiter::availableIn($key));

        RateLimiter::clear($key);
    }
}
