<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Order;
use App\Models\User;
use App\Services\Dashboard\DashboardTimeSeriesRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\Feature\TestCase;

final class DashboardTimeSeriesRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        app()->setLocale('en');
        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 1, 10, 12));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_time_series_returns_expected_lengths_and_order(): void
    {
        Order::factory()->completed()->create(['total' => 150, 'created_at' => CarbonImmutable::now()]);
        Order::factory()->completed()->create(['total' => 200, 'created_at' => CarbonImmutable::now()->subDay()]);
        Order::factory()->completed()->create(['total' => 80, 'created_at' => CarbonImmutable::now()->subDays(3)]);

        User::factory()->create(['created_at' => CarbonImmutable::now()]);
        User::factory()->create(['created_at' => CarbonImmutable::now()->subDay()]);
        User::factory()->create(['created_at' => CarbonImmutable::now()->subDays(2)]);

        $repository = app(DashboardTimeSeriesRepository::class);

        $data = $repository->allSeries();

        self::assertCount(30, $data['labels']);
        self::assertCount(30, $data['datasets'][0]['data']);
        self::assertSame('Dec 12', $data['labels'][0] ?? null);
        self::assertSame('Jan 10', $data['labels'][29] ?? null);
    }

    public function test_time_series_matches_snapshot_for_five_days(): void
    {
        Order::factory()->completed()->create(['total' => 150, 'created_at' => CarbonImmutable::now()]);
        Order::factory()->completed()->create(['total' => 50, 'created_at' => CarbonImmutable::now()]);
        Order::factory()->completed()->create(['total' => 200, 'created_at' => CarbonImmutable::now()->subDay()]);
        Order::factory()->completed()->create(['total' => 80, 'created_at' => CarbonImmutable::now()->subDays(3)]);

        User::factory()->create(['created_at' => CarbonImmutable::now()]);
        User::factory()->create(['created_at' => CarbonImmutable::now()->subDay()]);
        User::factory()->create(['created_at' => CarbonImmutable::now()->subDays(2)]);
        User::factory()->create(['created_at' => CarbonImmutable::now()->subDays(3)]);
        User::factory()->create(['created_at' => CarbonImmutable::now()->subDays(3)]);

        $repository = app(DashboardTimeSeriesRepository::class);

        $data = $repository->allSeries(5);

        $expectedPath = base_path('tests/Fixtures/snapshots/dashboard_time_series_five_days.json');
        if (! File::exists($expectedPath)) {
            File::ensureDirectoryExists(dirname($expectedPath));
            File::put($expectedPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        self::assertJsonStringEqualsJsonFile($expectedPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
