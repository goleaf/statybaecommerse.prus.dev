<?php

declare(strict_types=1);

use App\Livewire\Pages\Account\Addresses;
use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Tests\Support\TestingDatabase::migrate();
    Tests\Support\TestingDatabase::ensureUserTestingColumns();
});

it('loads an existing address into edit mode', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->shipping()->active()->create([
        'first_name'     => 'John',
        'last_name'      => 'Doe',
        'address_line_1' => 'Old Street 1',
        'city'           => 'Vilnius',
        'postal_code'    => 'LT-12345',
        'country_code'   => 'LT',
        'is_default'     => true,
    ]);

    $this->actingAs($user);

    Livewire::test(Addresses::class)
        ->call('editAddress', $address->id)
        ->assertSet('editing_address_id', $address->id)
        ->assertSet('first_name', 'John')
        ->assertSet('address_line_1', 'Old Street 1')
        ->assertSet('country_code', 'LT');
});

it('updates an existing address when saving in edit mode', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->shipping()->active()->create([
        'first_name'     => 'John',
        'last_name'      => 'Doe',
        'address_line_1' => 'Old Street 1',
        'city'           => 'Vilnius',
        'postal_code'    => 'LT-12345',
        'country_code'   => 'LT',
        'is_default'     => false,
    ]);

    $this->actingAs($user);

    Livewire::test(Addresses::class)
        ->call('editAddress', $address->id)
        ->set('first_name', 'Jane')
        ->set('last_name', 'Smith')
        ->set('address_line_1', 'New Street 10')
        ->set('city', 'Kaunas')
        ->set('postal_code', 'LT-54321')
        ->set('country_code', 'LT')
        ->set('phone', '+37060000000')
        ->set('type', 'billing')
        ->set('set_as_default', true)
        ->call('saveAddress')
        ->assertSet('editing_address_id', null)
        ->assertSet('first_name', null);

    $address->refresh();

    expect($address->first_name)->toBe('Jane')
        ->and($address->last_name)->toBe('Smith')
        ->and($address->address_line_1)->toBe('New Street 10')
        ->and($address->city)->toBe('Kaunas')
        ->and($address->postal_code)->toBe('LT-54321')
        ->and($address->country_code)->toBe('LT')
        ->and($address->phone)->toBe('+37060000000')
        ->and($address->type)->toBe('billing')
        ->and($address->is_default)->toBeTrue();
});

it('does not allow editing another users address', function (): void {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $address = Address::factory()->for($owner)->shipping()->active()->create([
        'first_name'     => 'Owner',
        'address_line_1' => 'Private Street 1',
        'country_code'   => 'LT',
    ]);

    $this->actingAs($attacker);

    Livewire::test(Addresses::class)
        ->call('editAddress', $address->id)
        ->assertSet('editing_address_id', null);

    $address->refresh();
    expect($address->first_name)->toBe('Owner');
});
