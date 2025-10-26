<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Event::fake();
});

it('renders the registration page', function () {
    get(route('register'))
        ->assertOk();
});

it('has a registration form', function () {
    livewire(Register::class)
        ->assertFormExists();
});

it('validates required fields', function () {
    livewire(Register::class)
        ->call('register')
        ->assertHasFormErrors([
            // Each key references the nested Livewire form property to mirror the frontend bindings.
            'registrationForm.first_name' => 'required',
            'registrationForm.last_name'  => 'required',
            'registrationForm.email'      => 'required',
            'registrationForm.password'   => 'required',
        ]);
});

it('validates email format', function () {
    livewire(Register::class)
        // Provide an invalid email via the nested form property to trigger format validation.
        ->set('registrationForm.email', 'invalid-email')
        ->call('register')
        ->assertHasFormErrors(['registrationForm.email' => 'email']);
});

it('validates unique email', function () {
    User::factory()->create(['email' => 'test@example.com']);

    livewire(Register::class)
        // Attempt to reuse the existing email on the same nested form binding.
        ->set('registrationForm.email', 'test@example.com')
        ->call('register')
        ->assertHasFormErrors(['registrationForm.email' => 'unique']);
});

it('validates password confirmation', function () {
    livewire(Register::class)
        // Provide mismatched passwords on the nested form to surface the confirmation rule.
        ->set('registrationForm.password', 'password123')
        ->set('registrationForm.password_confirmation', 'different-password')
        ->call('register')
        ->assertHasFormErrors(['registrationForm.password' => 'same']);
});

it('requires password confirmation when submitting a password', function () {
    livewire(Register::class)
        ->fillForm([
            // Provide the minimum required payload except the confirmation to isolate the failure.
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'no-confirm@example.com',
            'password' => 'password123',
        ])
        ->call('register')
        ->assertHasFormErrors(['password_confirmation' => 'required']);
});

it('registers a new user successfully', function () {
    $userData = [
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ];

    livewire(Register::class)
        // Populate the Livewire form using the nested keys so the component mirrors real interactions.
        ->set('registrationForm.first_name', $userData['first_name'])
        ->set('registrationForm.last_name', $userData['last_name'])
        ->set('registrationForm.email', $userData['email'])
        ->set('registrationForm.password', $userData['password'])
        ->set('registrationForm.password_confirmation', $userData['password_confirmation'])
        ->call('register')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('account'));

    assertDatabaseHas('users', [
        'first_name'       => 'John',
        'last_name'        => 'Doe',
        'email'            => 'john@example.com',
        'preferred_locale' => app()->getLocale(),
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect(Hash::check('password123', $user->getAttributes()['password']))->toBeTrue();

    assertAuthenticated();
    Event::assertDispatched(Registered::class);
});

it('sets the preferred locale on registration', function () {
    app()->setLocale('lt');

    livewire(Register::class)
        // Set every nested form field to ensure the component persists the locale alongside the profile data.
        ->set('registrationForm.first_name', 'Jonas')
        ->set('registrationForm.last_name', 'Jonaitis')
        ->set('registrationForm.email', 'jonas@example.com')
        ->set('registrationForm.password', 'password123')
        ->set('registrationForm.password_confirmation', 'password123')
        ->call('register');

    assertDatabaseHas('users', [
        'email'            => 'jonas@example.com',
        'preferred_locale' => 'lt',
    ]);
});

it('has proper form field attributes', function () {
    livewire(Register::class)
        // Confirm the Livewire form exposes the nested keys expected by the frontend bindings.
        ->assertFormFieldExists('registrationForm.first_name')
        ->assertFormFieldExists('registrationForm.last_name')
        ->assertFormFieldExists('registrationForm.email')
        ->assertFormFieldExists('registrationForm.password')
        ->assertFormFieldExists('registrationForm.password_confirmation');
});

it('displays meta information correctly', function () {
    get(route('register'))
        ->assertSee(__('Create account'))
        ->assertSee(__('Create an account to track orders, save favorites, and enjoy a personalized experience'));
});
