<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use App\Models\Category;
use App\Filament\Resources\Categories\CategoryResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user with proper permissions
        $this->user = User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $this->user->assignRole($adminRole);
    }

    public function test_can_list_categories(): void
    {
        $categories = Category::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('index'))
            ->assertOk();
    }

    public function test_can_view_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('view', ['record' => $category]))
            ->assertOk();
    }

    public function test_can_create_category(): void
    {
        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('create'))
            ->assertOk();
    }

    public function test_can_edit_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('edit', ['record' => $category]))
            ->assertOk();
    }

    public function test_category_resource_has_correct_model(): void
    {
        $this->assertEquals(Category::class, CategoryResource::getModel());
    }

    public function test_category_table_has_required_columns(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_visible' => true,
        ]);

        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('index'))
            ->assertSee('Test Category')
            ->assertSee('test-category');
    }

    public function test_category_form_has_required_fields(): void
    {
        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('create'))
            ->assertSee('name')
            ->assertSee('slug')
            ->assertSee('description');
    }

    public function test_can_filter_categories_by_visibility(): void
    {
        $visibleCategory = Category::factory()->create(['is_visible' => true]);
        $hiddenCategory = Category::factory()->create(['is_visible' => false]);

        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('index'))
            ->assertSee($visibleCategory->name)
            ->assertSee($hiddenCategory->name);
    }

    public function test_can_search_categories(): void
    {
        $category1 = Category::factory()->create(['name' => 'Unique Category Name']);
        $category2 = Category::factory()->create(['name' => 'Another Category']);

        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('index') . '?search=Unique')
            ->assertSee('Unique Category Name')
            ->assertDontSee('Another Category');
    }

    public function test_category_resource_pages_exist(): void
    {
        $pages = CategoryResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_category_resource_has_eloquent_query(): void
    {
        $query = CategoryResource::getEloquentQuery();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    public function test_category_resource_has_global_search_attributes(): void
    {
        $searchAttributes = CategoryResource::getGlobalSearchResultAttributes();

        $this->assertIsArray($searchAttributes);
    }

    public function test_category_resource_has_global_search_result_details(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $details = CategoryResource::getGlobalSearchResultDetails($category);

        $this->assertIsArray($details);
    }

    public function test_category_resource_has_global_search_result_url(): void
    {
        $category = Category::factory()->create();

        $url = CategoryResource::getGlobalSearchResultUrl($category);

        $this->assertIsString($url);
        $this->assertStringContainsString('categories', $url);
    }

    public function test_category_hierarchy_display(): void
    {
        $parentCategory = Category::factory()->create(['name' => 'Parent Category']);
        $childCategory = Category::factory()->create([
            'name' => 'Child Category',
            'parent_id' => $parentCategory->id
        ]);

        $this->actingAs($this->user)
            ->get(CategoryResource::getUrl('index'))
            ->assertSee('Parent Category')
            ->assertSee('Child Category');
    }
}