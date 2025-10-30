<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\ObservabilityDashboard;
use App\Models\User;
use App\Support\Monitoring\CacheMetricsStore;
use App\Support\Monitoring\QueueMetricsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ObservabilityDashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_mount_populates_metrics_from_snapshot(): void
    {
        // Force the monitoring stores to use the in-memory cache for deterministic assertions.
        config()->set('observability.metrics.cache_store', 'array');
        config()->set('queue.default', 'sync');
        config()->set('queue.connections', [
            'sync' => [
                'driver' => 'sync',
                'queue'  => 'default',
            ],
        ]);
        $this->bindArrayMetricsStores();

        Cache::store('array')->forever(config('observability.metrics.queue_key'), [
            'total_failed'    => 1,
            'total_processed' => 5,
            'queues'          => [
                [
                    'connection'             => 'redis',
                    'queue'                  => 'default',
                    'failed'                 => 1,
                    'processed'              => 5,
                    'last_failed_at'         => null,
                    'last_failed_job'        => null,
                    'last_exception_message' => null,
                    'last_processed_at'      => null,
                ],
            ],
            'updated_at'   => '2024-01-01T00:00:00Z',
            'last_failure' => null,
        ]);

        Cache::store('array')->forever(config('observability.metrics.cache_key'), [
            'hits'       => 10,
            'misses'     => 2,
            'stores'     => [
                'redis' => ['hits' => 10, 'misses' => 2],
            ],
            'updated_at' => '2024-01-01T00:00:00Z',
        ]);

        $this->resolveAdminPanel();
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        Livewire::test(ObservabilityDashboard::class)
            ->assertSet('queueMetrics.total_failed', 1)
            ->assertSet('queueMetrics.total_processed', 5)
            ->assertSet('cacheMetrics.hits', 10)
            ->assertSet('cacheMetrics.misses', 2)
            ->assertSet('openPrWatchList.0.number', 1566);
    }

    public function test_can_access_requires_admin_context(): void
    {
        $this->assertFalse(ObservabilityDashboard::canAccess());

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);
        $this->assertFalse(ObservabilityDashboard::canAccess());

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');
        auth()->setUser($user->fresh());
        $this->assertTrue(ObservabilityDashboard::canAccess());

        $user->removeRole('admin');
        $user->is_admin = true;
        $user->save();
        auth()->setUser($user->fresh());
        $this->assertTrue(ObservabilityDashboard::canAccess());
    }

    public function test_should_register_navigation_delegates_to_access_check(): void
    {
        $this->assertFalse(ObservabilityDashboard::shouldRegisterNavigation());

        $this->resolveAdminPanel();
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $this->assertTrue(ObservabilityDashboard::shouldRegisterNavigation());
    }

    /**
     * Rebind the monitoring stores against the array cache driver so the
     * Filament page never attempts to resolve Redis connections in tests.
     */
    private function bindArrayMetricsStores(): void
    {
        app()->forgetInstance(CacheMetricsStore::class);
        app()->forgetInstance(QueueMetricsStore::class);

        app()->singleton(CacheMetricsStore::class, function (): CacheMetricsStore {
            return new CacheMetricsStore(Cache::store('array'), (string) config('observability.metrics.cache_key'));
        });

        app()->singleton(QueueMetricsStore::class, function (): QueueMetricsStore {
            return new QueueMetricsStore(Cache::store('array'), (string) config('observability.metrics.queue_key'));
        });
    }
}
