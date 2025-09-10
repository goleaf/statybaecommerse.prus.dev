<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('runs database seeder without errors and creates base users', function (): void {
    // Run full fresh migration and seed to validate seeder stability
    Artisan::call('migrate:fresh', ['--seed' => true]);

    // Assert admin and manager users exist as seeded by LithuanianBuilderShopSeeder
    expect(User::where('email', 'admin@statybaecommerse.lt')->exists())->toBeTrue();
    expect(User::where('email', 'manager@statybaecommerse.lt')->exists())->toBeTrue();
});


