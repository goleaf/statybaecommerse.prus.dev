<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\CacheMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

final class CacheMaintenancePageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve Filament panel services before mounting the page.
        $this->resolveAdminPanel();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);

        // Ensure the monitoring stores use the in-memory cache for predictable results.
        config()->set('observability.metrics.cache_store', 'array');
        // Force the application to use the file cache driver so forget operations survive the
        // Livewire request cycle triggered during the action call.
        config()->set('cache.default', 'file');
        Cache::store('file')->flush();
        // Seed a minimal performance snapshot so the dashboard infolist renders without touching
        // external metrics collectors during the Livewire mount cycle.
        Cache::put('component_performance_metrics', [
            'demo-component' => [
                'total_renders' => 1,
                'total_time'    => 10,
                'avg_time'      => 10,
                'min_time'      => 10,
                'max_time'      => 10,
                'last_render'   => now()->toISOString(),
            ],
        ], now()->addHour());
    }

    public function test_forget_cache_key_action_clears_cached_entry(): void
    {
        // Seed the cache so the action has something concrete to clear.
        Cache::put('demo-key', 'value');

        Livewire::test(CacheMaintenance::class)
            ->fillForm([
                'cacheKey' => 'demo-key',
                'cacheTags' => [],
            ])
            ->callAction('forgetCacheKey')
            ->assertHasNoActionErrors();

        $this->assertNull(Cache::store('file')->get('demo-key'));
    }

    public function test_should_register_navigation_follows_authorization_rules(): void
    {
        auth()->logout();
        $this->assertFalse(CacheMaintenance::shouldRegisterNavigation());

        $this->actingAs($this->admin);
        $this->assertTrue(CacheMaintenance::shouldRegisterNavigation());
    }
}
