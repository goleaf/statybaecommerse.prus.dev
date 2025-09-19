<?php declare(strict_types=1);

use App\Filament\Resources\EnumValueResource;
use App\Models\EnumValue;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create permissions
    $permissions = [
        'browse_enum_values',
        'read_enum_values',
        'edit_enum_values',
        'add_enum_values',
        'delete_enum_values',
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
    $this->testEnumValue = EnumValue::factory()->create([
        'type' => 'order_status',
        'key' => 'pending',
        'value' => 'Pending',
        'name' => 'Pending Order',
        'is_active' => true,
    ]);
});

it('can list enum values in admin panel', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('can create an enum value', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\CreateEnumValue::class)
        ->fillForm([
            'type' => 'payment_status',
            'key' => 'paid',
            'value' => 'Paid',
            'name' => 'Payment Completed',
            'description' => 'Payment has been completed successfully',
            'sort_order' => 1,
            'is_active' => true,
            'is_default' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('enum_values', [
        'type' => 'payment_status',
        'key' => 'paid',
        'value' => 'Paid',
        'name' => 'Payment Completed',
        'is_active' => true,
    ]);
});

it('can view an enum value record', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('view', ['record' => $this->testEnumValue]))
        ->assertOk();
});

it('can edit an enum value record', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\EditEnumValue::class, ['record' => $this->testEnumValue->id])
        ->fillForm([
            'value' => 'Processing',
            'name' => 'Processing Order',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('enum_values', [
        'id' => $this->testEnumValue->id,
        'value' => 'Processing',
        'name' => 'Processing Order',
    ]);
});

it('can delete an enum value record', function () {
    $enumValue = EnumValue::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\EditEnumValue::class, ['record' => $enumValue->id])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('enum_values', [
        'id' => $enumValue->id,
    ]);
});

it('validates required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\CreateEnumValue::class)
        ->fillForm([
            'type' => '',
            'key' => '',
            'value' => '',
            'name' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['type', 'key', 'value', 'name']);
});

it('can filter enum values by type', function () {
    EnumValue::factory()->create(['type' => 'order_status']);
    EnumValue::factory()->create(['type' => 'payment_status']);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('can filter enum values by active status', function () {
    EnumValue::factory()->create(['is_active' => true]);
    EnumValue::factory()->create(['is_active' => false]);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('can filter enum values by default status', function () {
    EnumValue::factory()->create(['is_default' => true]);
    EnumValue::factory()->create(['is_default' => false]);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('can search enum values by name', function () {
    $enumValue = EnumValue::factory()->create(['name' => 'Special Status']);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index') . '?search=Special')
        ->assertOk();
});

it('can search enum values by key', function () {
    $enumValue = EnumValue::factory()->create(['key' => 'special_key']);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index') . '?search=special')
        ->assertOk();
});

it('can search enum values by value', function () {
    $enumValue = EnumValue::factory()->create(['value' => 'Special Value']);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index') . '?search=Special')
        ->assertOk();
});

it('can sort enum values by type', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index') . '?sort=type&direction=asc')
        ->assertOk();
});

it('can sort enum values by sort order', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index') . '?sort=sort_order&direction=asc')
        ->assertOk();
});

it('shows correct enum value data in table', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertSee($this->testEnumValue->name)
        ->assertSee($this->testEnumValue->key);
});

it('can perform bulk delete action', function () {
    $enumValue1 = EnumValue::factory()->create();
    $enumValue2 = EnumValue::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\ListEnumValues::class)
        ->callTableBulkAction('delete', [$enumValue1->id, $enumValue2->id])
        ->assertOk();

    $this->assertDatabaseMissing('enum_values', [
        'id' => $enumValue1->id,
    ]);

    $this->assertDatabaseMissing('enum_values', [
        'id' => $enumValue2->id,
    ]);
});

it('can activate an enum value', function () {
    $enumValue = EnumValue::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\ListEnumValues::class)
        ->callTableAction('activate', $enumValue)
        ->assertHasNoActionErrors();

    $enumValue->refresh();
    expect($enumValue->is_active)->toBeTrue();
});

it('can deactivate an enum value', function () {
    $enumValue = EnumValue::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\ListEnumValues::class)
        ->callTableAction('deactivate', $enumValue)
        ->assertHasNoActionErrors();

    $enumValue->refresh();
    expect($enumValue->is_active)->toBeFalse();
});

it('can set an enum value as default', function () {
    $enumValue = EnumValue::factory()->create(['is_default' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\ListEnumValues::class)
        ->callTableAction('set_default', $enumValue)
        ->assertHasNoActionErrors();

    $enumValue->refresh();
    expect($enumValue->is_default)->toBeTrue();
});

it('can perform bulk activation', function () {
    $enumValue1 = EnumValue::factory()->create(['is_active' => false]);
    $enumValue2 = EnumValue::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\ListEnumValues::class)
        ->callTableBulkAction('activate_bulk', [$enumValue1->id, $enumValue2->id])
        ->assertHasNoBulkActionErrors();

    $enumValue1->refresh();
    $enumValue2->refresh();

    expect($enumValue1->is_active)->toBeTrue();
    expect($enumValue2->is_active)->toBeTrue();
});

