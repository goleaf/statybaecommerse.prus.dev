<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\RecommendationBlock;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecommendationBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_and_casts(): void
    {
        // Instantiate the model to inspect its configuration arrays.
        $model = new RecommendationBlock();

        // Confirm mass-assignable fields map to the expected recommendation block attributes.
        self::assertSame([
            'name',
            'title',
            'description',
            'type',
            'position',
            'is_active',
            'is_default',
            'show_title',
            'show_description',
            'max_products',
            'sort_order',
            'config_ids',
            'cache_duration',
            'display_settings',
        ], $model->getFillable());

        // Validate boolean, integer, and array casting for runtime safety.
        self::assertSame([
            'config_ids' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'show_title' => 'boolean',
            'show_description' => 'boolean',
            'max_products' => 'integer',
            'sort_order' => 'integer',
            'cache_duration' => 'integer',
            'display_settings' => 'array',
        ], $model->getCasts());
    }

    public function test_relationships_resolve_expected_relation_types(): void
    {
        // Ensure each relationship accessor returns the proper eloquent relation instance.
        $model = new RecommendationBlock();

        self::assertInstanceOf(BelongsToMany::class, $model->products());
        self::assertInstanceOf(HasMany::class, $model->analytics());
        self::assertInstanceOf(HasMany::class, $model->cache());
    }

    public function test_scopes_apply_expected_constraints(): void
    {
        // Validate the active scope enforces an equality filter on the is_active column.
        $activeQuery = RecommendationBlock::query()->active();
        $activeWhere = $activeQuery->getQuery()->wheres[0];
        self::assertSame('is_active', $activeWhere['column']);
        self::assertTrue($activeWhere['value']);

        // Confirm the byName scope matches the provided block name.
        $namedQuery = RecommendationBlock::query()->byName('homepage');
        $namedWhere = $namedQuery->getQuery()->wheres[0];
        self::assertSame('name', $namedWhere['column']);
        self::assertSame('homepage', $namedWhere['value']);

        // Check the orderedByName scope appends an ascending order clause by default.
        $orderedQuery = RecommendationBlock::query()->orderedByName();
        $order = $orderedQuery->getQuery()->orders[0];
        self::assertSame('name', $order['column']);
        self::assertSame('asc', strtolower($order['direction']));
    }
}
