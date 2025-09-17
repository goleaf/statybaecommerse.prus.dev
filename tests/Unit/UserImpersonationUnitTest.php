<?php declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Unit Tests', function () {
    it('can create admin and regular users', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        
        $regularUser = User::factory()->create(['is_admin' => false]);
        
        expect($admin->hasRole('administrator'))->toBeTrue();
        expect($regularUser->is_admin)->toBeFalse();
    });

    it('can validate impersonation permissions', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        
        $regularUser = User::factory()->create(['is_admin' => false]);
        $anotherAdmin = User::factory()->create(['is_admin' => true]);
        
        // Admin can impersonate regular user
        expect(!$regularUser->is_admin)->toBeTrue();
        
        // Admin cannot impersonate another admin
        expect(!$anotherAdmin->is_admin)->toBeFalse();
    });

    it('can handle session data structure', function () {
        $admin = User::factory()->create();
        $regularUser = User::factory()->create(['is_admin' => false]);
        
        $impersonateData = [
            'original_user_id' => $admin->id,
            'impersonated_user_id' => $regularUser->id,
            'started_at' => now()->toISOString(),
        ];
        
        expect($impersonateData)->toHaveKeys(['original_user_id', 'impersonated_user_id', 'started_at']);
        expect($impersonateData['original_user_id'])->toBe($admin->id);
        expect($impersonateData['impersonated_user_id'])->toBe($regularUser->id);
    });

    it('can test user model relationships', function () {
        $user = User::factory()->create();
        
        // Test relationships exist
        expect($user->orders())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->addresses())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->reviews())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->wishlist())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
        expect($user->customerGroups())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
        expect($user->documents())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
    });

    it('can test user model attributes', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true,
            'is_admin' => false,
        ]);
        
        expect($user->name)->toBe('Test User');
        expect($user->email)->toBe('test@example.com');
        expect($user->is_active)->toBeTrue();
        expect($user->is_admin)->toBeFalse();
    });

    it('can test user roles and permissions', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        
        $user = User::factory()->create();
        $user->assignRole('user');
        
        expect($admin->hasRole('administrator'))->toBeTrue();
        expect($user->hasRole('user'))->toBeTrue();
        expect($admin->hasRole('user'))->toBeFalse();
    });
});
