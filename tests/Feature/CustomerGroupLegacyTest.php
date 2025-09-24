<?php

declare(strict_types=1);

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a customer group with legacy fields only', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $customerGroup = CustomerGroup::create([
        'name' => 'VIP Customers',
        'code' => 'VIP',
        'description' => 'High-value customers',
        'discount_percentage' => 15,
        'is_enabled' => true,
    ]);

    expect($customerGroup->name)->toBe('VIP Customers');
    expect($customerGroup->code)->toBe('VIP');
    expect($customerGroup->discount_percentage)->toBe('15.00');
    expect($customerGroup->is_enabled)->toBeTrue();
});

it('can update customer group legacy fields', function () {
    $customerGroup = CustomerGroup::create([
        'name' => 'Regular Customers',
        'code' => 'REG',
        'description' => 'Regular customers',
        'discount_percentage' => 5,
        'is_enabled' => true,
    ]);

    $customerGroup->update([
        'discount_percentage' => 10,
        'is_enabled' => false,
    ]);

    expect($customerGroup->fresh()->discount_percentage)->toBe('10.00');
    expect($customerGroup->fresh()->is_enabled)->toBeFalse();
});

it('can access customer group relationships', function () {
    $customerGroup = CustomerGroup::create([
        'name' => 'Test Group',
        'code' => 'TEST',
        'description' => 'Test customers',
        'discount_percentage' => 0,
        'is_enabled' => true,
    ]);

    // Test that relationships exist
    expect($customerGroup->users())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
    expect($customerGroup->customers())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
    expect($customerGroup->discounts())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
    expect($customerGroup->priceLists())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});

it('can use customer group scopes', function () {
    CustomerGroup::create([
        'name' => 'Enabled Group',
        'code' => 'ENABLED',
        'description' => 'Enabled group',
        'discount_percentage' => 10,
        'is_enabled' => true,
    ]);

    CustomerGroup::create([
        'name' => 'Disabled Group',
        'code' => 'DISABLED',
        'description' => 'Disabled group',
        'discount_percentage' => 0,
        'is_enabled' => false,
    ]);

    $enabledGroups = CustomerGroup::enabled()->get();
    expect($enabledGroups)->toHaveCount(1);
    expect($enabledGroups->first()->name)->toBe('Enabled Group');

    $groupsWithDiscount = CustomerGroup::withDiscount()->get();
    expect($groupsWithDiscount)->toHaveCount(1);
    expect($groupsWithDiscount->first()->name)->toBe('Enabled Group');
});
