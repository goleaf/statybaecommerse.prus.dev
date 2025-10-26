<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Models\User;
use App\Support\Security\TwoFactor\TotpGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_two_factor_is_redirected_to_challenge(): void
    {
        $secret = app(TotpGenerator::class)->generateSecret();

        $user = User::factory()->create([
            'email' => 'twofactor@example.com',
            'password' => Hash::make('correct-password'),
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        // Attempt the initial login and confirm the response redirects to the challenge screen.
        Livewire::test(Login::class)
            ->set('loginForm.email', $user->email)
            ->set('loginForm.password', 'correct-password')
            ->call('login')
            ->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->assertTrue(session()->has('auth.two_factor.id'));
        $this->assertSame($user->getAuthIdentifier(), session()->get('auth.two_factor.id'));
    }

    public function test_two_factor_challenge_accepts_valid_totp_code(): void
    {
        $generator = app(TotpGenerator::class);
        $secret = $generator->generateSecret();

        $user = User::factory()->create([
            'email' => 'complete@example.com',
            'password' => Hash::make('correct-password'),
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        // Seed the pending two-factor session identifiers to mimic the post-password step.
        session()->put('auth.two_factor.id', $user->getAuthIdentifier());
        session()->put('auth.two_factor.guard', config('auth.defaults.guard'));
        session()->put('auth.two_factor.remember', false);
        session()->put('auth.two_factor.expires_at', now()->addMinutes(5)->getTimestamp());

        $code = $generator->generate($secret, time());
        RateLimiter::clear($this->twoFactorThrottleKey($user));

        // Provide a valid authenticator code and ensure the user is fully authenticated.
        Livewire::test(TwoFactorChallenge::class)
            ->set('code', $code)
            ->call('verify')
            ->assertRedirect(route('account', absolute: false));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertFalse(session()->has('auth.two_factor.id'));

        $refreshed = $user->fresh();
        $this->assertNotNull($refreshed->last_login_at);

        $expectedIpHash = hash_hmac('sha256', '127.0.0.1', (string) config('app.key'));
        $this->assertSame($expectedIpHash, $refreshed->last_login_ip);
        $this->assertIsArray($refreshed->preferences['last_login'] ?? null);
        $this->assertArrayHasKey('device_hash', $refreshed->preferences['last_login']);
    }

    public function test_two_factor_challenge_rate_limits_failed_attempts(): void
    {
        $generator = app(TotpGenerator::class);
        $secret = $generator->generateSecret();

        $user = User::factory()->create([
            'email' => 'throttle@example.com',
            'password' => Hash::make('correct-password'),
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        // Prepare the pending challenge session and reset limiter state.
        session()->put('auth.two_factor.id', $user->getAuthIdentifier());
        session()->put('auth.two_factor.guard', config('auth.defaults.guard'));
        session()->put('auth.two_factor.remember', false);
        session()->put('auth.two_factor.expires_at', now()->addMinutes(5)->getTimestamp());

        $throttleKey = $this->twoFactorThrottleKey($user);
        RateLimiter::clear($throttleKey);

        $maxAttempts = (int) config('security.rate_limiting.auth.two_factor.max_attempts', 5);
        $caught429 = null;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                // Feed an invalid code to increment the limiter bucket.
                Livewire::test(TwoFactorChallenge::class)
                    ->set('code', '000000')
                    ->call('verify');
            } catch (ValidationException $exception) {
                $caught429 = $exception;
            }
        }

        $this->assertInstanceOf(ValidationException::class, $caught429);
        $this->assertSame(429, $caught429->status);
        $this->assertTrue(RateLimiter::tooManyAttempts($throttleKey, $maxAttempts));
    }

    // Helper to mirror the limiter key generation logic from the production component.
    private function twoFactorThrottleKey(User $user): string
    {
        $ip = '127.0.0.1';

        return \Illuminate\Support\Str::transliterate('two-factor|'.$user->getAuthIdentifier().'|'.$ip);
    }
}
