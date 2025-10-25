<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\RecommendationAnalytics;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecommendationAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_and_casts(): void
    {
        // Inspect the model metadata to confirm guarded attribute definitions.
        $model = new RecommendationAnalytics();

        // Ensure mass-assignable attributes include the metrics columns required for analytics ingestion.
        self::assertSame([
            'block_id',
            'config_id',
            'user_id',
            'product_id',
            'action',
            'ctr',
            'conversion_rate',
            'metrics',
            'date',
        ], $model->getFillable());

        // Confirm type casting is applied to numeric rates, JSON payloads, and date values.
        self::assertSame([
            'ctr' => 'decimal:4',
            'conversion_rate' => 'decimal:4',
            'metrics' => 'array',
            'date' => 'date',
        ], $model->getCasts());
    }

    public function test_relationships_resolve_expected_relation_types(): void
    {
        // Validate each relationship accessor returns a BelongsTo relation for linkage traversal.
        $model = new RecommendationAnalytics();

        self::assertInstanceOf(BelongsTo::class, $model->block());
        self::assertInstanceOf(BelongsTo::class, $model->config());
        self::assertInstanceOf(BelongsTo::class, $model->user());
        self::assertInstanceOf(BelongsTo::class, $model->product());
    }

    public function test_scopes_attach_relevant_query_constraints(): void
    {
        // The date scope should constrain records to the provided exact day.
        $dateQuery = RecommendationAnalytics::query()->byDate('2025-01-15');
        $dateWhere = $dateQuery->getQuery()->wheres[0];
        self::assertSame('date', $dateWhere['column']);
        self::assertSame('2025-01-15', $dateWhere['value']);

        // Range scope should translate to a between clause on the date column.
        $rangeQuery = RecommendationAnalytics::query()->byDateRange('2025-01-01', '2025-01-31');
        $rangeWhere = $rangeQuery->getQuery()->wheres[0];
        self::assertSame('Between', $rangeWhere['type']);
        self::assertSame('date', $rangeWhere['column']);
        self::assertSame(['2025-01-01', '2025-01-31'], $rangeWhere['values']);

        // Action, block, and config scopes should add basic where clauses with provided values.
        $actionQuery = RecommendationAnalytics::query()->byAction('click');
        $actionWhere = $actionQuery->getQuery()->wheres[0];
        self::assertSame('action', $actionWhere['column']);
        self::assertSame('click', $actionWhere['value']);

        $blockQuery = RecommendationAnalytics::query()->byBlock(5);
        $blockWhere = $blockQuery->getQuery()->wheres[0];
        self::assertSame('block_id', $blockWhere['column']);
        self::assertSame(5, $blockWhere['value']);

        $configQuery = RecommendationAnalytics::query()->byConfig(9);
        $configWhere = $configQuery->getQuery()->wheres[0];
        self::assertSame('config_id', $configWhere['column']);
        self::assertSame(9, $configWhere['value']);
    }
}
