<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Livewire\livewire;

afterEach(function (): void {
    \Mockery::close();
});

it('validates login credentials before attempting authentication', function (): void {
    livewire(Login::class)
        ->set('loginForm.email', '')
        ->set('loginForm.password', '')
        ->call('login')
        ->assertHasErrors([
            'loginForm.email' => 'required',
            'loginForm.password' => 'required',
        ]);
});

it('returns 401 when requesting the authenticated profile without a token', function (): void {
    getJson(route('api.v1.user.show'))->assertUnauthorized();
});

it('returns 403 when the token lacks the required ability', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['orders.read']);

    getJson(route('api.v1.user.show'))
        ->assertForbidden()
        ->assertJsonPath('message', 'Invalid ability provided.');
});

it('returns 404 when the authenticated user has been soft deleted', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['profile.read']);

    $user->delete();

    getJson(route('api.v1.user.show'))
        ->assertNotFound()
        ->assertJson(['success' => false]);
});

it('applies rate limiting to the authenticated profile endpoint', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['profile.read']);

    Config::set('api.rate_limits.default', 1);

    $limiterKey = 'user:'.$user->id;
    RateLimiter::clear($limiterKey);

    \Mockery::mock('alias:App\\Support\\Contracts\\Entities\\UserContract')
        ->shouldReceive('forUser')
        ->andReturn(['id' => $user->getKey(), 'name' => $user->name]);

    getJson(route('api.v1.user.show'))->assertOk();

    getJson(route('api.v1.user.show'))->assertStatus(429);

    RateLimiter::clear($limiterKey);
});

it('returns a 500 response when the user contract renderer fails', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['profile.read']);

    $mock = \Mockery::mock('alias:App\\Support\\Contracts\\Entities\\UserContract');
    $mock->shouldReceive('forUser')
        ->once()
        ->andThrow(new \RuntimeException('contract failure'));

    getJson(route('api.v1.user.show'))
        ->assertStatus(500)
        ->assertJson(['success' => false]);
});
