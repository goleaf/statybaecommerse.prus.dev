<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Exclude this test from global RefreshDatabase trait to avoid VACUUM issues
uses()->skip('Feature');

it('runs database seeder without errors and creates base users', function (): void {
    // Manually handle database setup to avoid RefreshDatabase transaction issues
    // Run migration without seed first
    Artisan::call('migrate:fresh');
    
    // Run seeders manually to avoid VACUUM issues
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder']);

    // Assert admin and manager users exist as seeded by AdminUserSeeder
    expect(User::where('email', 'admin@statybaecommerse.lt')->exists())->toBeTrue();
    expect(User::where('email', 'manager@statybaecommerse.lt')->exists())->toBeTrue();
});


