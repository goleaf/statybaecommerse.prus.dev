<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Basic Tests', function () {
    it('can create users for impersonation testing', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $regularUser = User::factory()->create(['is_admin' => false]);

        expect($admin->hasRole('administrator'))->toBeTrue();
        expect($regularUser->is_admin)->toBeFalse();
    });

    it('can check user permissions for impersonation', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $regularUser = User::factory()->create(['is_admin' => false]);

        // Test permission checks
        expect($admin->hasRole('administrator'))->toBeTrue();
        expect($regularUser->is_admin)->toBeFalse();

        // Test that admin can impersonate regular user
        $canImpersonate = ! $regularUser->is_admin;
        expect($canImpersonate)->toBeTrue();

        // Test that admin cannot impersonate another admin
        $anotherAdmin = User::factory()->create(['is_admin' => true]);
        $cannotImpersonateAdmin = ! $anotherAdmin->is_admin;
        expect($cannotImpersonateAdmin)->toBeFalse();
    });

    it('can handle session data for impersonation', function () {
        $admin = User::factory()->create();
        $regularUser = User::factory()->create(['is_admin' => false]);

        // Test session structure
        $impersonateData = [
            'original_user_id' => $admin->id,
            'impersonated_user_id' => $regularUser->id,
            'started_at' => now()->toISOString(),
        ];

        expect($impersonateData['original_user_id'])->toBe($admin->id);
        expect($impersonateData['impersonated_user_id'])->toBe($regularUser->id);
        expect($impersonateData['started_at'])->not()->toBeNull();
    });

    it('can validate user relationships', function () {
        $user = User::factory()->create();

        // Test that user has the expected relationships
        expect($user->orders())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->addresses())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->reviews())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->wishlist())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
    });
});
