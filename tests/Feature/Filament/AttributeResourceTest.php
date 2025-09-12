<?php declare(strict_types=1);

use App\Filament\Resources\AttributeResource;
use App\Models\Attribute;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing};

beforeEach(function () {
    $this->admin = User::factory()->create();
    Role::findOrCreate('admin', config('auth.defaults.guard', 'web'));
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
        'is_visible' => true,
        'is_editable' => true,
        'is_sortable' => true,
        'is_enabled' => true,
        'sort_order' => 0,
    ];

    Livewire::test(AttributeResource\Pages\CreateAttribute::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

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

    Livewire::test(AttributeResource\Pages\EditAttribute::class, [
        'record' => $attribute->getRouteKey(),
    ])->assertStatus(200);
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
        'is_visible' => true,
        'is_editable' => true,
        'is_sortable' => true,
        'is_enabled' => true,
        'sort_order' => 10,
    ];

    Livewire::test(AttributeResource\Pages\EditAttribute::class, [
        'record' => $attribute->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas('attributes', array_merge(['id' => $attribute->id], $newData));
});

it('can delete attribute', function () {
    $attribute = Attribute::factory()->create();

    Livewire::test(AttributeResource\Pages\EditAttribute::class, [
        'record' => $attribute->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    assertDatabaseMissing('attributes', ['id' => $attribute->id]);
});

it('can list attributes', function () {
    $attributes = Attribute::factory()->count(10)->create();

    Livewire::test(AttributeResource\Pages\ListAttributes::class)
        ->assertCanSeeTableRecords($attributes);
});

it('can search attributes', function () {
    $attribute = Attribute::factory()->create(['name' => 'Searchable Attribute']);
    $other = Attribute::factory()->create(['name' => 'Other Attribute']);

    Livewire::test(AttributeResource\Pages\ListAttributes::class)
        ->searchTable('Searchable')
        ->assertCanSeeTableRecords([$attribute])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can filter attributes by type', function () {
    $textAttribute = Attribute::factory()->create(['type' => 'text']);
    $numberAttribute = Attribute::factory()->create(['type' => 'number']);

    Livewire::test(AttributeResource\Pages\ListAttributes::class)
        ->filterTable('type', 'text')
        ->assertCanSeeTableRecords([$textAttribute])
        ->assertCanNotSeeTableRecords([$numberAttribute]);
});

it('validates required fields when creating attribute', function () {
    Livewire::test(AttributeResource\Pages\CreateAttribute::class)
        ->fillForm([])
        ->call('create')
        ->assertHasFormErrors(['name', 'slug', 'type']);
});

it('validates unique slug when creating attribute', function () {
    $existingAttribute = Attribute::factory()->create(['slug' => 'existing-slug']);

    Livewire::test(AttributeResource\Pages\CreateAttribute::class)
        ->fillForm([
            'name' => 'Test Attribute',
            'slug' => 'existing-slug',
            'type' => 'text',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});
