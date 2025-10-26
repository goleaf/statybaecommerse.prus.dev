<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class);

it('registers a new account and authenticates the user', function (): void {
    // Arrange: ensure we operate within a predictable locale for the preferred locale assertion.
    app()->setLocale('lt');

    // Act: submit the registration form with valid and unique data.
    $component = Livewire::test(Register::class)
        ->set('registrationForm.first_name', 'Jane')
        ->set('registrationForm.last_name', 'Doe')
        ->set('registrationForm.email', 'jane@example.com')
        ->set('registrationForm.password', 'Password123!')
        ->set('registrationForm.password_confirmation', 'Password123!')
        ->call('register');

    // Assert: confirm the component emitted no validation errors during registration.
    $component->assertHasNoErrors();

    // Assert: verify the user was created with a securely hashed password and correct locale.
    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('Password123!', $user->password))->toBeTrue();
    expect($user->preferred_locale)->toBe('lt');

    // Assert: ensure the user is authenticated after successful registration.
    expect(Auth::check())->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

it('prevents duplicate registrations using the same email address', function (): void {
    // Arrange: create an existing user that should trigger the unique email validation rule.
    User::factory()->create([
        'email' => 'duplicate@example.com',
    ]);

    // Act: attempt to register again using the already-registered email address.
    $component = Livewire::test(Register::class)
        ->set('registrationForm.first_name', 'John')
        ->set('registrationForm.last_name', 'Doe')
        ->set('registrationForm.email', 'duplicate@example.com')
        ->set('registrationForm.password', 'Password123!')
        ->set('registrationForm.password_confirmation', 'Password123!')
        ->call('register');

    // Assert: confirm the unique validation rule is triggered and the user count remains unchanged.
    $component->assertHasErrors(['registrationForm.email' => ['unique']]);
    expect(User::where('email', 'duplicate@example.com')->count())->toBe(1);
    expect(Auth::check())->toBeFalse();
});
