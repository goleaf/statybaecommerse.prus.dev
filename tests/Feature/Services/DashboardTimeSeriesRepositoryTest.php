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
        $this->createOrderAt(150, CarbonImmutable::now());
        $this->createOrderAt(200, CarbonImmutable::now()->subDay());
        $this->createOrderAt(80, CarbonImmutable::now()->subDays(3));

        $this->createUserAt(CarbonImmutable::now());
        $this->createUserAt(CarbonImmutable::now()->subDay());
        $this->createUserAt(CarbonImmutable::now()->subDays(2));

        $repository = app(DashboardTimeSeriesRepository::class);

        $data = $repository->allSeries();

        self::assertCount(30, $data['labels']);
        self::assertCount(30, $data['datasets'][0]['data']);
        self::assertSame('Dec 12', $data['labels'][0] ?? null);
        self::assertSame('Jan 10', $data['labels'][29] ?? null);
    }

    public function test_time_series_matches_snapshot_for_five_days(): void
    {
        $this->createOrderAt(150, CarbonImmutable::now());
        $this->createOrderAt(50, CarbonImmutable::now());
        $this->createOrderAt(200, CarbonImmutable::now()->subDay());
        $this->createOrderAt(80, CarbonImmutable::now()->subDays(3));

        $this->createUserAt(CarbonImmutable::now());
        $this->createUserAt(CarbonImmutable::now()->subDay());
        $this->createUserAt(CarbonImmutable::now()->subDays(2));
        $this->createUserAt(CarbonImmutable::now()->subDays(3));
        $this->createUserAt(CarbonImmutable::now()->subDays(3));

        $repository = app(DashboardTimeSeriesRepository::class);

        $data = $repository->allSeries(5);

        $expectedPath = base_path('tests/Fixtures/snapshots/dashboard_time_series_five_days.json');
        if (! File::exists($expectedPath)) {
            File::ensureDirectoryExists(dirname($expectedPath));
            File::put($expectedPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        self::assertJsonStringEqualsJsonFile($expectedPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    private function createOrderAt(float $total, CarbonImmutable $createdAt): void
    {
        $order = Order::factory()->completed()->make([
            'total'      => $total,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        // Persist manually to skip the factory's afterCreating hook that would seed extra related models.
        $order->timestamps = false;
        $order->save();
    }

    private function createUserAt(CarbonImmutable $createdAt): void
    {
        $user = User::factory()->make();
        $user->created_at = $createdAt;
        $user->updated_at = $createdAt;
        // Disable automatic timestamps so the custom date persists despite guarded attributes on the model.
        $user->timestamps = false;
        $user->save();
    }
}
