<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\{assertAuthenticated, assertDatabaseHas, get};
use function Pest\Livewire\livewire;

beforeEach(function () {
    Event::fake();
});

it('renders the registration page', function () {
    get(route('register'))
        ->assertOk()
        ->assertSeeLivewire(Register::class);
});

it('has a registration form', function () {
    livewire(Register::class)
        ->assertFormExists();
});

it('validates required fields', function () {
    livewire(Register::class)
        ->fillForm([])
        ->call('register')
        ->assertHasFormErrors([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);
});

it('validates email format', function () {
    livewire(Register::class)
        ->fillForm([
            'email' => 'invalid-email',
        ])
        ->call('register')
        ->assertHasFormErrors(['email' => 'email']);
});

it('validates unique email', function () {
    User::factory()->create(['email' => 'test@example.com']);

    livewire(Register::class)
        ->fillForm([
            'email' => 'test@example.com',
        ])
        ->call('register')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('validates password confirmation', function () {
    livewire(Register::class)
        ->fillForm([
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ])
        ->call('register')
        ->assertHasFormErrors(['password' => 'confirmed']);
});

it('registers a new user successfully', function () {
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    livewire(Register::class)
        ->fillForm($userData)
        ->call('register')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('account'));

    assertDatabaseHas('users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'preferred_locale' => app()->getLocale(),
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect(Hash::check('password123', $user->password))->toBeTrue();

    assertAuthenticated();
    Event::assertDispatched(Registered::class);
});

it('sets the preferred locale on registration', function () {
    app()->setLocale('lt');

    livewire(Register::class)
        ->fillForm([
            'first_name' => 'Jonas',
            'last_name' => 'Jonaitis',
            'email' => 'jonas@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->call('register');

    assertDatabaseHas('users', [
        'email' => 'jonas@example.com',
        'preferred_locale' => 'lt',
    ]);
});

it('has proper form field attributes', function () {
    livewire(Register::class)
        ->assertFormFieldExists('first_name')
        ->assertFormFieldExists('last_name')
        ->assertFormFieldExists('email')
        ->assertFormFieldExists('password')
        ->assertFormFieldExists('password_confirmation');
});

it('displays meta information correctly', function () {
    get(route('register'))
        ->assertSee(__('Create account'))
        ->assertSee(__('Create an account to track orders, save favorites, and enjoy a personalized experience'));
});
