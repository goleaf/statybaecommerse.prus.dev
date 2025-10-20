<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class LoginRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rate_limiter_returns_429_after_max_attempts(): void
    {
        $email = 'user'.Str::random(6).'@example.com';

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password-secret'),
        ]);

        $maxAttempts = (int) config('security.rate_limiting.login.max_attempts', 5);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                Livewire::test(Login::class)
                    ->set('loginForm.email', $user->email)
                    ->set('loginForm.password', 'incorrect-password')
                    ->call('login');
            } catch (ValidationException $exception) {
                $this->assertSame(422, $exception->status);
            }
        }

        $finalException = null;

        try {
            Livewire::test(Login::class)
                ->set('loginForm.email', $user->email)
                ->set('loginForm.password', 'incorrect-password')
                ->call('login');
        } catch (ValidationException $exception) {
            $finalException = $exception;
        }

        $this->assertNotNull($finalException);
        $this->assertSame(429, $finalException->status);
    }
}
