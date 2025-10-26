<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\RecommendationConfig;
use App\Services\Recommendations\ManualRecommendation;
use App\Services\RecommendationService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns recommendation blocks collection (smoke)', function () {
    if (! Schema::hasTable('recommendation_blocks')) {
        test()->markTestSkipped('recommendation_blocks table missing');
    }

    $svc = app(RecommendationService::class);
    $blocks = $svc->getRecommendationBlocks();
    expect($blocks)->toBeInstanceOf(EloquentCollection::class);
});

it('caches manual block recommendations and rehydrates products on subsequent requests', function (): void {
    // Arrange a curated product that the manual algorithm should surface with a fixed score.
    $product = Product::factory()->create([
        'is_visible' => true,
        'price'      => 129.99,
    ]);

    // Create a manual configuration so the service exercises block management and configuration selection.
    $config = RecommendationConfig::factory()->create([
        'type'      => 'manual',
        'is_active' => true,
        'config'    => [
            'product_ids' => [$product->id],
            'score'       => 0.9,
        ],
    ]);

    // Wire the configuration to an active block so the service can generate and cache results before reusing them.
    $block = RecommendationBlock::factory()->create([
        'name'           => 'stub-block',
        'is_active'      => true,
        'config_ids'     => [$config->id],
        'cache_duration' => 600,
        'max_products'   => 5,
    ]);

    $service = app(RecommendationService::class);

    // First call should generate recommendations, cache them, and persist analytics for later comparisons.
    $initial = $service->getRecommendations('stub-block');

    expect($initial)->toHaveCount(1)
        ->and($initial->first())->toBeInstanceOf(Product::class)
        ->and($initial->first()->id)->toBe($product->id)
        ->and($initial->first()->recommendation_score)->toBe(0.9);

    // Inspect the persisted cache payload to ensure caching metadata and analytics traces are stored alongside the products.
    $cacheKey = RecommendationCache::generateCacheKey('stub-block');
    $cacheEntry = RecommendationCache::where('cache_key', $cacheKey)->first();

    expect($cacheEntry)->not->toBeNull()
        ->and($cacheEntry->hit_count)->toBe(0)
        ->and($cacheEntry->recommendations['meta']['result_count'] ?? null)->toBe(1)
        ->and($cacheEntry->recommendations['meta']['config_metrics'][0]['algorithm_class'] ?? null)
        ->toBe(ManualRecommendation::class);

    // Second call should reuse the cache, increment the hit counter, and return hydrated product models again.
    $cached = $service->getRecommendations('stub-block');

    expect($cached)->toHaveCount(1)
        ->and($cached->first())->toBeInstanceOf(Product::class)
        ->and($cached->first()->id)->toBe($product->id)
        ->and($cached->first()->recommendation_score)->toBe(0.9);

    $cacheEntry->refresh();
    expect($cacheEntry->hit_count)->toBe(1);
});

it('compares configuration performance for lightweight ab testing', function (): void {
    $product = Product::factory()->create(['is_visible' => true]);

    $config = RecommendationConfig::factory()->create([
        'is_active' => true,
        'type'      => 'manual',
        'config'    => [
            'product_ids' => [$product->id],
            'score'       => 0.7,
        ],
    ]);

    $block = RecommendationBlock::factory()->create([
        'name'       => 'ab-block',
        'is_active'  => true,
        'config_ids' => [$config->id],
    ]);

    expect($block->fresh()->getConfigs()->first()->type)->toBe('manual');

    $service = app(RecommendationService::class);

    RecommendationAnalytics::factory()->create([
        'block_id'  => $block->id,
        'config_id' => $config->id,
        'action'    => 'serve',
        'metrics'   => [
            'requests'             => 2,
            'total_results'        => 4,
            'total_execution_time' => 1.0,
            'algorithm'            => 'manual',
            'algorithm_class'      => ManualRecommendation::class,
            'config_name'          => $config->name,
            'last_context'         => ['scenario' => 'test'],
        ],
        'date' => now()->toDateString(),
    ]);

    RecommendationAnalytics::factory()->create([
        'block_id'  => $block->id,
        'config_id' => $config->id,
        'action'    => 'serve',
        'metrics'   => [
            'requests'             => 1,
            'total_results'        => 2,
            'total_execution_time' => 0.5,
            'algorithm'            => 'manual',
            'algorithm_class'      => ManualRecommendation::class,
            'config_name'          => $config->name,
            'last_context'         => ['scenario' => 'test-b'],
        ],
        'date' => now()->subDay()->toDateString(),
    ]);

    $comparison = $service->compareConfigPerformance($block);

    expect($comparison)->toHaveCount(1)
        ->and($comparison[0]['config_id'])->toBe($config->id)
        ->and($comparison[0]['requests'])->toBeGreaterThanOrEqual(1)
        ->and($comparison[0]['avg_results'])->toBeGreaterThan(0)
        ->and($comparison[0]['score'])->toBeGreaterThan(0);
});
