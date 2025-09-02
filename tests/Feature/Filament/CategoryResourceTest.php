<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole($adminRole);
        $this->actingAs($this->adminUser);
    }

    public function test_can_render_category_index_page(): void
    {
        $this->get(CategoryResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_categories(): void
    {
        $categories = Category::factory()->count(10)->create();

        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->assertCanSeeTableRecords($categories);
    }

    public function test_can_create_category(): void
    {
        $newData = [
            'name' => 'Elektriniai įrankiai',
            'slug' => 'elektriniai-irankiai',
            'description' => 'Profesionalūs elektriniai įrankiai',
            'sort_order' => 1,
            'is_visible' => true,
        ];

        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Elektriniai įrankiai',
            'slug' => 'elektriniai-irankiai',
        ]);
    }

    public function test_can_create_subcategory(): void
    {
        $parent = Category::factory()->create(['name' => 'Parent Category']);

        $newData = [
            'name' => 'Subcategory',
            'slug' => 'subcategory',
            'parent_id' => $parent->id,
            'is_visible' => true,
        ];

        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Subcategory',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_can_validate_category_creation(): void
    {
        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
    }

    public function test_can_update_category(): void
    {
        $category = Category::factory()->create();

        $newData = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ];

        Livewire::test(CategoryResource\Pages\EditCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CategoryResource\Pages\EditCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_can_filter_categories_by_visibility(): void
    {
        $visibleCategory = Category::factory()->create(['is_visible' => true]);
        $hiddenCategory = Category::factory()->create(['is_visible' => false]);

        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->filterTable('visible')
            ->assertCanSeeTableRecords([$visibleCategory])
            ->assertCanNotSeeTableRecords([$hiddenCategory]);
    }

    public function test_can_filter_root_categories(): void
    {
        $rootCategory = Category::factory()->create(['parent_id' => null]);
        $subCategory = Category::factory()->create(['parent_id' => $rootCategory->id]);

        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->filterTable('root_categories')
            ->assertCanSeeTableRecords([$rootCategory])
            ->assertCanNotSeeTableRecords([$subCategory]);
    }

    public function test_categories_are_sorted_by_sort_order(): void
    {
        $categoryA = Category::factory()->create(['sort_order' => 2]);
        $categoryB = Category::factory()->create(['sort_order' => 1]);
        $categoryC = Category::factory()->create(['sort_order' => 3]);

        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->assertCanSeeTableRecords([$categoryB, $categoryA, $categoryC], inOrder: true);
    }
}
