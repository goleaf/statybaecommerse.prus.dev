<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

final class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_categories(): void
    {
        $categories = Category::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->assertCanSeeTableRecords($categories);
    }

    public function test_can_create_category(): void
    {
        $categoryData = [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'is_active' => true,
            'is_visible' => true,
            'is_enabled' => true,
        ];

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\CreateCategory::class)
            ->fillForm($categoryData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_can_edit_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\EditCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Category',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    public function test_can_view_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ViewCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$category]);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->callTableAction('delete', $category)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_can_toggle_category_active_status(): void
    {
        $category = Category::factory()->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->callTableAction('toggle_active', $category)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => true,
        ]);
    }

    public function test_can_toggle_category_visible_status(): void
    {
        $category = Category::factory()->create(['is_visible' => false]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->callTableAction('toggle_visible', $category)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_visible' => true,
        ]);
    }

    public function test_can_bulk_activate_categories(): void
    {
        $categories = Category::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->callTableBulkAction('activate', $categories)
            ->assertHasNoTableBulkActionErrors();

        foreach ($categories as $category) {
            $this->assertDatabaseHas('categories', [
                'id' => $category->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_categories(): void
    {
        $categories = Category::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->callTableBulkAction('deactivate', $categories)
            ->assertHasNoTableBulkActionErrors();

        foreach ($categories as $category) {
            $this->assertDatabaseHas('categories', [
                'id' => $category->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_filter_categories_by_parent(): void
    {
        $parentCategory = Category::factory()->create();
        $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->filterTable('parent_id', $parentCategory->id)
            ->assertCanSeeTableRecords([$childCategory])
            ->assertCanNotSeeTableRecords([$parentCategory]);
    }

    public function test_can_filter_categories_by_active_status(): void
    {
        $activeCategory = Category::factory()->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords([$activeCategory])
            ->assertCanNotSeeTableRecords([$inactiveCategory]);
    }

    public function test_can_filter_categories_by_visible_status(): void
    {
        $visibleCategory = Category::factory()->create(['is_visible' => true]);
        $hiddenCategory = Category::factory()->create(['is_visible' => false]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->filterTable('is_visible', '1')
            ->assertCanSeeTableRecords([$visibleCategory])
            ->assertCanNotSeeTableRecords([$hiddenCategory]);
    }

    public function test_can_filter_categories_by_featured_status(): void
    {
        $featuredCategory = Category::factory()->create(['is_featured' => true]);
        $regularCategory = Category::factory()->create(['is_featured' => false]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->filterTable('is_featured', '1')
            ->assertCanSeeTableRecords([$featuredCategory])
            ->assertCanNotSeeTableRecords([$regularCategory]);
    }

    public function test_can_sort_categories_by_name(): void
    {
        $categoryB = Category::factory()->create(['name' => 'B Category']);
        $categoryA = Category::factory()->create(['name' => 'A Category']);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords([$categoryA, $categoryB], inOrder: true);
    }

    public function test_can_sort_categories_by_sort_order(): void
    {
        $categoryB = Category::factory()->create(['sort_order' => 2]);
        $categoryA = Category::factory()->create(['sort_order' => 1]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->sortTable('sort_order')
            ->assertCanSeeTableRecords([$categoryA, $categoryB], inOrder: true);
    }

    public function test_can_search_categories_by_name(): void
    {
        $matchingCategory = Category::factory()->create(['name' => 'Electronics']);
        $nonMatchingCategory = Category::factory()->create(['name' => 'Clothing']);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->searchTable('Electronics')
            ->assertCanSeeTableRecords([$matchingCategory])
            ->assertCanNotSeeTableRecords([$nonMatchingCategory]);
    }

    public function test_can_search_categories_by_slug(): void
    {
        $matchingCategory = Category::factory()->create(['slug' => 'electronics']);
        $nonMatchingCategory = Category::factory()->create(['slug' => 'clothing']);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->searchTable('electronics')
            ->assertCanSeeTableRecords([$matchingCategory])
            ->assertCanNotSeeTableRecords([$nonMatchingCategory]);
    }

    public function test_category_form_validation(): void
    {
        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => '',  // Required field
                'slug' => 'invalid slug!',  // Invalid format
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'slug']);
    }

    public function test_category_slug_auto_generation(): void
    {
        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => 'Test Category Name',
            ])
            ->assertFormSet('slug', 'test-category-name');
    }

    public function test_category_parent_relationship(): void
    {
        $parentCategory = Category::factory()->create();
        $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertEquals($parentCategory->id, $childCategory->parent_id);
        $this->assertTrue($parentCategory->children->contains($childCategory));
    }

    public function test_category_hierarchical_display(): void
    {
        $parentCategory = Category::factory()->create(['name' => 'Parent']);
        $childCategory = Category::factory()->create([
            'name' => 'Child',
            'parent_id' => $parentCategory->id,
        ]);

        Livewire::test(\App\Filament\Resources\CategoryResource\Pages\ListCategories::class)
            ->assertCanSeeTableRecords([$parentCategory, $childCategory]);
    }
}
