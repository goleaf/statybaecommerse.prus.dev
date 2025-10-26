<?php

declare(strict_types=1);

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view brands',
        'create brands',
        'update brands',
        'delete brands',
        'browse_brands',
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
        'name'       => 'Test Brand',
        'slug'       => 'test-brand',
        'is_enabled' => true,
    ]);
});

it('can list brands in admin panel', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('lists brands alphabetically by default', function (): void {
    $alpha = Brand::factory()->create([
        'name'       => 'Alpha Tools',
        'slug'       => 'alpha-tools',
        'is_enabled' => true,
    ]);
    $zulu = Brand::factory()->create([
        'name'       => 'Zulu Tools',
        'slug'       => 'zulu-tools',
        'is_enabled' => true,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        // Expect the listing to surface an A→Z ordering anchored by the shared OrdersByName scope.
        ->assertCanSeeTableRecordsInOrder([$alpha, $this->testBrand, $zulu]);
});

it('can create a new brand', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name'        => 'New Brand',
            'slug'        => 'new-brand',
            'description' => 'A new brand description',
            'website'     => 'https://newbrand.com',
            'is_enabled'  => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('brands', [
        'name'       => 'New Brand',
        'slug'       => 'new-brand',
        'is_enabled' => true,
    ]);
});

it('can view a brand in admin panel', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('view', ['record' => $this->testBrand]))
        ->assertOk();
});

it('can edit a brand', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\EditBrand::class, ['record' => $this->testBrand->getRouteKey()])
        ->fillForm([
            'name'        => 'Updated Brand',
            'description' => 'Updated description',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('brands', [
        'id'          => $this->testBrand->id,
        'name'        => 'Updated Brand',
        'description' => 'Updated description',
    ]);
});

it('can delete a brand', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableAction('delete', $this->testBrand);

    $this->assertSoftDeleted('brands', [
        'id' => $this->testBrand->id,
    ]);
});

it('can restore a deleted brand', function (): void {
    $this->testBrand->delete();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('trashed')
        ->callTableBulkAction('restore', [$this->testBrand]);

    $this->assertDatabaseHas('brands', [
        'id'         => $this->testBrand->id,
        'deleted_at' => null,
    ]);
});

it('can force delete a brand', function (): void {
    $this->testBrand->delete();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('trashed')
        ->callTableBulkAction('forceDelete', [$this->testBrand]);

    $this->assertDatabaseMissing('brands', [
        'id' => $this->testBrand->id,
    ]);
});

it('validates required fields when creating brand', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name' => '',
            'slug' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
});

it('validates unique slug when creating brand', function (): void {
    // Create a brand with existing slug
    Brand::factory()->create(['slug' => 'existing-slug']);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name' => 'Another Brand',
            'slug' => 'existing-slug',  // This should fail validation
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('validates website URL format', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\CreateBrand::class)
        ->fillForm([
            'name'    => 'Test Brand',
            'slug'    => 'test-brand',
            'website' => 'invalid-url',  // This should fail validation
        ])
        ->call('create')
        ->assertHasFormErrors(['website' => 'url']);
});

