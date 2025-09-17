<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

final class MenuSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Menu $menu */
        $menu = Menu::query()->firstOrCreate(
            ['key' => 'main_header'],
            [
                'name' => 'Pagrindinis meniu',
                'location' => 'header',
                'is_active' => true,
            ]
        );

        // Clear existing items for idempotency
        $menu->allItems()->delete();

        // Build from visible root categories
        $roots = Category::query()
            ->active()
            ->root()
            ->ordered()
            ->with(['children.children.children'])
            ->get();

        $sort = 0;
        foreach ($roots as $root) {
            $sort++;
            $rootItem = $this->createItem($menu, null, $root, $sort);
            $this->createChildren($menu, $rootItem, $root);
        }
    }

    private function createChildren(Menu $menu, MenuItem $parentItem, Category $parentCategory): void
    {
        $order = 0;
        foreach ($parentCategory->children()->ordered()->get() as $childCategory) {
            $order++;
            $childItem = $this->createItem($menu, $parentItem, $childCategory, $order);
            if ($childCategory->children()->exists()) {
                $this->createChildren($menu, $childItem, $childCategory);
            }
        }
    }

    private function createItem(Menu $menu, ?MenuItem $parentItem, Category $category, int $sortOrder): MenuItem
    {
        $label = method_exists($category, 'trans')
            ? ($category->trans('name') ?? $category->name)
            : $category->name;

        return MenuItem::query()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItem?->id,
            'label' => $label,
            'url' => null,
            'route_name' => 'category.show',
            'route_params' => ['category' => $category->slug],
            'icon' => null,
            'sort_order' => $sortOrder,
            'is_visible' => true,
        ]);
    }
}
