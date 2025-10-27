<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

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

it('requires the password confirmation field before creating an account', function (): void {
    // Act: attempt to register without supplying the confirmation field payload.
    $component = Livewire::test(Register::class)
        ->set('registrationForm.first_name', 'Jamie')
        ->set('registrationForm.last_name', 'Rivera')
        ->set('registrationForm.email', 'jamie@example.com')
        ->set('registrationForm.password', 'Password123!')
        ->set('registrationForm.password_confirmation', '')
        ->call('register');

    // Assert: ensure the confirmation field is treated as required and no user gets persisted.
    $component->assertHasErrors([
        'registrationForm.password_confirmation' => ['required'],
        'registrationForm.password'              => ['confirmed'],
    ]);
    expect(User::where('email', 'jamie@example.com')->exists())->toBeFalse();
    expect(Auth::check())->toBeFalse();
});

it('normalizes uppercase email input before storing the user record', function (): void {
    // Act: submit mixed-case email data to confirm the lowercase validation mutates the payload.
    $component = Livewire::test(Register::class)
        ->set('registrationForm.first_name', 'Casey')
        ->set('registrationForm.last_name', 'Morgan')
        ->set('registrationForm.email', 'UPPER@Example.COM')
        ->set('registrationForm.password', 'Password123!')
        ->set('registrationForm.password_confirmation', 'Password123!')
        ->call('register');

    // Assert: confirm validation passes and that the stored email has been normalized.
    $component->assertHasNoErrors();
    $user = User::where('email', 'upper@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->email)->toBe('upper@example.com');
});

it('validates individual fields during real-time input updates', function (): void {
    // Arrange: bootstrap the Livewire component without submitting the full form payload.
    $component = Livewire::test(Register::class);

    // Act & Assert: ensure an empty first name immediately surfaces the required validation message.
    $component->set('registrationForm.first_name', '')
        ->assertHasErrors(['registrationForm.first_name' => ['required']]);

    // Reset the first name to a valid value so subsequent assertions are not polluted by earlier errors.
    $component->set('registrationForm.first_name', 'Taylor')
        ->assertHasNoErrors(['registrationForm.first_name']);

    // Act & Assert: confirm invalid email syntax is caught before the form is submitted.
    $component->set('registrationForm.email', 'invalid-email')
        ->assertHasErrors(['registrationForm.email' => ['email']]);

    // Normalize the email again to ensure later password checks are isolated to their field only.
    $component->set('registrationForm.email', 'valid@example.com')
        ->assertHasNoErrors(['registrationForm.email']);

    // Act & Assert: verify the default password rule set still enforces minimum length requirements inline.
    $component->set('registrationForm.password', 'short')
        ->assertHasErrors(['registrationForm.password' => ['min']]);

    // Provide a compliant password so the confirmation rule can run without conflicting errors.
    $component->set('registrationForm.password', 'Password123!')
        ->assertHasNoErrors(['registrationForm.password']);

    // Act & Assert: make sure the confirmation field must be populated even during live validation cycles.
    $component->set('registrationForm.password_confirmation', 'temporary')
        ->set('registrationForm.password_confirmation', '')
        ->assertHasErrors(['registrationForm.password_confirmation' => ['required']]);
});
