<?php

declare(strict_types=1);

use App\Http\Middleware\HandleImpersonation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;

beforeEach(function () {
    $this->middleware = new HandleImpersonation;
    $this->request = Request::create('/test');
    $this->user = User::factory()->create();
    $this->originalUser = User::factory()->create(['is_admin' => true]);
});

test('middleware passes through when no impersonation session exists', function () {
    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware handles valid impersonation session', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => now(),
        ],
    ]);

    Auth::shouldReceive('login')
        ->once()
        ->with($this->user);

    View::shouldReceive('share')
        ->once()
        ->with('impersonating', Mockery::type('array'));

    Log::shouldReceive('info')
        ->once()
        ->with('User impersonation active', Mockery::type('array'));

    RateLimiter::shouldReceive('hit')
        ->once();

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware validates impersonation session structure', function () {
    // Invalid session data
    session(['impersonating' => 'invalid']);

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware handles expired impersonation session', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => now()->subHours(25), // Expired
        ],
    ]);

    Auth::shouldReceive('logout')->once();
    Log::shouldReceive('info')
        ->once()
        ->with('Impersonation session ended due to expiration');

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect(session()->has('impersonating'))->toBeFalse();
});

test('middleware applies rate limiting', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => now(),
        ],
    ]);

    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->with('impersonation_attempts:127.0.0.1', 10)
        ->andReturn(true);

    Log::shouldReceive('warning')
        ->once()
        ->with('Impersonation rate limit exceeded', Mockery::type('array'));

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('Should not reach here');
    });

    expect($response->getStatusCode())->toBe(429);
});

test('middleware does not share view data for JSON requests', function () {
    $this->request = Request::create('/test', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => now(),
        ],
    ]);

    Auth::shouldReceive('login')
        ->once()
        ->with($this->user);

    View::shouldReceive('share')->never();

    Log::shouldReceive('info')
        ->once()
        ->with('User impersonation active', Mockery::type('array'));

    RateLimiter::shouldReceive('hit')
        ->once();

    $response = $this->middleware->handle($this->request, function ($request) {
        return response()->json(['status' => 'OK']);
    });

    expect($response->getStatusCode())->toBe(200);
});

test('middleware logs impersonation activity with correct data', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => now(),
        ],
    ]);

    Auth::shouldReceive('login')->once();
    View::shouldReceive('share')->once();
    RateLimiter::shouldReceive('hit')->once();

    Log::shouldReceive('info')
        ->once()
        ->with('User impersonation active', [
            'impersonated_user_id' => $this->user->id,
            'original_user_id'     => $this->originalUser->id,
            'ip_address'           => '127.0.0.1',
            'user_agent'           => 'Symfony',
        ]);

    $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });
});

test('middleware handles missing user in session gracefully', function () {
    session([
        'impersonating' => [
            'user'             => null,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => now(),
        ],
    ]);

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware handles missing original user id gracefully', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => null,
            'started_at'       => now(),
        ],
    ]);

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware handles missing started_at gracefully', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => null,
        ],
    ]);

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware validates user instance type', function () {
    session([
        'impersonating' => [
            'user'             => 'not-a-user-instance',
            'original_user_id' => $this->originalUser->id,
            'started_at'       => now(),
        ],
    ]);

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware validates original user id type', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => 'not-an-integer',
            'started_at'       => now(),
        ],
    ]);

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

test('middleware validates started_at type', function () {
    session([
        'impersonating' => [
            'user'             => $this->user,
            'original_user_id' => $this->originalUser->id,
            'started_at'       => 'not-a-datetime',
        ],
    ]);

    $response = $this->middleware->handle($this->request, function ($request) {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});
