<?php declare(strict_types=1);

use App\Filament\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing};

beforeEach(function () {
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
        ->post(CustomerGroupResource::getUrl('create'), $newData)
        ->assertRedirect();

    assertDatabaseHas('customer_groups', $newData);
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
        ->put(CustomerGroupResource::getUrl('edit', ['record' => $customerGroup]), $newData)
        ->assertRedirect();

    assertDatabaseHas('customer_groups', array_merge(['id' => $customerGroup->id], $newData));
});

it('can delete customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();

    actingAs($this->admin)
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
        ->get(CustomerGroupResource::getUrl('index') . '?filter[active]=1')
        ->assertSuccessful()
        ->assertSeeText($activeGroup->name)
        ->assertDontSeeText($inactiveGroup->name);
});

it('validates required fields when creating customer group', function () {
    actingAs($this->admin)
        ->post(CustomerGroupResource::getUrl('create'), [])
        ->assertSessionHasErrors(['name']);
});

it('validates discount percentage is within valid range', function () {
    actingAs($this->admin)
        ->post(CustomerGroupResource::getUrl('create'), [
            'name' => 'Test Group',
            'discount_percentage' => 150, // Invalid - over 100%
        ])
        ->assertSessionHasErrors(['discount_percentage']);

    actingAs($this->admin)
        ->post(CustomerGroupResource::getUrl('create'), [
            'name' => 'Test Group',
            'discount_percentage' => -5, // Invalid - negative
        ])
        ->assertSessionHasErrors(['discount_percentage']);
});
