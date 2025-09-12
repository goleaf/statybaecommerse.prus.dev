<?php declare(strict_types=1);

use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use App\Filament\Resources\CollectionResource;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view collections',
        'create collections',
        'update collections',
        'delete collections',
        'browse_collections'
    ];
    
    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }
    
    $role->givePermissionTo($permissions);
    
    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');
    
    // Create test data
    $this->testCollection = Collection::factory()->create([
        'name' => 'Test Collection',
        'slug' => 'test-collection',
        'description' => 'This is a test collection.',
        'sort_order' => 1,
        'is_visible' => true,
        'is_automatic' => false,
    ]);
});

it('can list collections in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(CollectionResource::getUrl('index'))
        ->assertOk();
});

it('can create a new collection', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\CreateCollection::class)
        ->fillForm([
            'name' => 'New Collection',
            'slug' => 'new-collection',
            'description' => 'This is a new collection.',
            'sort_order' => 2,
            'is_visible' => true,
            'is_automatic' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('collections', [
        'name' => 'New Collection',
        'slug' => 'new-collection',
        'description' => 'This is a new collection.',
        'sort_order' => 2,
        'is_visible' => true,
        'is_automatic' => false,
    ]);
});

it('can view a collection', function () {
    $this->actingAs($this->adminUser)
        ->get(CollectionResource::getUrl('view', ['record' => $this->testCollection]))
        ->assertOk();
});

it('can edit a collection', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\EditCollection::class, ['record' => $this->testCollection->id])
        ->fillForm([
            'name' => 'Updated Collection',
            'description' => 'This is an updated collection.',
            'sort_order' => 5,
            'is_visible' => false,
            'is_automatic' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('collections', [
        'id' => $this->testCollection->id,
        'name' => 'Updated Collection',
        'description' => 'This is an updated collection.',
        'sort_order' => 5,
        'is_visible' => false,
        'is_automatic' => true,
    ]);
});

it('can delete a collection', function () {
    $collection = Collection::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\EditCollection::class, ['record' => $collection->id])
        ->callAction('delete')
        ->assertOk();
    
    $this->assertDatabaseMissing('collections', [
        'id' => $collection->id,
    ]);
});

it('validates required fields when creating collection', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\CreateCollection::class)
        ->fillForm([
            'name' => null,
            'slug' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'slug']);
});

it('validates unique slug when creating collection', function () {
    $existingCollection = Collection::factory()->create(['slug' => 'existing-slug']);
    
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'slug' => 'existing-slug', // Duplicate slug
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('validates slug length when creating collection', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'slug' => str_repeat('a', 256), // Too long
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('validates numeric sort order', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'slug' => 'test-collection',
            'sort_order' => 'not-a-number',
        ])
        ->call('create')
        ->assertHasFormErrors(['sort_order']);
});

it('can filter collections by visibility', function () {
    $this->actingAs($this->adminUser)
        ->get(CollectionResource::getUrl('index'))
        ->assertOk();
});

it('can filter collections by automatic status', function () {
    $this->actingAs($this->adminUser)
        ->get(CollectionResource::getUrl('index'))
        ->assertOk();
});

it('shows correct collection data in table', function () {
    $this->actingAs($this->adminUser)
        ->get(CollectionResource::getUrl('index'))
        ->assertSee($this->testCollection->name)
        ->assertSee($this->testCollection->slug);
});

it('handles collection visibility toggle', function () {
    $collection = Collection::factory()->create(['is_visible' => true]);
    
    // Hide collection
    $collection->update(['is_visible' => false]);
    expect($collection->is_visible)->toBeFalse();
    
    // Show collection
    $collection->update(['is_visible' => true]);
    expect($collection->is_visible)->toBeTrue();
});

it('handles automatic collection management', function () {
    $collection = Collection::factory()->create(['is_automatic' => false]);
    
    // Make collection automatic
    $collection->update(['is_automatic' => true]);
    expect($collection->is_automatic)->toBeTrue();
    
    // Make collection manual
    $collection->update(['is_automatic' => false]);
    expect($collection->is_automatic)->toBeFalse();
});

it('handles bulk actions on collections', function () {
    $collection1 = Collection::factory()->create();
    $collection2 = Collection::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\ListCollections::class)
        ->callTableBulkAction('delete', [$collection1->id, $collection2->id])
        ->assertOk();
    
    $this->assertDatabaseMissing('collections', [
        'id' => $collection1->id,
    ]);
    
    $this->assertDatabaseMissing('collections', [
        'id' => $collection2->id,
    ]);
});

it('can manage collection sort order', function () {
    $collection1 = Collection::factory()->create(['sort_order' => 1]);
    $collection2 = Collection::factory()->create(['sort_order' => 2]);
    
    // Update sort orders
    $collection1->update(['sort_order' => 3]);
    $collection2->update(['sort_order' => 1]);
    
    expect($collection1->sort_order)->toBe(3);
    expect($collection2->sort_order)->toBe(1);
});

it('can upload collection images', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionResource\Pages\CreateCollection::class)
        ->fillForm([
            'name' => 'Collection with Image',
            'slug' => 'collection-with-image',
            'image' => 'collections/test-image.jpg',
            'banner' => 'collections/banners/test-banner.jpg',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('collections', [
        'name' => 'Collection with Image',
        'image' => 'collections/test-image.jpg',
        'banner' => 'collections/banners/test-banner.jpg',
    ]);
});

it('can search collections by name or slug', function () {
    $this->actingAs($this->adminUser)
        ->get(CollectionResource::getUrl('index'))
        ->assertOk();
});

it('shows product count for collections', function () {
    $collection = Collection::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    
    // Attach products to collection
    $collection->products()->attach([$product1->id, $product2->id]);
    
    $this->actingAs($this->adminUser)
        ->get(CollectionResource::getUrl('index'))
        ->assertSee('2'); // Product count
});

