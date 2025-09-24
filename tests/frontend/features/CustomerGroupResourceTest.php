<?php

declare(strict_types=1);

use App\Filament\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('can render customer group resource index page', function () {
    actingAs($this->admin)
        ->get(CustomerGroupResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render customer group resource create page', function () {
    actingAs($this->admin)
        ->get(CustomerGroupResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create customer group', function () {
    $newData = [
        'name' => 'VIP Customers',
        'description' => 'High value customers with special privileges',
        'discount_percentage' => 15.00,
        'is_active' => true,
    ];

    actingAs($this->admin)
        ->from(CustomerGroupResource::getUrl('create'))
        ->post(CustomerGroupResource::getUrl('create'), array_merge($newData, ['slug' => 'vip-customers']))
        ->assertRedirect();

    assertDatabaseHas('customer_groups', array_merge(
        ['is_enabled' => $newData['is_active']],
        collect($newData)->except('is_active')->all(),
        ['slug' => 'vip-customers']
    ));
});

it('can render customer group resource view page', function () {
    $customerGroup = CustomerGroup::factory()->create();

    actingAs($this->admin)
        ->get(CustomerGroupResource::getUrl('view', ['record' => $customerGroup]))
        ->assertSuccessful();
});

it('can render customer group resource edit page', function () {
    $customerGroup = CustomerGroup::factory()->create();

    actingAs($this->admin)
        ->get(CustomerGroupResource::getUrl('edit', ['record' => $customerGroup]))
        ->assertSuccessful();
});

it('can update customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();
    $newData = [
        'name' => 'Updated Group',
        'description' => 'Updated description',
        'discount_percentage' => 20.00,
        'is_active' => false,
    ];

    actingAs($this->admin)
        ->from(CustomerGroupResource::getUrl('edit', ['record' => $customerGroup]))
        ->put(CustomerGroupResource::getUrl('edit', ['record' => $customerGroup]), $newData)
        ->assertRedirect();

    assertDatabaseHas('customer_groups', array_merge(
        ['id' => $customerGroup->id],
        ['is_enabled' => $newData['is_active']],
        collect($newData)->except('is_active')->all()
    ));
});

it('can delete customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();

    actingAs($this->admin)
        ->from(CustomerGroupResource::getUrl('edit', ['record' => $customerGroup]))
        ->delete(CustomerGroupResource::getUrl('edit', ['record' => $customerGroup]))
        ->assertRedirect();

    assertDatabaseMissing('customer_groups', ['id' => $customerGroup->id]);
});

it('can list customer groups', function () {
    $customerGroups = CustomerGroup::factory()->count(5)->create();

    actingAs($this->admin)
        ->get(CustomerGroupResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSeeText($customerGroups->first()->name);
});

it('can filter active customer groups', function () {
    $activeGroup = CustomerGroup::factory()->create(['is_active' => true]);
    $inactiveGroup = CustomerGroup::factory()->create(['is_active' => false]);

    actingAs($this->admin)
        ->get(CustomerGroupResource::getUrl('index').'?filter[active]=1')
        ->assertSuccessful()
        ->assertSeeText($activeGroup->name)
        ->assertDontSeeText($inactiveGroup->name);
});

it('validates required fields when creating customer group', function () {
    actingAs($this->admin)
        ->from(CustomerGroupResource::getUrl('create'))
        ->post(CustomerGroupResource::getUrl('create'), [])
        ->assertSessionHasErrors(['name']);
});

it('validates discount percentage is within valid range', function () {
    actingAs($this->admin)
        ->from(CustomerGroupResource::getUrl('create'))
        ->post(CustomerGroupResource::getUrl('create'), [
            'name' => 'Test Group',
            'discount_percentage' => 150,
        ])
        ->assertSessionHasErrors(['discount_percentage']);

    actingAs($this->admin)
        ->from(CustomerGroupResource::getUrl('create'))
        ->post(CustomerGroupResource::getUrl('create'), [
            'name' => 'Test Group',
            'discount_percentage' => -5,
        ])
        ->assertSessionHasErrors(['discount_percentage']);
});
