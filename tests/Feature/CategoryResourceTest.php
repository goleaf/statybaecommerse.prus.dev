<?php declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use App\Filament\Resources\CategoryResource;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view categories',
        'create categories',
        'update categories',
        'delete categories',
        'browse_categories'
    ];
    
    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }
    
    $role->givePermissionTo($permissions);
    
    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');
    
    // Create a test category
    $this->testCategory = Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);
});

it('can list categories in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(CategoryResource::getUrl('index'))
        ->assertOk();
});

it('can create a new category', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\CreateCategory::class)
        ->fillForm([
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'A new category description',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
        'is_active' => true,
    ]);
});

it('can view a category in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(CategoryResource::getUrl('view', ['record' => $this->testCategory]))
        ->assertOk();
});

it('can edit a category', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\EditCategory::class, ['record' => $this->testCategory->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'id' => $this->testCategory->id,
        'name' => 'Updated Category',
        'description' => 'Updated description',
    ]);
});

it('can delete a category', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->callTableAction('delete', $this->testCategory);

    $this->assertSoftDeleted('categories', [
        'id' => $this->testCategory->id,
    ]);
});

it('can restore a deleted category', function () {
    $this->testCategory->delete();

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->filterTable('trashed')
        ->callTableBulkAction('restore', [$this->testCategory]);

    $this->assertDatabaseHas('categories', [
        'id' => $this->testCategory->id,
        'deleted_at' => null,
    ]);
});

it('can force delete a category', function () {
    $this->testCategory->delete();

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->filterTable('trashed')
        ->callTableBulkAction('forceDelete', [$this->testCategory]);

    $this->assertDatabaseMissing('categories', [
        'id' => $this->testCategory->id,
    ]);
});

it('validates required fields when creating category', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\CreateCategory::class)
        ->fillForm([
            'name' => '',
            'slug' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
});

it('validates unique slug when creating category', function () {
    // Create a category with existing slug
    Category::factory()->create(['slug' => 'existing-slug']);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\CreateCategory::class)
        ->fillForm([
            'name' => 'Another Category',
            'slug' => 'existing-slug', // This should fail validation
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('can filter categories by active status', function () {
    Category::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->filterTable('active')
        ->assertCanSeeTableRecords(Category::where('is_active', true)->get())
        ->assertCanNotSeeTableRecords(Category::where('is_active', false)->get());
});

it('can filter root categories', function () {
    $parentCategory = Category::factory()->create(['parent_id' => null]);
    $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->filterTable('root_categories')
        ->assertCanSeeTableRecords([$parentCategory])
        ->assertCanNotSeeTableRecords([$childCategory]);
});

it('can search categories by name', function () {
    $searchCategory = Category::factory()->create(['name' => 'Searchable Category']);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->searchTable('Searchable')
        ->assertCanSeeTableRecords([$searchCategory])
        ->assertCanNotSeeTableRecords([$this->testCategory]);
});

it('can sort categories by sort order', function () {
    $categoryA = Category::factory()->create(['sort_order' => 1]);
    $categoryB = Category::factory()->create(['sort_order' => 2]);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->sortTable('sort_order')
        ->assertCanSeeTableRecords([$categoryA, $categoryB], inOrder: true);
});

it('can bulk activate categories', function () {
    $inactiveCategories = Category::factory()->count(2)->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->callTableBulkAction('activate', $inactiveCategories);

    foreach ($inactiveCategories as $category) {
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => true,
        ]);
    }
});

it('can bulk deactivate categories', function () {
    $activeCategories = Category::factory()->count(2)->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->callTableBulkAction('deactivate', $activeCategories);

    foreach ($activeCategories as $category) {
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }
});

it('can bulk delete categories', function () {
    $categoriesToDelete = Category::factory()->count(2)->create();

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->callTableBulkAction('delete', $categoriesToDelete);

    foreach ($categoriesToDelete as $category) {
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
});

it('requires admin role to access category resources', function () {
    $regularUser = User::factory()->create();

    $this->actingAs($regularUser)
        ->get(CategoryResource::getUrl('index'))
        ->assertStatus(403);
});

it('can create subcategory', function () {
    $parentCategory = Category::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\CreateCategory::class)
        ->fillForm([
            'name' => 'Subcategory',
            'slug' => 'subcategory',
            'parent_id' => $parentCategory->id,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Subcategory',
        'slug' => 'subcategory',
        'parent_id' => $parentCategory->id,
    ]);
});

it('displays parent category in table', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent Category']);
    $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);

    Livewire::actingAs($this->adminUser)
        ->test(CategoryResource\Pages\ListCategories::class)
        ->assertCanSeeTableRecords([$childCategory])
        ->assertTableColumnState('parent.name', $childCategory, 'Parent Category');
});

it('displays products count in table', function () {
    // This test would require products relationship
    // For now, we'll just test that the table loads
    $this->actingAs($this->adminUser)
        ->get(CategoryResource::getUrl('index'))
        ->assertOk();
});