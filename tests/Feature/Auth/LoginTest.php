<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Laravel\{assertAuthenticated, assertGuest, get};
use function Pest\Livewire\livewire;

beforeEach(function () {
    RateLimiter::clear('login.test@example.com|127.0.0.1');
});

it('renders the login page', function () {
    get(route('login'))
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

it('has a login form', function () {
    livewire(Login::class)
        ->assertFormExists();
});

it('validates required fields', function () {
    livewire(Login::class)
        ->fillForm([])
        ->call('login')
        ->assertHasFormErrors([
            'email' => 'required',
            'password' => 'required',
        ]);
});

it('validates email format', function () {
    livewire(Login::class)
        ->fillForm([
            'email' => 'invalid-email',
        ])
        ->call('login')
        ->assertHasFormErrors(['email' => 'email']);
});

it('fails with invalid credentials', function () {
    livewire(Login::class)
        ->fillForm([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ])
        ->call('login')
        ->assertHasFormErrors(['email']);

    assertGuest();
});

it('logs in user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => 'test@example.com',
            'password' => 'password123',
        ])
        ->call('login')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('account'));

    assertAuthenticated();
    expect(auth()->user()->id)->toBe($user->id);
});

it('remembers user when remember me is checked', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ])
        ->call('login')
        ->assertHasNoFormErrors();

    assertAuthenticated();
    expect(auth()->viaRemember())->toBeTrue();
});

it('redirects to intended url after login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    session(['url.intended' => localized_route('cart.index')]);

    livewire(Login::class)
        ->fillForm([
            'email' => 'test@example.com',
            'password' => 'password123',
        ])
        ->call('login')
        ->assertRedirect(localized_route('cart.index'));
});

it('rate limits login attempts', function () {
    // Simulate 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        livewire(Login::class)
            ->fillForm([
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ])
            ->call('login');
    }

    // 6th attempt should be rate limited
    livewire(Login::class)
        ->fillForm([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ])
        ->call('login')
        ->assertHasFormErrors(['email']);

    assertGuest();
});

it('has proper form field attributes', function () {
    livewire(Login::class)
        ->assertFormFieldExists('email')
        ->assertFormFieldExists('password')
        ->assertFormFieldExists('remember');
});

it('displays meta information correctly', function () {
    get(route('login'))
        ->assertSee(__('Log in'))
        ->assertSee(__('Access your account to track orders, manage addresses, and more'));
});

it('shows register link for new customers', function () {
    get(route('login'))
        ->assertSee(__('New customer'))
        ->assertSee(__('Create account'));
});
