<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MenuItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_children_relationship_orders_by_sort_order(): void
    {
        // Arrange: create a parent menu item to attach child items to.
        $parent = MenuItem::factory()->create();

        // Arrange: create children with explicit sort orders to verify ordering.
        $laterChild = MenuItem::factory()->child($parent)->create([
            'sort_order' => 20,
        ]);
        $earlierChild = MenuItem::factory()->child($parent)->create([
            'sort_order' => 10,
        ]);

        // Act: fetch the children through the relationship which applies ordering.
        $children = $parent->children()->get();

        // Assert: the child with the smaller sort order should be returned first.
        $this->assertSame([
            $earlierChild->id,
            $laterChild->id,
        ], $children->pluck('id')->all());
    }

    public function test_scope_roots_filters_out_child_items(): void
    {
        // Arrange: create one root item and one child item for comparison.
        $rootItem = MenuItem::factory()->create([
            'parent_id' => null,
        ]);
        $childItem = MenuItem::factory()->create([
            'parent_id' => $rootItem->id,
        ]);

        // Act: fetch only root menu items using the scope.
        $rootIds = MenuItem::roots()->pluck('id');

        // Assert: ensure the root is included and the child is excluded.
        $this->assertTrue($rootIds->contains($rootItem->id));
        $this->assertFalse($rootIds->contains($childItem->id));
    }

    public function test_scope_ordered_by_name_sorts_by_label(): void
    {
        // Arrange: create menu items with predictable labels to test ordering.
        $alpha = MenuItem::factory()->create([
            'label' => 'Alpha',
        ]);
        $zeta = MenuItem::factory()->create([
            'label' => 'Zeta',
        ]);
        $lambda = MenuItem::factory()->create([
            'label' => 'Lambda',
        ]);

        // Act: retrieve the labels after applying the alphabetical ordering scope.
        $orderedLabels = MenuItem::orderedByName()->pluck('label')->all();

        // Assert: verify that labels are sorted alphabetically regardless of creation order.
        $this->assertSame([
            'Alpha',
            'Lambda',
            'Zeta',
        ], $orderedLabels);

        // Assert: also confirm that the correct records were returned in the sorted order.
        $this->assertSame([
            $alpha->id,
            $lambda->id,
            $zeta->id,
        ], MenuItem::orderedByName()->pluck('id')->all());
    }

    public function test_scope_visible_includes_only_visible_items(): void
    {
        // Arrange: create both visible and hidden menu items.
        $visibleItem = MenuItem::factory()->create([
            'is_visible' => true,
        ]);
        $hiddenItem = MenuItem::factory()->create([
            'is_visible' => false,
        ]);

        // Act: use the visibility scope to retrieve visible menu items only.
        $visibleIds = MenuItem::visible()->pluck('id');

        // Assert: ensure the visible item is present and hidden item is excluded.
        $this->assertTrue($visibleIds->contains($visibleItem->id));
        $this->assertFalse($visibleIds->contains($hiddenItem->id));
    }
}
