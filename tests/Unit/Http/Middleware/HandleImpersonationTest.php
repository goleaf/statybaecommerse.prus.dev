<?php declare(strict_types=1);

use App\Http\Middleware\HandleImpersonation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

describe('HandleImpersonation Middleware', function () {
    beforeEach(function () {
        $this->middleware = new HandleImpersonation();
        $this->request = Request::create('/test', 'GET');
    });

    it('does nothing when no impersonation session exists', function () {
        $response = $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
        expect(session()->has('impersonate'))->toBeFalse();
    });

    it('does nothing when user is not authenticated', function () {
        session(['impersonate' => [
            'original_user_id' => 1,
            'impersonated_user_id' => 2,
            'started_at' => now()->toISOString(),
        ]]);

        $response = $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
        expect(Auth::check())->toBeFalse();
    });

    it('handles impersonation when session exists and user is authenticated', function () {
        $originalUser = User::factory()->create();
        $impersonatedUser = User::factory()->create();

        session(['impersonate' => [
            'original_user_id' => $originalUser->id,
            'impersonated_user_id' => $impersonatedUser->id,
            'started_at' => now()->toISOString(),
        ]]);

        Auth::login($originalUser);

        $response = $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
        expect(Auth::id())->toBe($impersonatedUser->id);
        expect(session('original_user'))->toBe($originalUser->id);
    });

    it('does not switch user if already impersonating the correct user', function () {
        $originalUser = User::factory()->create();
        $impersonatedUser = User::factory()->create();

        session(['impersonate' => [
            'original_user_id' => $originalUser->id,
            'impersonated_user_id' => $impersonatedUser->id,
            'started_at' => now()->toISOString(),
        ]]);

        // Already logged in as the impersonated user
        Auth::login($impersonatedUser);

        $response = $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
        expect(Auth::id())->toBe($impersonatedUser->id);
    });

    it('handles missing impersonated user gracefully', function () {
        $originalUser = User::factory()->create();

        session(['impersonate' => [
            'original_user_id' => $originalUser->id,
            'impersonated_user_id' => 999, // Non-existent user
            'started_at' => now()->toISOString(),
        ]]);

        Auth::login($originalUser);

        $response = $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
        expect(Auth::id())->toBe($originalUser->id);
    });

    it('stores original user only once', function () {
        $originalUser = User::factory()->create();
        $impersonatedUser = User::factory()->create();

        session(['impersonate' => [
            'original_user_id' => $originalUser->id,
            'impersonated_user_id' => $impersonatedUser->id,
            'started_at' => now()->toISOString(),
        ]]);

        Auth::login($originalUser);

        // First call
        $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        $firstOriginalUser = session('original_user');

        // Second call
        $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        expect(session('original_user'))->toBe($firstOriginalUser);
        expect(session('original_user'))->toBe($originalUser->id);
    });

    it('shares impersonation data with view', function () {
        $originalUser = User::factory()->create();
        $impersonatedUser = User::factory()->create();

        session(['impersonate' => [
            'original_user_id' => $originalUser->id,
            'impersonated_user_id' => $impersonatedUser->id,
            'started_at' => now()->toISOString(),
        ]]);

        Auth::login($originalUser);

        $response = $this->middleware->handle($this->request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
        
        // Check if impersonation data is shared with view
        $sharedData = view()->shared('impersonating');
        expect($sharedData)->not()->toBeNull();
        expect($sharedData['user']->id)->toBe($impersonatedUser->id);
        expect($sharedData['original_user']->id)->toBe($originalUser->id);
    });
});