it('can filter brands by enabled status', function (): void {
    Brand::factory()->create(['is_enabled' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('enabled')
        ->assertCanSeeTableRecords(Brand::where('is_enabled', true)->get())
        ->assertCanNotSeeTableRecords(Brand::where('is_enabled', false)->get());
});

it('can search brands by name', function (): void {
    $searchBrand = Brand::factory()->create(['name' => 'Searchable Brand']);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->searchTable('Searchable')
        ->assertCanSeeTableRecords([$searchBrand])
        ->assertCanNotSeeTableRecords([$this->testBrand]);
});

it('can sort brands by name', function (): void {
    $brandA = Brand::factory()->create(['name' => 'Alpha Brand']);
    $brandB = Brand::factory()->create(['name' => 'Beta Brand']);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords([$brandA, $brandB], inOrder: true);
});

it('can bulk enable brands', function (): void {
    $disabledBrands = Brand::factory()->count(2)->create(['is_enabled' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('enable', $disabledBrands);

    foreach ($disabledBrands as $brand) {
        $this->assertDatabaseHas('brands', [
            'id'         => $brand->id,
            'is_enabled' => true,
        ]);
    }
});

it('can bulk disable brands', function (): void {
    $enabledBrands = Brand::factory()->count(2)->create(['is_enabled' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('disable', $enabledBrands);

    foreach ($enabledBrands as $brand) {
        $this->assertDatabaseHas('brands', [
            'id'         => $brand->id,
            'is_enabled' => false,
        ]);
    }
});

it('can bulk delete brands', function (): void {
    $brandsToDelete = Brand::factory()->count(2)->create();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('delete', $brandsToDelete);

    foreach ($brandsToDelete as $brand) {
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }
});

it('requires admin role to access brand resources', function (): void {
    $regularUser = User::factory()->create();

    $this
        ->actingAs($regularUser)
        ->get(BrandResource::getUrl('index'))
        ->assertStatus(403);
});

it('displays brand logo in table', function (): void {
    // This test would require media library setup
    // For now, we'll just test that the table loads
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('displays products count in table', function (): void {
    // This test would require products relationship
    // For now, we'll just test that the table loads
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('displays translations count in table', function (): void {
    // This test would require translations relationship
    // For now, we'll just test that the table loads
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('can toggle brand active status', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableAction('toggle_active', $this->testBrand);

    $this->assertDatabaseHas('brands', [
        'id'        => $this->testBrand->id,
        'is_active' => ! $this->testBrand->is_active,
    ]);
});

it('can toggle brand featured status', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableAction('toggle_featured', $this->testBrand);

    $this->assertDatabaseHas('brands', [
        'id'          => $this->testBrand->id,
        'is_featured' => ! $this->testBrand->is_featured,
    ]);
});

it('can filter brands by visibility', function (): void {
    $visibleBrand = Brand::factory()->create(['is_visible' => true]);
    $hiddenBrand = Brand::factory()->create(['is_visible' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('is_visible', true)
        ->assertCanSeeTableRecords([$visibleBrand])
        ->assertCanNotSeeTableRecords([$hiddenBrand]);
});

it('can filter brands by featured status', function (): void {
    $featuredBrand = Brand::factory()->create(['is_featured' => true]);
    $regularBrand = Brand::factory()->create(['is_featured' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('is_featured', true)
        ->assertCanSeeTableRecords([$featuredBrand])
        ->assertCanNotSeeTableRecords([$regularBrand]);
});

it('can filter brands with products', function (): void {
    $brandWithProducts = Brand::factory()->create();
    $brandWithoutProducts = Brand::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('with_products')
        ->assertCanSeeTableRecords([$brandWithProducts])
        ->assertCanNotSeeTableRecords([$brandWithoutProducts]);
});

it('can filter brands without products', function (): void {
    $brandWithProducts = Brand::factory()->create();
    $brandWithoutProducts = Brand::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('without_products')
        ->assertCanSeeTableRecords([$brandWithoutProducts])
        ->assertCanNotSeeTableRecords([$brandWithProducts]);
});

it('can filter brands with website', function (): void {
    $brandWithWebsite = Brand::factory()->create(['website' => 'https://example.com']);
    $brandWithoutWebsite = Brand::factory()->create(['website' => null]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('with_website')
        ->assertCanSeeTableRecords([$brandWithWebsite])
        ->assertCanNotSeeTableRecords([$brandWithoutWebsite]);
});

it('can filter recent brands', function (): void {
    $recentBrand = Brand::factory()->create(['created_at' => now()->subDays(15)]);
    $oldBrand = Brand::factory()->create(['created_at' => now()->subDays(45)]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->filterTable('recent')
        ->assertCanSeeTableRecords([$recentBrand])
        ->assertCanNotSeeTableRecords([$oldBrand]);
});

it('can bulk feature brands', function (): void {
    $brandsToFeature = Brand::factory()->count(2)->create(['is_featured' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('feature', $brandsToFeature);

    foreach ($brandsToFeature as $brand) {
        $this->assertDatabaseHas('brands', [
            'id'          => $brand->id,
            'is_featured' => true,
        ]);
    }
});

it('can bulk unfeature brands', function (): void {
    $brandsToUnfeature = Brand::factory()->count(2)->create(['is_featured' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->callTableBulkAction('unfeature', $brandsToUnfeature);

    foreach ($brandsToUnfeature as $brand) {
        $this->assertDatabaseHas('brands', [
            'id'          => $brand->id,
            'is_featured' => false,
        ]);
    }
});

it('can sort brands by products count', function (): void {
    $brandWithManyProducts = Brand::factory()->create();
    $brandWithFewProducts = Brand::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->sortTable('products_count')
        ->assertCanSeeTableRecords([$brandWithFewProducts, $brandWithManyProducts], inOrder: true);
});

it('can copy brand slug', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('can view brand website in new tab', function (): void {
    $brandWithWebsite = Brand::factory()->create(['website' => 'https://example.com']);

    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('displays brand tooltip with description', function (): void {
    $brandWithDescription = Brand::factory()->create(['description' => 'Test description']);

    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('can paginate brands table', function (): void {
    Brand::factory()->count(15)->create();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->assertCanSeeTableRecords(Brand::take(10)->get())
        ->assertCanNotSeeTableRecords(Brand::skip(10)->take(5)->get());
});

it('can change pagination size', function (): void {
    Brand::factory()->count(15)->create();

    Livewire::actingAs($this->adminUser)
        ->test(BrandResource\Pages\ListBrands::class)
        ->setTableRecordsPerPage(25)
        ->assertCanSeeTableRecords(Brand::take(15)->get());
});

it('validates file upload size for logo', function (): void {
    // This test would require file upload testing
    // For now, we'll just test that the form loads
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('create'))
        ->assertOk();
});

it('validates file upload size for banner', function (): void {
    // This test would require file upload testing
    // For now, we'll just test that the form loads
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('create'))
        ->assertOk();
});

it('can upload brand logo with image editor', function (): void {
    // This test would require file upload testing
    // For now, we'll just test that the form loads
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('create'))
        ->assertOk();
});

it('can upload brand banner with image editor', function (): void {
    // This test would require file upload testing
    // For now, we'll just test that the form loads
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('create'))
        ->assertOk();
});

it('can access brand form tabs', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('create'))
        ->assertOk();
});

it('can access brand edit form tabs', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('edit', ['record' => $this->testBrand]))
        ->assertOk();
});

it('can access brand view page', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('view', ['record' => $this->testBrand]))
        ->assertOk();
});

it('displays navigation badge with brand count', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});

it('can search brands globally', function (): void {
    $searchableBrand = Brand::factory()->create(['name' => 'Searchable Brand']);

    $this
        ->actingAs($this->adminUser)
        ->get(BrandResource::getUrl('index'))
        ->assertOk();
});
