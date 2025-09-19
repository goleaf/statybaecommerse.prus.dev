<?php declare(strict_types=1);

use App\Filament\Resources\CustomerGroupResource\Pages\ListCustomerGroups;
use App\Filament\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create(['is_admin' => true]);
});

it('can list customer groups in admin panel', function () {
    $customerGroup = CustomerGroup::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->assertCanSeeTableRecords([$customerGroup]);
});

it('can create a new customer group with basic fields', function () {
    $customerGroupData = [
        'name' => 'VIP Customers',
        'code' => 'VIP',
        'description' => 'High-value customers',
        'discount_percentage' => 15,
        'is_active' => true,
        'is_default' => false,
    ];

    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->call('create')
        ->fillForm($customerGroupData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('customer_groups', [
        'name' => 'VIP Customers',
        'code' => 'VIP',
        'description' => 'High-value customers',
        'discount_percentage' => 15,
        'is_active' => true,
        'is_default' => false,
    ]);
});
