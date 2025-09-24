<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Model Tests', function () {
    it('can create a user', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        expect($user->name)->toBe('Test User');
        expect($user->email)->toBe('test@example.com');
    });

    it('can test user relationships', function () {
        $user = User::factory()->create();

        expect($user->orders())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->addresses())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        expect($user->reviews())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('can test user attributes', function () {
        $user = User::factory()->create([
            'is_active' => true,
            'is_admin' => false,
        ]);

        expect($user->is_active)->toBeTrue();
        expect($user->is_admin)->toBeFalse();
    });
});
