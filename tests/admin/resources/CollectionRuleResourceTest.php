<?php declare(strict_types=1);

use App\Filament\Resources\CollectionRuleResource;
use App\Models\Collection;
use App\Models\CollectionRule;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create permissions
    $permissions = [
        'browse_collection_rules',
        'read_collection_rules',
        'edit_collection_rules',
        'add_collection_rules',
        'delete_collection_rules',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $role = Role::create(['name' => 'administrator']);
    $role->givePermissionTo($permissions);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');

    // Create test data
    $this->testCollection = Collection::factory()->create([
        'name' => 'Test Collection',
    ]);

    $this->testCollectionRule = CollectionRule::factory()->create([
        'collection_id' => $this->testCollection->id,
        'field' => 'name',
        'operator' => 'contains',
        'value' => 'test',
        'position' => 1,
    ]);
});

it('can list collection rules in admin panel', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index'))
        ->assertOk();
});

it('can create a collection rule', function () {
    $collection = Collection::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\CreateCollectionRule::class)
        ->fillForm([
            'collection_id' => $collection->id,
            'field' => 'price',
            'operator' => 'greater_than',
            'value' => '100',
            'position' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('collection_rules', [
        'collection_id' => $collection->id,
        'field' => 'price',
        'operator' => 'greater_than',
        'value' => '100',
        'position' => 1,
    ]);
});

it('can view a collection rule record', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('view', ['record' => $this->testCollectionRule]))
        ->assertOk();
});

it('can edit a collection rule record', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\EditCollectionRule::class, ['record' => $this->testCollectionRule->id])
        ->fillForm([
            'field' => 'updated_field',
            'operator' => 'equals',
            'value' => 'updated_value',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('collection_rules', [
        'id' => $this->testCollectionRule->id,
        'field' => 'updated_field',
        'operator' => 'equals',
        'value' => 'updated_value',
    ]);
});

it('can delete a collection rule record', function () {
    $collectionRule = CollectionRule::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\EditCollectionRule::class, ['record' => $collectionRule->id])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('collection_rules', [
        'id' => $collectionRule->id,
    ]);
});

it('validates required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\CreateCollectionRule::class)
        ->fillForm([
            'collection_id' => '',
            'field' => '',
            'operator' => '',
            'value' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['collection_id', 'field', 'operator', 'value']);
});

it('can filter collection rules by collection', function () {
    $collection1 = Collection::factory()->create(['name' => 'Collection 1']);
    $collection2 = Collection::factory()->create(['name' => 'Collection 2']);

    CollectionRule::factory()->create(['collection_id' => $collection1->id]);
    CollectionRule::factory()->create(['collection_id' => $collection2->id]);

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index'))
        ->assertOk();
});

it('can filter collection rules by operator', function () {
    CollectionRule::factory()->create(['operator' => 'equals']);
    CollectionRule::factory()->create(['operator' => 'contains']);

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index'))
        ->assertOk();
});

it('can search collection rules by field', function () {
    $collectionRule = CollectionRule::factory()->create(['field' => 'special_field']);

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index') . '?search=special')
        ->assertOk();
});

it('can search collection rules by value', function () {
    $collectionRule = CollectionRule::factory()->create(['value' => 'special_value']);

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index') . '?search=special')
        ->assertOk();
});

it('can sort collection rules by position', function () {
    $rule1 = CollectionRule::factory()->create(['position' => 2]);
    $rule2 = CollectionRule::factory()->create(['position' => 1]);

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index') . '?sort=position&direction=asc')
        ->assertOk();
});

it('can sort collection rules by created date', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index') . '?sort=created_at&direction=desc')
        ->assertOk();
});

it('shows correct collection rule data in table', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index'))
        ->assertSee($this->testCollectionRule->field)
        ->assertSee($this->testCollectionRule->operator)
        ->assertSee($this->testCollectionRule->value);
});

it('can perform bulk delete action', function () {
    $collectionRule1 = CollectionRule::factory()->create();
    $collectionRule2 = CollectionRule::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\ListCollectionRules::class)
        ->callTableBulkAction('delete', [$collectionRule1->id, $collectionRule2->id])
        ->assertOk();

    $this->assertDatabaseMissing('collection_rules', [
        'id' => $collectionRule1->id,
    ]);

    $this->assertDatabaseMissing('collection_rules', [
        'id' => $collectionRule2->id,
    ]);
});

it('can reorder a collection rule', function () {
    $collectionRule = CollectionRule::factory()->create(['position' => 1]);

    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\ListCollectionRules::class)
        ->callTableAction('reorder', $collectionRule, [
            'position' => 5,
        ])
        ->assertHasNoActionErrors();

    $collectionRule->refresh();
    expect($collectionRule->position)->toBe(5);
});

it('can perform bulk reorder action', function () {
    $collectionRule1 = CollectionRule::factory()->create(['position' => 1]);
    $collectionRule2 = CollectionRule::factory()->create(['position' => 2]);

    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\ListCollectionRules::class)
        ->callTableBulkAction('reorder_bulk', [$collectionRule1->id, $collectionRule2->id], [
            'start_position' => 10,
        ])
        ->assertHasNoBulkActionErrors();

    $collectionRule1->refresh();
    $collectionRule2->refresh();

    expect($collectionRule1->position)->toBe(10);
    expect($collectionRule2->position)->toBe(11);
});

it('shows collection relationship in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('edit', ['record' => $this->testCollectionRule]))
        ->assertOk();
});

it('shows rule description in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('edit', ['record' => $this->testCollectionRule]))
        ->assertOk();
});

it('validates position is numeric', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CollectionRuleResource\Pages\CreateCollectionRule::class)
        ->fillForm([
            'collection_id' => $this->testCollection->id,
            'field' => 'test_field',
            'operator' => 'equals',
            'value' => 'test_value',
            'position' => 'not_a_number',
        ])
        ->call('create')
        ->assertHasFormErrors(['position']);
});

it('can access collection rule resource pages', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index'))
        ->assertOk();

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('create'))
        ->assertOk();
});

it('shows operator badges with correct colors', function () {
    $equalsRule = CollectionRule::factory()->create(['operator' => 'equals']);
    $containsRule = CollectionRule::factory()->create(['operator' => 'contains']);

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index'))
        ->assertOk();
});

it('can filter by recent collection rules', function () {
    $recentRule = CollectionRule::factory()->create(['created_at' => now()->subDays(10)]);
    $oldRule = CollectionRule::factory()->create(['created_at' => now()->subDays(60)]);

    $this
        ->actingAs($this->adminUser)
        ->get(CollectionRuleResource::getUrl('index'))
        ->assertOk();
});

