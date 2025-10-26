<?php

declare(strict_types=1);

use App\Filament\Resources\EnumManagementResource;
use App\Filament\Resources\EnumManagementResource\Pages;
use App\Models\EnumValue;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->adminUser = User::factory()->create([
        'email' => 'admin-enums@example.com',
    ]);
    $this->adminUser->assignRole('super_admin');

    $this->seedEnum = EnumValue::factory()->create([
        'type'        => 'order_status',
        'key'         => 'pending',
        'value'       => 'Pending',
        'name'        => 'Pending Order',
        'description' => 'Order awaiting processing',
        'is_active'   => true,
        'is_default'  => false,
        'metadata'    => ['category' => 'orders'],
    ]);
});

it('can list enum values via enum management resource', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumManagementResource::getUrl('index'))
        ->assertOk()
        ->assertSee(trans('admin.enums.plural'));
});

it('registers auxiliary enum management pages', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumManagementResource::getUrl('enums'))
        ->assertOk();
});

it('can create an enum value through enum management resource', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(Pages\CreateEnumManagement::class)
        ->fillForm([
            'type'        => 'product_status',
            'key'         => 'available',
            'value'       => 'Available',
            'name'        => 'Available Product',
            'description' => 'Products ready for sale',
            'sort_order'  => 5,
            'is_active'   => true,
            'is_default'  => false,
            'metadata'    => ['channel' => 'admin'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('enum_values', [
        'type'              => 'product_status',
        'key'               => 'available',
        'name'              => 'Available Product',
        'metadata->channel' => 'admin',
    ]);
});

it('can edit an enum value through enum management resource', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(Pages\EditEnumManagement::class, ['record' => $this->seedEnum->getRouteKey()])
        ->fillForm([
            'name'        => 'Pending Order Updated',
            'description' => 'Updated description',
            'metadata'    => ['category' => 'orders', 'updated' => true],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->seedEnum->refresh();

    expect($this->seedEnum->name)->toBe('Pending Order Updated');
    expect($this->seedEnum->description)->toBe('Updated description');
    expect($this->seedEnum->metadata)->toMatchArray(['category' => 'orders', 'updated' => true]);
});

it('can view an enum value record', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(EnumManagementResource::getUrl('view', ['record' => $this->seedEnum]))
        ->assertOk()
        ->assertSee('Pending Order');
});

it('can toggle activation status from the table', function (): void {
    $inactiveEnum = EnumValue::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(Pages\ListEnumManagement::class)
        ->callTableAction('activate', $inactiveEnum)
        ->assertHasNoActionErrors();

    expect($inactiveEnum->fresh()->is_active)->toBeTrue();

    Livewire::actingAs($this->adminUser)
        ->test(Pages\ListEnumManagement::class)
        ->callTableAction('deactivate', $this->seedEnum)
        ->assertHasNoActionErrors();

    expect($this->seedEnum->fresh()->is_active)->toBeFalse();
});

it('can set an enum value as default', function (): void {
    $enumOne = EnumValue::factory()->create([
        'type'       => 'shipping_status',
        'is_default' => true,
    ]);
    $enumTwo = EnumValue::factory()->create([
        'type'       => 'shipping_status',
        'is_default' => false,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(Pages\ListEnumManagement::class)
        ->callTableAction('set_default', $enumTwo)
        ->assertHasNoActionErrors();

    expect($enumOne->fresh()->is_default)->toBeFalse();
    expect($enumTwo->fresh()->is_default)->toBeTrue();
});

it('can bulk activate and deactivate enum values', function (): void {
    $inactiveEnums = EnumValue::factory()->count(2)->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(Pages\ListEnumManagement::class)
        ->callTableBulkAction('activate_bulk', $inactiveEnums->modelKeys())
        ->assertHasNoBulkActionErrors();

    expect($inactiveEnums->fresh()->every->is_active)->toBeTrue();

    $activeEnums = EnumValue::factory()->count(2)->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(Pages\ListEnumManagement::class)
        ->callTableBulkAction('deactivate_bulk', $activeEnums->modelKeys())
        ->assertHasNoBulkActionErrors();

    expect($activeEnums->fresh()->every->is_active)->toBeFalse();
});
