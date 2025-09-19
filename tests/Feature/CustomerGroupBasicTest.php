<?php declare(strict_types=1);

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a customer group with basic fields', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $customerGroup = CustomerGroup::create([
        'name' => 'VIP Customers',
        'code' => 'VIP',
        'description' => 'High-value customers',
        'discount_percentage' => 15,
        'is_active' => true,
        'is_default' => false,
        'type' => 'vip',
        'sort_order' => 1,
    ]);

    expect($customerGroup->name)->toBe('VIP Customers');
    expect($customerGroup->code)->toBe('VIP');
    expect($customerGroup->discount_percentage)->toBe(15.0);
    expect($customerGroup->is_active)->toBeTrue();
    expect($customerGroup->is_default)->toBeFalse();
});

it('can update customer group fields', function () {
    $customerGroup = CustomerGroup::create([
        'name' => 'Regular Customers',
        'code' => 'REG',
        'description' => 'Regular customers',
        'discount_percentage' => 5,
        'is_active' => true,
        'is_default' => false,
        'type' => 'regular',
        'sort_order' => 2,
    ]);

    $customerGroup->update([
        'discount_percentage' => 10,
        'is_active' => false,
    ]);

    expect($customerGroup->fresh()->discount_percentage)->toBe(10.0);
    expect($customerGroup->fresh()->is_active)->toBeFalse();
});
