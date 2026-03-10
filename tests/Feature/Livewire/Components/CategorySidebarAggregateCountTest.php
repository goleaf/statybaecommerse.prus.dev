<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\CategorySidebar;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CategorySidebarAggregateCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_aggregates_counts_from_all_descendant_levels_for_visible_menu_nodes(): void
    {
        $root = Category::factory()->create([
            'parent_id'  => null,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $childLevel1 = Category::factory()->create([
            'parent_id'  => $root->id,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $childLevel2 = Category::factory()->create([
            'parent_id'  => $childLevel1->id,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $childLevel3 = Category::factory()->create([
            'parent_id'  => $childLevel2->id,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $childLevel4 = Category::factory()->create([
            'parent_id'  => $childLevel3->id,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $directProduct = Product::factory()->create();
        $deepProduct = Product::factory()->create();

        $directProduct->categories()->attach($childLevel2->id);
        $deepProduct->categories()->attach($childLevel4->id);

        cache()->flush();

        /** @var array<int, array<string, mixed>> $tree */
        $tree = Livewire::test(CategorySidebar::class)->instance()->categoryTree();

        /** @var array<string, mixed>|null $rootNode */
        $rootNode = collect($tree)->firstWhere('id', $root->id);
        $this->assertNotNull($rootNode);
        $this->assertSame(2, (int) ($rootNode['aggregate_products_count'] ?? 0));
        $this->assertSame(0, (int) ($rootNode['products_count'] ?? 0));

        /** @var array<string, mixed>|null $childLevel1Node */
        $childLevel1Node = collect($rootNode['children'] ?? [])->firstWhere('id', $childLevel1->id);
        $this->assertNotNull($childLevel1Node);
        $this->assertSame(2, (int) ($childLevel1Node['aggregate_products_count'] ?? 0));
        $this->assertSame(0, (int) ($childLevel1Node['products_count'] ?? 0));

        /** @var array<string, mixed>|null $childLevel2Node */
        $childLevel2Node = collect($childLevel1Node['children'] ?? [])->firstWhere('id', $childLevel2->id);
        $this->assertNotNull($childLevel2Node);
        $this->assertSame(2, (int) ($childLevel2Node['aggregate_products_count'] ?? 0));
        $this->assertSame(1, (int) ($childLevel2Node['products_count'] ?? 0));

        // Sidebar rendering depth is limited, but aggregate count must still include deeper descendants.
        $this->assertSame([], $childLevel2Node['children'] ?? null);
    }
}

