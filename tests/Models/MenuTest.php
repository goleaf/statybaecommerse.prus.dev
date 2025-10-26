<?php

declare(strict_types=1);

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Boot the database refresh trait while the shared Pest bootstrap loads the Laravel TestCase kernel.
uses(RefreshDatabase::class);

describe('Menu model', function (): void {
    it('defines the expected fillable attributes', function (): void {
        // Create a fresh instance so we can verify the guarded attributes.
        $menu = new Menu;

        // Ensuring the fillable list matches the schema prevents silent mass-assignment bugs.
        expect($menu->getFillable())->toBe([
            'key',
            'name',
            'location',
            'description',
            'is_active',
        ]);
    });

    it('casts the is_active attribute to a boolean', function (): void {
        // Persist a menu with a truthy integer to confirm the cast pipeline.
        $menu = Menu::factory()->create([
            'is_active' => 1,
        ]);

        // The value should be returned as a native boolean every time.
        expect($menu->is_active)->toBeTrue();
        expect(is_bool($menu->is_active))->toBeTrue();
    });

    it('returns top-level items ordered by sort order through items relation', function (): void {
        // Use a dedicated menu to isolate relationship behaviour.
        $menu = Menu::factory()->create();

        // Create child items with explicit sort orders for deterministic assertions.
        $laterItem = MenuItem::factory()->for($menu)->create([
            'parent_id'  => null,
            'sort_order' => 20,
        ]);
        $earlierItem = MenuItem::factory()->for($menu)->create([
            'parent_id'  => null,
            'sort_order' => 10,
        ]);
        $childItem = MenuItem::factory()->for($menu)->create([
            'parent_id'  => $earlierItem->id,
            'sort_order' => 5,
        ]);

        // The relation should ignore children and respect the configured ordering.
        $items = $menu->items;

        expect($items)->toHaveCount(2);
        expect($items->pluck('id')->all())->toBe([
            $earlierItem->id,
            $laterItem->id,
        ]);
        expect($items->contains($childItem))->toBeFalse();
    });

    it('returns all items ordered including descendants through allItems relation', function (): void {
        // Build a menu with a parent/child hierarchy for verification.
        $menu = Menu::factory()->create();
        $rootItem = MenuItem::factory()->for($menu)->create([
            'parent_id'  => null,
            'sort_order' => 10,
        ]);
        $childItem = MenuItem::factory()->for($menu)->create([
            'parent_id'  => $rootItem->id,
            'sort_order' => 5,
        ]);

        // All related records should be returned, still sorted by the sort_order column.
        $allItems = $menu->allItems;

        expect($allItems)->toHaveCount(2);
        expect($allItems->pluck('id')->all())->toBe([
            $childItem->id,
            $rootItem->id,
        ]);
    });

    it('scopes active menus correctly', function (): void {
        // Create both active and inactive records to validate the scope predicate.
        $activeMenu = Menu::factory()->create([
            'is_active' => true,
        ]);
        Menu::factory()->create([
            'is_active' => false,
        ]);

        // Removing global scopes lets us assert directly on the scope behaviour.
        $scopedMenus = Menu::query()
            ->withoutGlobalScopes()
            ->active()
            ->get();

        expect($scopedMenus->pluck('id')->all())->toBe([
            $activeMenu->id,
        ]);
    });

    it('scopes menus by key value', function (): void {
        // Ensure we have a deterministic key to filter for.
        $targetMenu = Menu::factory()->create([
            'key'       => 'primary-menu',
            'is_active' => true,
        ]);
        Menu::factory()->create([
            'key'       => 'secondary-menu',
            'is_active' => true,
        ]);

        // The scope should narrow the query down to just the matching record.
        $scopedMenus = Menu::query()
            ->withoutGlobalScopes()
            ->forKey('primary-menu')
            ->get();

        expect($scopedMenus->pluck('id')->all())->toBe([
            $targetMenu->id,
        ]);
    });

    it('scopes menus by location value', function (): void {
        // Persist menus with distinct locations to target in the scope.
        $headerMenu = Menu::factory()->create([
            'location'  => 'header',
            'is_active' => true,
        ]);
        Menu::factory()->create([
            'location'  => 'footer',
            'is_active' => true,
        ]);

        // Filtering by location should only surface the matching menu.
        $scopedMenus = Menu::query()
            ->withoutGlobalScopes()
            ->forLocation('header')
            ->get();

        expect($scopedMenus->pluck('id')->all())->toBe([
            $headerMenu->id,
        ]);
    });

    it('eager loads only visible items ordered when using scopeWithVisibleItems', function (): void {
        // Prepare a menu with a blend of visible and hidden items for the assertion.
        $menu = Menu::factory()->create([
            'is_active' => true,
        ]);
        $hiddenItem = MenuItem::factory()->for($menu)->create([
            'is_visible' => false,
            'sort_order' => 1,
        ]);
        $visibleEarly = MenuItem::factory()->for($menu)->create([
            'is_visible' => true,
            'sort_order' => 2,
        ]);
        $visibleLate = MenuItem::factory()->for($menu)->create([
            'is_visible' => true,
            'sort_order' => 5,
        ]);

        // Fetching with the scope should hydrate the relation using the chained scopes.
        $scopedMenu = Menu::query()
            ->withVisibleItems()
            ->find($menu->getKey());

        expect($scopedMenu)->not->toBeNull();
        expect($scopedMenu->relationLoaded('allItems'))->toBeTrue();
        expect($scopedMenu->allItems->pluck('id')->all())->toBe([
            $visibleEarly->id,
            $visibleLate->id,
        ]);
        expect($scopedMenu->allItems->contains($hiddenItem))->toBeFalse();
    });

    it('orders menus alphabetically using scopeOrderedByName', function (): void {
        // Persist menus with intentionally jumbled names to confirm ordering.
        Menu::factory()->create([
            'name'      => 'Delta Menu',
            'key'       => 'delta-menu',
            'is_active' => true,
        ]);
        Menu::factory()->create([
            'name'      => 'Alpha Menu',
            'key'       => 'alpha-menu',
            'is_active' => true,
        ]);
        Menu::factory()->create([
            'name'      => 'Bravo Menu',
            'key'       => 'bravo-menu',
            'is_active' => true,
        ]);

        // The resulting collection should come back sorted in ascending order by name.
        $orderedNames = Menu::query()
            ->withoutGlobalScopes()
            ->orderedByName()
            ->pluck('name')
            ->all();

        expect($orderedNames)->toBe([
            'Alpha Menu',
            'Bravo Menu',
            'Delta Menu',
        ]);
    });
});