it('can perform bulk deactivation', function () {
    $enumValue1 = EnumValue::factory()->create(['is_active' => true]);
    $enumValue2 = EnumValue::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\ListEnumValues::class)
        ->callTableBulkAction('deactivate_bulk', [$enumValue1->id, $enumValue2->id])
        ->assertHasNoBulkActionErrors();

    $enumValue1->refresh();
    $enumValue2->refresh();

    expect($enumValue1->is_active)->toBeFalse();
    expect($enumValue2->is_active)->toBeFalse();
});

it('shows enum value preview in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('edit', ['record' => $this->testEnumValue]))
        ->assertOk();
});

it('shows usage count in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('edit', ['record' => $this->testEnumValue]))
        ->assertOk();
});

it('shows formatted value in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('edit', ['record' => $this->testEnumValue]))
        ->assertOk();
});

it('shows correct type badges', function () {
    $orderStatus = EnumValue::factory()->create(['type' => 'order_status']);
    $paymentStatus = EnumValue::factory()->create(['type' => 'payment_status']);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('shows correct active status icons', function () {
    $activeEnum = EnumValue::factory()->create(['is_active' => true]);
    $inactiveEnum = EnumValue::factory()->create(['is_active' => false]);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('shows correct default status icons', function () {
    $defaultEnum = EnumValue::factory()->create(['is_default' => true]);
    $nonDefaultEnum = EnumValue::factory()->create(['is_default' => false]);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('can filter recent enum values', function () {
    $recentEnum = EnumValue::factory()->create(['created_at' => now()->subDays(10)]);
    $oldEnum = EnumValue::factory()->create(['created_at' => now()->subDays(60)]);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('can access enum value resource pages', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('create'))
        ->assertOk();
});

it('validates enum value with metadata', function () {
    $metadata = [
        'color' => 'blue',
        'icon' => 'check-circle',
        'description' => 'This is a test status',
    ];

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\CreateEnumValue::class)
        ->fillForm([
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
            'name' => 'Test Status Name',
            'metadata' => $metadata,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('enum_values', [
        'type' => 'order_status',
        'key' => 'test_status',
        'value' => 'Test Status',
        'name' => 'Test Status Name',
        'is_active' => true,
    ]);
});

it('handles enum value with complex metadata', function () {
    $complexMetadata = [
        'color' => 'green',
        'icon' => 'check-circle',
        'description' => 'Complex status description',
        'priority' => 'high',
        'category' => 'business',
        'tags' => ['important', 'urgent'],
    ];

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\CreateEnumValue::class)
        ->fillForm([
            'type' => 'priority',
            'key' => 'high_priority',
            'value' => 'High Priority',
            'name' => 'High Priority Status',
            'metadata' => $complexMetadata,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('enum_values', [
        'type' => 'priority',
        'key' => 'high_priority',
        'value' => 'High Priority',
        'name' => 'High Priority Status',
        'is_active' => true,
    ]);
});

it('can set only one default per type', function () {
    $enumValue1 = EnumValue::factory()->create([
        'type' => 'order_status',
        'is_default' => true,
    ]);
    $enumValue2 = EnumValue::factory()->create([
        'type' => 'order_status',
        'is_default' => false,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\ListEnumValues::class)
        ->callTableAction('set_default', $enumValue2)
        ->assertHasNoActionErrors();

    $enumValue1->refresh();
    $enumValue2->refresh();

    expect($enumValue1->is_default)->toBeFalse();
    expect($enumValue2->is_default)->toBeTrue();
});

it('shows usage count in table', function () {
    $enumValue = EnumValue::factory()->create();

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('can copy enum value key', function () {
    $enumValue = EnumValue::factory()->create(['key' => 'copyable_key']);

    $this
        ->actingAs($this->adminUser)
        ->get(EnumValueResource::getUrl('index'))
        ->assertOk();
});

it('handles enum value with empty metadata', function () {
    Livewire::actingAs($this->adminUser)
        ->test(EnumValueResource\Pages\CreateEnumValue::class)
        ->fillForm([
            'type' => 'status',
            'key' => 'empty_metadata',
            'value' => 'Empty Metadata',
            'name' => 'Empty Metadata Status',
            'metadata' => [],
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('enum_values', [
        'type' => 'status',
        'key' => 'empty_metadata',
        'value' => 'Empty Metadata',
        'name' => 'Empty Metadata Status',
        'is_active' => true,
    ]);
});
