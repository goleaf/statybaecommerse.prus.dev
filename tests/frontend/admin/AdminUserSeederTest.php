<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\AdminUserSeeder;

it('seeds a default administrator user', function () {
    // Ensure a clean state
    expect(User::where('email', 'admin@example.com')->exists())->toBeFalse();

    // Run the seeder
    $this->seed(AdminUserSeeder::class);

    // Assert seeded user exists with expected role
    $admin = User::where('email', 'admin@example.com')->first();
    expect($admin)->not->toBeNull();
    expect($admin->hasRole('administrator'))->toBeTrue();
});
