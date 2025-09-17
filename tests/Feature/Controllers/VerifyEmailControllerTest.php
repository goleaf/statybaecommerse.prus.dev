<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\URL;

it('redirects to account with verified flag', function (): void {
    $user = User::factory()->create(['email_verified_at' => null]);
    login($user);

    $signed = URL::temporarySignedRoute('verification.verify', now()->addMinutes(5), [
        'id' => $user->getKey(),
        'hash' => sha1($user->email),
        'locale' => 'en',
    ]);

    // The controller uses implicit route model binding via EmailVerificationRequest
    $response = get($signed);
    $response->assertRedirectContains('account');
});
