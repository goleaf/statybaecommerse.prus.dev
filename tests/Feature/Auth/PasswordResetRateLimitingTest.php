<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

final class PasswordResetRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_requests_are_throttled_with_429(): void
    {
        Notification::fake();

        $email = 'reset'.Str::random(6).'@example.com';

        $user = User::factory()->create([
            'email' => $email,
        ]);

        $maxAttempts = (int) config('security.rate_limiting.password_reset.max_attempts', 3);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                Livewire::test('pages.auth.forgot-password')
                    ->set('email', $user->email)
                    ->call('sendPasswordResetLink');
            } catch (ValidationException $exception) {
                $this->assertSame(422, $exception->status);
            }
        }

        $finalException = null;

        try {
            Livewire::test('pages.auth.forgot-password')
                ->set('email', $user->email)
                ->call('sendPasswordResetLink');
        } catch (ValidationException $exception) {
            $finalException = $exception;
        }

        $this->assertNotNull($finalException);
        $this->assertSame(429, $finalException->status);

        Notification::assertSentTimes(ResetPassword::class, $maxAttempts);
    }
}
