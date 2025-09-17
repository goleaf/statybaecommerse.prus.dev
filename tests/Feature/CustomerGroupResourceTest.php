<?php declare(strict_types=1);

use App\Models\CustomerGroup;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\CustomerGroupResource;
use App\Filament\Resources\CustomerGroupResource\Pages\ListCustomerGroups;
use App\Filament\Resources\CustomerGroupResource\Pages\CreateCustomerGroup;
use App\Filament\Resources\CustomerGroupResource\Pages\ViewCustomerGroup;
use App\Filament\Resources\CustomerGroupResource\Pages\EditCustomerGroup;

beforeEach(function () {
    $this->adminUser = User::factory()->create(['is_admin' => true]);
});

it('can list customer groups in admin panel', function () {
    $customerGroup = CustomerGroup::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->assertCanSeeTableRecords([$customerGroup]);
});

it('can create a new customer group', function () {
    $customerGroupData = [
        'name' => 'VIP Customers',
        'code' => 'VIP',
        'description' => 'High-value customers',
        'color' => '#ff0000',
        'icon' => 'star',
        'discount_percentage' => 15,
        'minimum_order_amount' => 1000,
        'credit_limit' => 5000,
        'payment_terms' => 'net_30',
        'is_active' => true,
        'is_default' => false,
    ];
    
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm($customerGroupData)
        ->call('create')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('customer_groups', [
        'name' => 'VIP Customers',
        'code' => 'VIP',
        'description' => 'High-value customers',
        'color' => '#ff0000',
        'icon' => 'star',
        'discount_percentage' => 15,
        'minimum_order_amount' => 1000,
        'credit_limit' => 5000,
        'payment_terms' => 'net_30',
        'is_active' => true,
        'is_default' => false,
    ]);
});

it('can view a customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ViewCustomerGroup::class, ['record' => $customerGroup->id])
        ->assertOk();
});

it('can edit a customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditCustomerGroup::class, ['record' => $customerGroup->id])
        ->fillForm([
            'name' => 'Updated Group',
            'discount_percentage' => 20,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('customer_groups', [
        'id' => $customerGroup->id,
        'name' => 'Updated Group',
        'discount_percentage' => 20,
    ]);
});

it('can delete a customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->callTableAction('delete', $customerGroup)
        ->assertHasNoTableActionErrors();
    
    $this->assertSoftDeleted('customer_groups', [
        'id' => $customerGroup->id,
    ]);
});

it('validates required fields when creating customer group', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => null,
            'code' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'code']);
});

it('validates unique customer group code', function () {
    $existingGroup = CustomerGroup::factory()->create(['code' => 'UNIQUE']);
    
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => 'Another Group',
            'code' => 'UNIQUE',
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('validates payment terms options', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => 'Test Group',
            'code' => 'TEST',
            'payment_terms' => 'invalid_terms',
        ])
        ->call('create')
        ->assertHasFormErrors(['payment_terms']);
});

it('validates numeric fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => 'Test Group',
            'code' => 'TEST',
            'discount_percentage' => 'not_a_number',
            'minimum_order_amount' => 'invalid',
            'credit_limit' => 'invalid',
        ])
        ->call('create')
        ->assertHasFormErrors(['discount_percentage', 'minimum_order_amount', 'credit_limit']);
});

it('validates discount percentage range', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => 'Test Group',
            'code' => 'TEST',
            'discount_percentage' => 150, // Over 100%
        ])
        ->call('create')
        ->assertHasFormErrors(['discount_percentage']);
});

it('can filter customer groups by active status', function () {
    $activeGroup = CustomerGroup::factory()->create(['is_active' => true]);
    $inactiveGroup = CustomerGroup::factory()->create(['is_active' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$activeGroup])
        ->assertCanNotSeeTableRecords([$inactiveGroup]);
});

