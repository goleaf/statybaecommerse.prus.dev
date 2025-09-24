<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

final class NewsCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);
    }

    public function test_can_list_news_categories(): void
    {
        // Arrange
        $categories = NewsCategory::factory()->count(3)->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->assertCanSeeTableRecords($categories);
    }

    public function test_can_create_news_category(): void
    {
        // Arrange
        $categoryData = [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test category description',
            'color' => '#FF0000',
            'icon' => 'heroicon-o-tag',
            'sort_order' => 1,
            'is_visible' => true,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\CreateNewsCategory::class)
            ->fillForm($categoryData)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'slug' => 'test-category',
            'color' => '#FF0000',
            'icon' => 'heroicon-o-tag',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }

    public function test_can_edit_news_category(): void
    {
        // Arrange
        $category = NewsCategory::factory()->create([
            'name' => 'Original Category',
            'color' => '#000000',
        ]);

        $updatedData = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
            'color' => '#FF0000',
            'is_visible' => false,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\EditNewsCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'id' => $category->id,
            'color' => '#FF0000',
            'is_visible' => false,
        ]);
    }

    public function test_can_delete_news_category(): void
    {
        // Arrange
        $category = NewsCategory::factory()->create();

        // Act
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->callTableAction('delete', $category);

        // Assert
        $this->assertDatabaseMissing('news_categories', ['id' => $category->id]);
    }

    public function test_can_filter_categories_by_parent(): void
    {
        // Arrange
        $parentCategory = NewsCategory::factory()->create();
        $childCategory = NewsCategory::factory()->create(['parent_id' => $parentCategory->id]);
        $otherCategory = NewsCategory::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->filterTable('parent_id', $parentCategory->id)
            ->assertCanSeeTableRecords([$childCategory])
            ->assertCanNotSeeTableRecords([$otherCategory]);
    }

    public function test_can_filter_categories_by_visibility(): void
    {
        // Arrange
        $visibleCategory = NewsCategory::factory()->create(['is_visible' => true]);
        $hiddenCategory = NewsCategory::factory()->create(['is_visible' => false]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->filterTable('is_visible', 'true')
            ->assertCanSeeTableRecords([$visibleCategory])
            ->assertCanNotSeeTableRecords([$hiddenCategory]);
    }

    public function test_can_filter_categories_with_news(): void
    {
        // Arrange
        $news = News::factory()->create();
        $categoryWithNews = NewsCategory::factory()->create();
        $categoryWithoutNews = NewsCategory::factory()->create();

        $categoryWithNews->news()->attach($news);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->filterTable('has_news', 'with_news')
            ->assertCanSeeTableRecords([$categoryWithNews])
            ->assertCanNotSeeTableRecords([$categoryWithoutNews]);
    }

    public function test_can_filter_categories_with_children(): void
    {
        // Arrange
        $parentCategory = NewsCategory::factory()->create();
        $childCategory = NewsCategory::factory()->create(['parent_id' => $parentCategory->id]);
        $categoryWithoutChildren = NewsCategory::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->filterTable('has_children', 'with_children')
            ->assertCanSeeTableRecords([$parentCategory])
            ->assertCanNotSeeTableRecords([$categoryWithoutChildren]);
    }

    public function test_category_validation_requires_name(): void
    {
        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\CreateNewsCategory::class)
            ->fillForm([
                'slug' => 'test-category',
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_category_validation_requires_slug(): void
    {
        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\CreateNewsCategory::class)
            ->fillForm([
                'name' => 'Test Category',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_category_slug_must_be_unique(): void
    {
        // Arrange
        $existingCategory = NewsCategory::factory()->create(['slug' => 'existing-slug']);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\CreateNewsCategory::class)
            ->fillForm([
                'name' => 'Test Category',
                'slug' => 'existing-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_can_view_category_details(): void
    {
        // Arrange
        $category = NewsCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'color' => '#FF0000',
            'icon' => 'heroicon-o-tag',
        ]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ViewNewsCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => 'Test Category',
                'slug' => 'test-category',
                'description' => 'Test description',
                'color' => '#FF0000',
                'icon' => 'heroicon-o-tag',
            ]);
    }

    public function test_can_search_categories(): void
    {
        // Arrange
        $searchableCategory = NewsCategory::factory()->create(['name' => 'Searchable Category']);
        $otherCategory = NewsCategory::factory()->create(['name' => 'Other Category']);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->searchTable('Searchable')
            ->assertCanSeeTableRecords([$searchableCategory])
            ->assertCanNotSeeTableRecords([$otherCategory]);
    }

    public function test_can_create_hierarchical_category(): void
    {
        // Arrange
        $parentCategory = NewsCategory::factory()->create();

        $childCategoryData = [
            'name' => 'Child Category',
            'slug' => 'child-category',
            'parent_id' => $parentCategory->id,
            'sort_order' => 1,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\CreateNewsCategory::class)
            ->fillForm($childCategoryData)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'name' => 'Child Category',
            'slug' => 'child-category',
            'parent_id' => $parentCategory->id,
        ]);
    }

    public function test_can_reorder_categories(): void
    {
        // Arrange
        $category1 = NewsCategory::factory()->create(['sort_order' => 1]);
        $category2 = NewsCategory::factory()->create(['sort_order' => 2]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->callTableAction('reorder', $category2, ['order' => 1]);

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'id' => $category2->id,
            'sort_order' => 1,
        ]);
    }

    public function test_can_bulk_delete_categories(): void
    {
        // Arrange
        $categories = NewsCategory::factory()->count(3)->create();

        // Act
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\ListNewsCategories::class)
            ->callTableBulkAction('delete', $categories);

        // Assert
        foreach ($categories as $category) {
            $this->assertDatabaseMissing('news_categories', ['id' => $category->id]);
        }
    }

    public function test_auto_generates_slug_from_name(): void
    {
        // Arrange
        $categoryData = [
            'name' => 'Test Category Name',
            'description' => 'Test description',
        ];

        // Act
        Livewire::test(\App\Filament\Resources\NewsCategoryResource\Pages\CreateNewsCategory::class)
            ->fillForm($categoryData)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'slug' => 'test-category-name',
        ]);
    }
}
