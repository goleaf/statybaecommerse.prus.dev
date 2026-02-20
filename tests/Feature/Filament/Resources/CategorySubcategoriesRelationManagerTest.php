<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\RelationManagers\SubcategoriesRelationManager;
use App\Models\AdminUser;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CategorySubcategoriesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private Category $parentCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin');

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

    public function test_category_edit_page_can_create_subcategory_via_relation_manager_action(): void
    {
        Livewire::test(SubcategoriesRelationManager::class, [
            'ownerRecord' => $this->parentCategory,
            'pageClass'   => EditCategory::class,
        ])
            ->mountTableAction('create')
            ->set('mountedActions.0.data.name', 'Nauja subkategorija')
            ->set('mountedActions.0.data.is_visible', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('categories', [
            'name'      => 'Nauja subkategorija',
            'parent_id' => $this->parentCategory->getKey(),
        ]);
    }
}
