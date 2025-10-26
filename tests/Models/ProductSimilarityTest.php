<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\ProductSimilarity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ProductSimilarityTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_and_casts_configuration(): void
    {
        // Create a fresh model instance to examine the guarded configuration arrays.
        $model = new ProductSimilarity;

        // Confirm mass-assignable attributes guard against accidental column exposure.
        self::assertSame([
            'product_id',
            'similar_product_id',
            'algorithm_type',
            'similarity_score',
            'calculation_data',
            'calculated_at',
        ], $model->getFillable());

        // Ensure native type casting is configured for computational fields.
        self::assertSame([
            'similarity_score' => 'decimal:6',
            'calculation_data' => 'array',
            'calculated_at'    => 'datetime',
        ], $model->getCasts());
    }

    public function test_relationships_return_expected_relation_types(): void
    {
        // Instantiate the model and assert each relationship returns an eloquent relation instance.
        $model = new ProductSimilarity;

        self::assertInstanceOf(BelongsTo::class, $model->product());
        self::assertInstanceOf(BelongsTo::class, $model->similarProduct());
    }

    public function test_scopes_apply_expected_query_constraints(): void
    {
        // Freeze time to obtain deterministic comparisons for the recent scope.
        Carbon::setTestNow(now());

        // Validate the algorithm filtering scope configures an equality where clause.
        $algorithmQuery = ProductSimilarity::query()->byAlgorithm('collaborative');
        $algorithmWhere = $algorithmQuery->getQuery()->wheres[0];
        self::assertSame('algorithm_type', $algorithmWhere['column']);
        self::assertSame('=', $algorithmWhere['operator']);
        self::assertSame('collaborative', $algorithmWhere['value']);

        // Confirm the similarity score scope enforces a greater-than-or-equal threshold.
        $scoreQuery = ProductSimilarity::query()->withMinScore(0.75);
        $scoreWhere = $scoreQuery->getQuery()->wheres[0];
        self::assertSame('similarity_score', $scoreWhere['column']);
        self::assertSame('>=', $scoreWhere['operator']);
        self::assertSame(0.75, $scoreWhere['value']);

        // Ensure ordering scope sorts by similarity score in descending order.
        $orderedQuery = ProductSimilarity::query()->orderedBySimilarity();
        $order = $orderedQuery->getQuery()->orders[0];
        self::assertSame('similarity_score', $order['column']);
        self::assertSame('desc', strtolower($order['direction']));

        // Verify the recent scope constrains results to records after the expected timestamp.
        $recentQuery = ProductSimilarity::query()->recent(3);
        $recentWhere = $recentQuery->getQuery()->wheres[0];
        self::assertSame('calculated_at', $recentWhere['column']);
        self::assertSame('>=', $recentWhere['operator']);

        // Clear the mocked now instance to avoid influencing other tests.
        Carbon::setTestNow();
    }
}
