<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\CustomerGroup;
use App\Models\User;
use Database\Seeders\BulkCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates users using factory with proper attributes', function () {
    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify users were created
    expect(User::count())->toBeGreaterThan(0);

    // Verify user structure
    $user = User::first();
    expect($user->name)->toStartWith('Customer');
    expect($user->email)->toContain('@example.com');
    expect($user->preferred_locale)->toBeIn(['lt', 'en']);
    expect($user->is_admin)->toBeFalse();
    expect($user->email_verified_at)->not->toBeNull();
});

it('creates addresses using factory relationships', function () {
    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify addresses were created
    expect(Address::count())->toBeGreaterThan(0);

    // Verify address relationships
    $address = Address::with('user')->first();
    expect($address->user)->not->toBeNull();
    expect($address->type)->toBeIn(['shipping', 'billing']);
    expect($address->first_name)->not->toBeNull();
    expect($address->last_name)->not->toBeNull();
    expect($address->city)->not->toBeNull();
});

it('creates both shipping and billing addresses for each user', function () {
    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify each user has both types of addresses
    $users = User::with('addresses')->get();

    foreach ($users->take(5) as $user) {  // Check first 5 users
        $addressTypes = $user->addresses->pluck('type')->toArray();
        expect($addressTypes)->toContain('shipping');
        expect($addressTypes)->toContain('billing');
    }
});

it('assigns users to customer groups using relationships', function () {
    // Create a customer group first
    CustomerGroup::factory()->create();

    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify users were assigned to customer group
    $customerGroup = CustomerGroup::with('users')->first();
    expect($customerGroup->users)->not->toBeEmpty();

    // Verify pivot table has proper timestamps
    $pivotData = $customerGroup->users()->first()->pivot;
    expect($pivotData->assigned_at)->not->toBeNull();
    expect($pivotData->created_at)->not->toBeNull();
    expect($pivotData->updated_at)->not->toBeNull();
});

it('handles chunked processing correctly', function () {
    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify users were created in expected quantities
    $userCount = User::count();
    expect($userCount)->toBeGreaterThan(0);

    // Verify email format consistency
    $users = User::all();
    foreach ($users->take(10) as $user) {
        expect($user->email)->toMatch('/^customer\d{5}@example\.com$/');
    }
});

it('creates proper locale distribution', function () {
    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify both locales are represented
    $ltUsers = User::where('preferred_locale', 'lt')->count();
    $enUsers = User::where('preferred_locale', 'en')->count();

    expect($ltUsers)->toBeGreaterThan(0);
    expect($enUsers)->toBeGreaterThan(0);
});

it('handles missing customer groups gracefully', function () {
    // Ensure no customer groups exist
    CustomerGroup::query()->delete();

    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify users were still created
    expect(User::count())->toBeGreaterThan(0);

    // Verify addresses were still created
    expect(Address::count())->toBeGreaterThan(0);
});

it('creates addresses with proper default settings', function () {
    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify shipping addresses are set as default
    $shippingAddresses = Address::where('type', 'shipping')->get();
    foreach ($shippingAddresses->take(5) as $address) {
        expect($address->is_default)->toBeTrue();
    }

    // Verify billing addresses are not set as default
    $billingAddresses = Address::where('type', 'billing')->get();
    foreach ($billingAddresses->take(5) as $address) {
        expect($address->is_default)->toBeFalse();
    }
});

it('maintains data integrity across relationships', function () {
    CustomerGroup::factory()->create();

    $seeder = new BulkCustomerSeeder;
    $seeder->run();

    // Verify all addresses belong to existing users
    $addressesWithoutUser = Address::whereNull('user_id')->count();
    expect($addressesWithoutUser)->toBe(0);

    // Verify all customer group assignments reference existing users
    $customerGroup = CustomerGroup::first();
    if ($customerGroup) {
        $invalidUserAssignments = $customerGroup
            ->users()
            ->whereNotExists(function ($query) {
                $query
                    ->select('id')
                    ->from('users')
                    ->whereColumn('users.id', 'customer_group_user.user_id');
            })
            ->count();

        expect($invalidUserAssignments)->toBe(0);
    }
});
