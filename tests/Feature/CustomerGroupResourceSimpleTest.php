<?php

declare(strict_types=1);

use App\Filament\Resources\CustomerGroupResource\Pages\ListCustomerGroups;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create(['is_admin' => true]);
});

it('feature: can list customer groups in admin panel', function () {
    $customerGroup = CustomerGroup::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->assertCanSeeTableRecords([$customerGroup]);
});

it('feature: can create a new customer group with basic fields', function () {
    $customerGroupData = [
        'name'                => 'VIP Customers',
        'code'                => 'VIP',
        'description'         => 'High-value customers',
        'discount_percentage' => 15,
        'is_active'           => true,
        'is_default'          => false,
    ];

    Livewire::actingAs($this->adminUser)
        ->test(ListCustomerGroups::class)
        ->call('create')
        ->fillForm($customerGroupData)
        ->call('create')
        ->assertHasNoFormErrors();

    $group = CustomerGroup::where('code', 'VIP')->first();
    expect($group)->not->toBeNull();
    expect($group->getTranslation('name', 'en'))->toBe('VIP Customers');
    expect($group->getTranslation('description', 'en'))->toBe('High-value customers');
    expect($group->discount_percentage)->toBe(15.0);
    expect($group->is_active)->toBeTrue();
    expect($group->is_default)->toBeFalse();
});
