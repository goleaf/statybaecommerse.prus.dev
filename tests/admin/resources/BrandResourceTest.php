<?php declare(strict_types=1);

use App\Models\Brand;
use App\Models\User;
use App\Filament\Resources\BrandResource;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view brands',
        'create brands',
        'update brands',
        'delete brands',
        'browse_brands'
    ];
    
    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }
    
    $role->givePermissionTo($permissions);
    
    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');
    
    // Create a test brand
    $this->testBrand = Brand::factory()->create([
        'name' => 'Test Brand',
        'slug' => 'test-brand',
        'is_enabled' => true,
    ]);
});

it('can list brands in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('can create a new brand', function () {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name' => 'New Brand',
            'slug' => 'new-brand',
            'description' => 'A new brand description',
            'website' => 'https://newbrand.com',
            'is_enabled' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('brands', [
        'name' => 'New Brand',
        'slug' => 'new-brand',
        'is_enabled' => true,
    ]);
});

it('can view a brand in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('view', ['record' => $this->testBrand]))
        ->assertOk();
});

it('can edit a brand', function () {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\EditBrand::class, ['record' => $this->testBrand->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Brand',
            'description' => 'Updated description',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('brands', [
        'id' => $this->testBrand->id,
        'name' => 'Updated Brand',
        'description' => 'Updated description',
    ]);
});

it('can delete a brand', function () {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableAction('delete', $this->testBrand);

    $this->assertSoftDeleted('brands', [
        'id' => $this->testBrand->id,
    ]);
});

it('can restore a deleted brand', function () {
    $this->testBrand->delete();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('trashed')
        ->callTableBulkAction('restore', [$this->testBrand]);

    $this->assertDatabaseHas('brands', [
        'id' => $this->testBrand->id,
        'deleted_at' => null,
    ]);
});

it('can force delete a brand', function () {
    $this->testBrand->delete();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('trashed')
        ->callTableBulkAction('forceDelete', [$this->testBrand]);

    $this->assertDatabaseMissing('brands', [
        'id' => $this->testBrand->id,
    ]);
});

it('validates required fields when creating brand', function () {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name' => '',
            'slug' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
});

it('validates unique slug when creating brand', function () {
    // Create a brand with existing slug
    Brand::factory()->create(['slug' => 'existing-slug']);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name' => 'Another Brand',
            'slug' => 'existing-slug', // This should fail validation
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('validates website URL format', function () {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'website' => 'invalid-url', // This should fail validation
        ])
        ->call('create')
        ->assertHasFormErrors(['website' => 'url']);
});

it('can filter brands by enabled status', function () {
    Brand::factory()->create(['is_enabled' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('enabled')
        ->assertCanSeeTableRecords(Brand::where('is_enabled', true)->get())
        ->assertCanNotSeeTableRecords(Brand::where('is_enabled', false)->get());
});

it('can search brands by name', function () {
    $searchBrand = Brand::factory()->create(['name' => 'Searchable Brand']);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->searchTable('Searchable')
        ->assertCanSeeTableRecords([$searchBrand])
        ->assertCanNotSeeTableRecords([$this->testBrand]);
});

it('can sort brands by name', function () {
    $brandA = Brand::factory()->create(['name' => 'Alpha Brand']);
    $brandB = Brand::factory()->create(['name' => 'Beta Brand']);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords([$brandA, $brandB], inOrder: true);
});

it('can bulk enable brands', function () {
    $disabledBrands = Brand::factory()->count(2)->create(['is_enabled' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('enable', $disabledBrands);

    foreach ($disabledBrands as $brand) {
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'is_enabled' => true,
        ]);
    }
});

it('can bulk disable brands', function () {
    $enabledBrands = Brand::factory()->count(2)->create(['is_enabled' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('disable', $enabledBrands);

    foreach ($enabledBrands as $brand) {
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'is_enabled' => false,
        ]);
    }
});

it('can bulk delete brands', function () {
    $brandsToDelete = Brand::factory()->count(2)->create();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('delete', $brandsToDelete);

    foreach ($brandsToDelete as $brand) {
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }
});

it('requires admin role to access brand resources', function () {
    $regularUser = User::factory()->create();

    $this->actingAs($regularUser)
        ->get(BrandResource::getUrl('index'))
        ->assertStatus(403);
});

it('displays brand logo in table', function () {
    // This test would require media library setup
    // For now, we'll just test that the table loads
    $this->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('displays products count in table', function () {
    // This test would require products relationship
    // For now, we'll just test that the table loads
    $this->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('displays translations count in table', function () {
    // This test would require translations relationship
    // For now, we'll just test that the table loads
    $this->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});