<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\RecommendationSystemManagement;
use App\Models\ProductSimilarity;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\RecommendationConfig;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class RecommendationSystemManagementPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve Filament before touching Livewire components.
        $this->resolveAdminPanel();

        // Sign in as an administrator to satisfy panel authorization.
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_clear_cache_removes_cached_entries(): void
    {
        // Persist a cache entry so the page action has visible work to perform.
        $block = RecommendationBlock::factory()->create();
        RecommendationCache::factory()->create([
            'block_id' => $block->id,
        ]);

        Livewire::test(RecommendationSystemManagement::class)
            ->call('clearCache')
            ->assertDispatched('$refresh');

        // Use the singular cache table name to mirror the migration signature.
        $this->assertDatabaseCount('recommendation_cache', 0);
    }

    public function test_system_stats_reflect_current_database_counts(): void
    {
        // Seed representative data across each model that feeds the metrics panel.
        $blocks = RecommendationBlock::factory()->count(2)->create();
        RecommendationConfig::factory()->count(3)->create();
        RecommendationCache::factory()->count(4)->create([
            'block_id' => $blocks->first()->id,
        ]);
        UserBehavior::factory()->count(5)->create();
        ProductSimilarity::factory()->count(6)->create();
        UserProductInteraction::factory()->count(7)->create();

        $stats = Livewire::test(RecommendationSystemManagement::class)
            ->instance()
            ->getSystemStats();

        $this->assertSame(2, $stats['total_blocks']);
        $this->assertSame(2, $stats['active_blocks']);
        $this->assertSame(3, $stats['total_configs']);
        $this->assertSame(4, $stats['cache_entries']);
        $this->assertSame(5, $stats['user_behaviors']);
        $this->assertSame(6, $stats['product_similarities']);
        $this->assertSame(7, $stats['user_interactions']);
    }

    public function test_block_performance_collects_analytics_metrics(): void
    {
        // Create a deterministic block and matching analytics rows for the aggregation.
        $block = RecommendationBlock::factory()->create([
            'name'       => 'Homepage Hero',
            'title'      => 'Hero Block',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        RecommendationAnalytics::factory()->create([
            'block_id'        => $block->id,
            'metrics'         => ['requests' => 5],
            'ctr'             => 0.4,
            'conversion_rate' => 0.1,
        ]);

        RecommendationAnalytics::factory()->create([
            'block_id'        => $block->id,
            'metrics'         => ['requests' => 7],
            'ctr'             => 0.6,
            'conversion_rate' => 0.2,
        ]);

        $performance = Livewire::test(RecommendationSystemManagement::class)
            ->instance()
            ->getBlockPerformance();

        $this->assertCount(1, $performance);
        $this->assertSame('Homepage Hero', $performance[0]['name']);
        $this->assertSame(12, $performance[0]['total_requests']);
        $this->assertEqualsWithDelta(0.5, $performance[0]['avg_ctr'], 0.0001);
        $this->assertEqualsWithDelta(0.15, $performance[0]['avg_conversion'], 0.0001);
    }
}
