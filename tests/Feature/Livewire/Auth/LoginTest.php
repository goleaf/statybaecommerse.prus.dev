<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Tests\Support\TestingDatabase::migrate();
    Tests\Support\TestingDatabase::ensureUserTestingColumns();
});

it('logs in a user with valid credentials', function (): void {
    $user = User::factory()->create([
        'email'    => 'logintest@example.com',
        'password' => 'Password123!',
    ]);

    Livewire::test(Login::class)
        ->set('loginForm.email', 'logintest@example.com')
        ->set('loginForm.password', 'Password123!')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('account.index', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function (): void {
    User::factory()->create([
        'email'    => 'invalidlogin@example.com',
        'password' => 'Password123!',
    ]);

    Livewire::test(Login::class)
        ->set('loginForm.email', 'invalidlogin@example.com')
        ->set('loginForm.password', 'WrongPassword!')
        ->call('login')
        ->assertHasErrors(['loginForm.email']);

    expect(Auth::check())->toBeFalse();
});

it('validates required login fields', function (): void {
    Livewire::test(Login::class)
        ->set('loginForm.email', '')
        ->set('loginForm.password', '')
        ->call('login')
        ->assertHasErrors([
            'loginForm.email'    => ['required'],
            'loginForm.password' => ['required'],
        ]);
});
