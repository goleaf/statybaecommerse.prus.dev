<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecommendationAnalyticsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_recommendation_analytics_record(): void
    {
        $block = RecommendationBlock::factory()->create(['name' => 'homepage-hero']);
        $config = RecommendationConfig::factory()->create(['name' => 'default-config']);
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $analytics = RecommendationAnalytics::create([
            'block_id'        => $block->id,
            'config_id'       => $config->id,
            'user_id'         => $user->id,
            'product_id'      => $product->id,
            'action'          => 'click',
            'ctr'             => 0.1234,
            'conversion_rate' => 0.4321,
            'metrics'         => [
                'impressions' => 150,
                'clicks'      => 12,
            ],
            'date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('recommendation_analytics', [
            'id'     => $analytics->id,
            'action' => 'click',
        ]);

        $this->assertSame('click', $analytics->action);
        $this->assertSame(0.1234, (float) $analytics->ctr);
        $this->assertSame(0.4321, (float) $analytics->conversion_rate);
        $this->assertSame(12, $analytics->metrics['clicks']);
    }

    public function test_scopes_filter_by_action_and_date_range(): void
    {
        $startDate = now()->subDays(5)->toDateString();
        $endDate = now()->toDateString();

        RecommendationAnalytics::factory()->create([
            'action' => 'view',
            'date'   => $startDate,
        ]);

        $clicked = RecommendationAnalytics::factory()->create([
            'action' => 'click',
            'date'   => now()->subDays(2)->toDateString(),
        ]);

        RecommendationAnalytics::factory()->create([
            'action' => 'purchase',
            'date'   => now()->subDays(10)->toDateString(),
        ]);

        $dateRangeResults = RecommendationAnalytics::query()->byDateRange($startDate, $endDate)->get();
        $clickResults = RecommendationAnalytics::query()->byAction('click')->get();

        $this->assertCount(2, $dateRangeResults);
        $this->assertTrue($dateRangeResults->contains(fn (RecommendationAnalytics $analytics): bool => $analytics->is($clicked)));
        $this->assertCount(1, $clickResults);
        $this->assertTrue($clickResults->first()->is($clicked));
    }

    public function test_metrics_are_stored_as_array(): void
    {
        $analytics = RecommendationAnalytics::factory()->create([
            'metrics' => [
                'impressions' => 100,
                'ctr'         => 0.25,
            ],
        ]);

        $this->assertIsArray($analytics->metrics);
        $this->assertSame(0.25, $analytics->metrics['ctr']);
    }
}
