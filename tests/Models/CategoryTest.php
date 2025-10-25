<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_ordered_by_name_scope_orders_categories_alphabetically(): void
    {
        // Arrange named categories in an unsorted creation order to verify the scope's behavior.
        Category::factory()->create([
            'name' => 'Gamma Tools',
            'slug' => 'gamma-tools',
        ]);
        Category::factory()->create([
            'name' => 'Alpha Tools',
            'slug' => 'alpha-tools',
        ]);
        Category::factory()->create([
            'name' => 'Beta Tools',
            'slug' => 'beta-tools',
        ]);

        // Act by collecting the names through the dedicated alphabetical scope.
        $orderedNames = Category::query()
            ->orderedByName()
            ->pluck('name')
            ->all();

        // Assert alphabetical ordering is enforced regardless of insertion order.
        $this->assertSame(
            ['Alpha Tools', 'Beta Tools', 'Gamma Tools'],
            $orderedNames,
        );

    }

    public function test_top_level_visible_scope_filters_and_orders_root_categories(): void
    {
        // Arrange a mix of visible and hidden categories plus a nested child for completeness.
        $lateVisible = Category::factory()->create([
            'name'       => 'Late Visible',
            'slug'       => 'late-visible',
            'sort_order' => 2,
            'is_visible' => true,
        ]);
        $earlyVisible = Category::factory()->create([
            'name'       => 'Early Visible',
            'slug'       => 'early-visible',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
        Category::factory()->create([
            'name'       => 'Hidden Root',
            'slug'       => 'hidden-root',
            'is_visible' => false,
        ]);
        Category::factory()->create([
            'name'      => 'Child Node',
            'slug'      => 'child-node',
            'parent_id' => $lateVisible->id,
        ]);

        // Act by executing the composite scope that should only keep visible root nodes ordered by sort_order and name.
        $visibleRootIds = Category::query()
            ->topLevelVisible()
            ->pluck('id')
            ->all();

        // Assert hidden and nested categories are excluded while order preference is honored.
        $this->assertSame([
            $earlyVisible->id,
            $lateVisible->id,
        ], $visibleRootIds);
    }
}
