<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\FeatureFlag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FeatureFlagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class ProfiledSeedCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_option_runs_configured_profile_for_database_seeder(): void
    {
        config()->set('seeds.profiles.test_profile', [
            FeatureFlagSeeder::class,
        ]);

        $exitCode = Artisan::call('db:seed', [
            '--class'          => DatabaseSeeder::class,
            '--profile'        => 'test_profile',
            '--force'          => true,
            '--no-interaction' => true,
        ]);

        self::assertSame(0, $exitCode);
        self::assertGreaterThan(0, FeatureFlag::query()->count());
    }

    public function test_clear_option_truncates_seedable_tables_before_run(): void
    {
        User::factory()->create();

        $exitCode = Artisan::call('db:seed', [
            '--class'          => FeatureFlagSeeder::class,
            '--clear'          => true,
            '--force'          => true,
            '--no-interaction' => true,
        ]);

        self::assertSame(0, $exitCode);
        self::assertSame(0, User::query()->count());
        self::assertGreaterThan(0, FeatureFlag::query()->count());
    }
}
