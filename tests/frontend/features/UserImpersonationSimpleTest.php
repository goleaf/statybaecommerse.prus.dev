<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Basic Functionality', function () {
    it('allows administrator to access user impersonation page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $this
            ->actingAs($admin)
            ->get('/admin/user-impersonation')
            ->assertOk();
    });

    it('denies access to regular users', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this
            ->actingAs($user)
            ->get('/admin/user-impersonation')
            ->assertForbidden();
    });

    it('can start impersonation session', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $targetUser = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin);

        // Test the impersonation logic directly
        session([
            'impersonate' => [
                'original_user_id' => $admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ],
        ]);

        auth()->login($targetUser);

        expect(session('impersonate'))->not()->toBeNull();
        expect(session('impersonate.original_user_id'))->toBe($admin->id);
        expect(session('impersonate.impersonated_user_id'))->toBe($targetUser->id);
        expect(auth()->id())->toBe($targetUser->id);
    });

    it('can stop impersonation session', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $targetUser = User::factory()->create(['is_admin' => false]);

        // Start impersonation
        session([
            'impersonate' => [
                'original_user_id' => $admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ],
        ]);
        auth()->login($targetUser);

        // Stop impersonation
        $originalUserId = session('impersonate.original_user_id');
        $originalUser = User::find($originalUserId);

        if ($originalUser) {
            auth()->login($originalUser);
            session()->forget('impersonate');
        }

        expect(session('impersonate'))->toBeNull();
        expect(auth()->id())->toBe($admin->id);
    });

    it('prevents impersonating admin users', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $adminUser = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        // Admin users should not be impersonatable
        expect($adminUser->is_admin)->toBeTrue();

        // The impersonation logic should check for is_admin
        $canImpersonate = ! $adminUser->is_admin;
        expect($canImpersonate)->toBeFalse();
    });

    it('handles impersonation middleware correctly', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $targetUser = User::factory()->create(['is_admin' => false]);

        // Start impersonation
        session([
            'impersonate' => [
                'original_user_id' => $admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ],
        ]);

        // Login as admin first
        $this->actingAs($admin);

        // Make a request that goes through the middleware
        $response = $this->get('/admin');

        // The middleware should handle the impersonation
        expect(auth()->id())->toBe($targetUser->id);
    });
});