it('can filter customer groups by default status', function () {
    $defaultGroup = CustomerGroup::factory()->create(['is_default' => true]);
    $nonDefaultGroup = CustomerGroup::factory()->create(['is_default' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->filterTable('is_default', true)
        ->assertCanSeeTableRecords([$defaultGroup])
        ->assertCanNotSeeTableRecords([$nonDefaultGroup]);
});

it('shows correct customer group data in table', function () {
    $customerGroup = CustomerGroup::factory()->create([
        'name' => 'VIP Customers',
        'code' => 'VIP',
        'discount_percentage' => 15,
        'minimum_order_amount' => 1000,
        'credit_limit' => 5000,
        'is_active' => true,
        'is_default' => false,
    ]);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->assertCanSeeTableRecords([$customerGroup])
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('code')
        ->assertCanRenderTableColumn('discount_percentage')
        ->assertCanRenderTableColumn('minimum_order_amount')
        ->assertCanRenderTableColumn('credit_limit')
        ->assertCanRenderTableColumn('users_count')
        ->assertCanRenderTableColumn('is_active')
        ->assertCanRenderTableColumn('is_default');
});

it('handles customer group activation and deactivation', function () {
    $customerGroup = CustomerGroup::factory()->create(['is_active' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(EditCustomerGroup::class, ['record' => $customerGroup->id])
        ->fillForm(['is_active' => true])
        ->call('save')
        ->assertHasNoFormErrors();
        
    expect($customerGroup->fresh()->is_active)->toBeTrue();
});

it('can set default customer group', function () {
    $customerGroup = CustomerGroup::factory()->create(['is_default' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(EditCustomerGroup::class, ['record' => $customerGroup->id])
        ->fillForm(['is_default' => true])
        ->call('save')
        ->assertHasNoFormErrors();
        
    expect($customerGroup->fresh()->is_default)->toBeTrue();
});

it('can search customer groups by name', function () {
    $group1 = CustomerGroup::factory()->create(['name' => 'VIP Customers']);
    $group2 = CustomerGroup::factory()->create(['name' => 'Regular Customers']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->searchTable('VIP')
        ->assertCanSeeTableRecords([$group1])
        ->assertCanNotSeeTableRecords([$group2]);
});

it('can search customer groups by code', function () {
    $group1 = CustomerGroup::factory()->create(['code' => 'VIP']);
    $group2 = CustomerGroup::factory()->create(['code' => 'REG']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->searchTable('VIP')
        ->assertCanSeeTableRecords([$group1])
        ->assertCanNotSeeTableRecords([$group2]);
});

it('handles bulk actions on customer groups', function () {
    $group1 = CustomerGroup::factory()->create();
    $group2 = CustomerGroup::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->callTableBulkAction('delete', [$group1->id, $group2->id])
        ->assertOk();
    
    $this->assertSoftDeleted('customer_groups', [
        'id' => $group1->id,
    ]);
    
    $this->assertSoftDeleted('customer_groups', [
        'id' => $group2->id,
    ]);
});

it('can create customer group with minimal required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => 'Basic Group',
            'code' => 'BASIC',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('customer_groups', [
        'name' => 'Basic Group',
        'code' => 'BASIC',
    ]);
});

it('can set color and icon for customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditCustomerGroup::class, ['record' => $customerGroup->id])
        ->fillForm([
            'color' => '#00ff00',
            'icon' => 'crown',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('customer_groups', [
        'id' => $customerGroup->id,
        'color' => '#00ff00',
        'icon' => 'crown',
    ]);
});

it('can set description for customer group', function () {
    $customerGroup = CustomerGroup::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditCustomerGroup::class, ['record' => $customerGroup->id])
        ->fillForm(['description' => 'This is a test description'])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('customer_groups', [
        'id' => $customerGroup->id,
        'description' => 'This is a test description',
    ]);
});

it('validates minimum order amount is not negative', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => 'Test Group',
            'code' => 'TEST',
            'minimum_order_amount' => -100,
        ])
        ->call('create')
        ->assertHasFormErrors(['minimum_order_amount']);
});

it('validates credit limit is not negative', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateCustomerGroup::class)
        ->fillForm([
            'name' => 'Test Group',
            'code' => 'TEST',
            'credit_limit' => -1000,
        ])
        ->call('create')
        ->assertHasFormErrors(['credit_limit']);
});


