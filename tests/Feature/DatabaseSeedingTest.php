<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

it('runs database seeder without errors and creates base users', function (): void {
    // For SQLite, we need to handle VACUUM operations outside of transactions
    if (DB::getDriverName() === 'sqlite') {
        // Skip this test for SQLite as it cannot handle VACUUM operations within transactions
        // The RefreshDatabase trait runs migrations in a transaction which conflicts with VACUUM
        $this->markTestSkipped('SQLite cannot run VACUUM operations within transactions. This test requires a non-transactional database setup.');
        return;
    }

    // For other databases, run the full fresh migration with seed
    Artisan::call('migrate:fresh', ['--seed' => true]);

    // Assert admin and manager users exist as seeded by AdminUserSeeder
    expect(User::where('email', 'admin@statybaecommerse.lt')->exists())->toBeTrue();
    expect(User::where('email', 'manager@statybaecommerse.lt')->exists())->toBeTrue();
});


