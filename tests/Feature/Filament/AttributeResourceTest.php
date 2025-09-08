<?php declare(strict_types=1);

use App\Filament\Resources\AttributeResource;
use App\Models\Attribute;
use App\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing};

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('can render attribute resource index page', function () {
    actingAs($this->admin)
        ->get(AttributeResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render attribute resource create page', function () {
    actingAs($this->admin)
        ->get(AttributeResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create attribute', function () {
    $newData = [
        'name' => 'Test Attribute',
        'slug' => 'test-attribute',
        'type' => 'text',
        'is_required' => false,
        'is_filterable' => true,
        'is_searchable' => false,
        'sort_order' => 0,
    ];

    actingAs($this->admin)
        ->post(AttributeResource::getUrl('create'), $newData)
        ->assertRedirect();

    assertDatabaseHas('attributes', $newData);
});

it('can render attribute resource view page', function () {
    $attribute = Attribute::factory()->create();

    actingAs($this->admin)
        ->get(AttributeResource::getUrl('view', ['record' => $attribute]))
        ->assertSuccessful();
});

it('can render attribute resource edit page', function () {
    $attribute = Attribute::factory()->create();

    actingAs($this->admin)
        ->get(AttributeResource::getUrl('edit', ['record' => $attribute]))
        ->assertSuccessful();
});

it('can update attribute', function () {
    $attribute = Attribute::factory()->create();
    $newData = [
        'name' => 'Updated Attribute',
        'slug' => 'updated-attribute',
        'type' => 'select',
        'is_required' => true,
        'is_filterable' => false,
        'is_searchable' => true,
        'sort_order' => 10,
    ];

    actingAs($this->admin)
        ->put(AttributeResource::getUrl('edit', ['record' => $attribute]), $newData)
        ->assertRedirect();

    assertDatabaseHas('attributes', array_merge(['id' => $attribute->id], $newData));
});

it('can delete attribute', function () {
    $attribute = Attribute::factory()->create();

    actingAs($this->admin)
        ->delete(AttributeResource::getUrl('edit', ['record' => $attribute]))
        ->assertRedirect();

    assertDatabaseMissing('attributes', ['id' => $attribute->id]);
});

it('can list attributes', function () {
    $attributes = Attribute::factory()->count(10)->create();

    actingAs($this->admin)
        ->get(AttributeResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSeeText($attributes->first()->name);
});

it('can search attributes', function () {
    $attribute = Attribute::factory()->create(['name' => 'Searchable Attribute']);
    Attribute::factory()->create(['name' => 'Other Attribute']);

    actingAs($this->admin)
        ->get(AttributeResource::getUrl('index') . '?search=Searchable')
        ->assertSuccessful()
        ->assertSeeText('Searchable Attribute')
        ->assertDontSeeText('Other Attribute');
});

it('can filter attributes by type', function () {
    $textAttribute = Attribute::factory()->create(['type' => 'text']);
    $numberAttribute = Attribute::factory()->create(['type' => 'number']);

    actingAs($this->admin)
        ->get(AttributeResource::getUrl('index') . '?filter[type]=text')
        ->assertSuccessful()
        ->assertSeeText($textAttribute->name)
        ->assertDontSeeText($numberAttribute->name);
});

it('validates required fields when creating attribute', function () {
    actingAs($this->admin)
        ->post(AttributeResource::getUrl('create'), [])
        ->assertSessionHasErrors(['name', 'slug', 'type']);
});

it('validates unique slug when creating attribute', function () {
    $existingAttribute = Attribute::factory()->create(['slug' => 'existing-slug']);

    actingAs($this->admin)
        ->post(AttributeResource::getUrl('create'), [
            'name' => 'Test Attribute',
            'slug' => 'existing-slug',
            'type' => 'text',
        ])
        ->assertSessionHasErrors(['slug']);
});
