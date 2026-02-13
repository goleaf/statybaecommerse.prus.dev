<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategorySubcategoriesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private Category $parentCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin);

        $this->parentCategory = Category::withoutGlobalScopes()->create([
            'name'       => 'Parent Category',
            'slug'       => 'parent-category',
            'is_visible' => true,
            'is_enabled' => true,
            'is_active'  => true,
        ]);
    }

    public function test_creating_subcategory_without_slug_generates_slug_for_parent_relation(): void
    {
        $subcategory = $this->parentCategory->children()->create([
            'name'       => 'Betonuojami sijos pagrindai u formos',
            'is_visible' => true,
            'is_enabled' => true,
            'is_active'  => true,
        ]);

        $this->assertNotNull($subcategory);
        $this->assertSame('betonuojami-sijos-pagrindai-u-formos', $subcategory->slug);
    }

    public function test_category_edit_page_subcategories_relation_tab_loads(): void
    {
        $response = $this->get("/admin/categories/{$this->parentCategory->getRouteKey()}/edit?relation=4");

        $this->assertLessThan(500, $response->status());
    }
}
