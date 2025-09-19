<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NewsCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_news_categories(): void
    {
        // Arrange
        $categories = NewsCategory::factory()->count(3)->create();

        // Act
        $response = $this->get('/admin/news-categories');

        // Assert
        $response->assertOk();
        $response->assertSee($categories->first()->name);
    }

    public function test_can_create_news_category(): void
    {
        // Arrange
        $categoryData = [
            'is_visible' => true,
            'sort_order' => 1,
            'color' => '#ff0000',
            'icon' => 'heroicon-o-rectangle-stack',
        ];

        // Act
        $response = $this->post('/admin/news-categories', $categoryData);

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'is_visible' => true,
            'sort_order' => 1,
            'color' => '#ff0000',
            'icon' => 'heroicon-o-rectangle-stack',
        ]);
    }

    public function test_can_edit_news_category(): void
    {
        // Arrange
        $category = NewsCategory::factory()->create([
            'is_visible' => false,
            'sort_order' => 1,
        ]);

        $updateData = [
            'is_visible' => true,
            'sort_order' => 2,
            'color' => '#00ff00',
        ];

        // Act
        $response = $this->put("/admin/news-categories/{$category->id}", $updateData);

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'id' => $category->id,
            'is_visible' => true,
            'sort_order' => 2,
            'color' => '#00ff00',
        ]);
    }

    public function test_can_delete_news_category(): void
    {
        // Arrange
        $category = NewsCategory::factory()->create();

        // Act
        $response = $this->delete("/admin/news-categories/{$category->id}");

        // Assert
        $this->assertDatabaseMissing('news_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_can_filter_visible_categories(): void
    {
        // Arrange
        $visibleCategory = NewsCategory::factory()->create(['is_visible' => true]);
        $hiddenCategory = NewsCategory::factory()->create(['is_visible' => false]);

        // Act
        $response = $this->get('/admin/news-categories?tableFilters[is_visible][value]=1');

        // Assert
        $response->assertOk();
        $response->assertSee($visibleCategory->name);
        $response->assertDontSee($hiddenCategory->name);
    }

    public function test_can_reorder_categories(): void
    {
        // Arrange
        $category1 = NewsCategory::factory()->create(['sort_order' => 1]);
        $category2 = NewsCategory::factory()->create(['sort_order' => 2]);

        // Act
        $response = $this->post('/admin/news-categories/reorder', [
            'items' => [
                ['id' => $category2->id, 'sort_order' => 1],
                ['id' => $category1->id, 'sort_order' => 2],
            ]
        ]);

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'id' => $category2->id,
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('news_categories', [
            'id' => $category1->id,
            'sort_order' => 2,
        ]);
    }

    public function test_can_set_parent_category(): void
    {
        // Arrange
        $parentCategory = NewsCategory::factory()->create();
        $childCategory = NewsCategory::factory()->create(['parent_id' => null]);

        $updateData = [
            'parent_id' => $parentCategory->id,
            'is_visible' => true,
            'sort_order' => 1,
        ];

        // Act
        $response = $this->put("/admin/news-categories/{$childCategory->id}", $updateData);

        // Assert
        $this->assertDatabaseHas('news_categories', [
            'id' => $childCategory->id,
            'parent_id' => $parentCategory->id,
        ]);
    }

    public function test_validation_requires_sort_order(): void
    {
        // Arrange
        $categoryData = [
            'is_visible' => true,
            // Missing sort_order
        ];

        // Act & Assert
        $response = $this->post('/admin/news-categories', $categoryData);
        $response->assertSessionHasErrors(['sort_order']);
    }

    public function test_validation_requires_numeric_sort_order(): void
    {
        // Arrange
        $categoryData = [
            'is_visible' => true,
            'sort_order' => 'not-a-number',
        ];

        // Act & Assert
        $response = $this->post('/admin/news-categories', $categoryData);
        $response->assertSessionHasErrors(['sort_order']);
    }
}

