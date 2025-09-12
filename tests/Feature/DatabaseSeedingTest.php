<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('runs database seeder without errors and creates base users', function (): void {
    // For SQLite, skip this test as it cannot handle VACUUM operations within transactions
    if (DB::getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite cannot run VACUUM operations within transactions');
        return;
    }

    // For other databases, run the full fresh migration with seed
    Artisan::call('migrate:fresh', ['--seed' => true]);

    // Assert admin and manager users exist as seeded by AdminUserSeeder
    expect(User::where('email', 'admin@statybaecommerse.lt')->exists())->toBeTrue();
    expect(User::where('email', 'manager@statybaecommerse.lt')->exists())->toBeTrue();
});


