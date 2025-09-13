<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use App\Models\City;
use App\Enums\AddressType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_addresses_index(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('frontend.addresses.index'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.addresses.index');
        $response->assertSee($address->display_name);
    }

    public function test_user_can_view_create_address_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('frontend.addresses.create'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.addresses.create');
        $response->assertSee(__('translations.add_new_address'));
    }

    public function test_user_can_create_address(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();

        $addressData = [
            'type' => AddressType::SHIPPING->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => 'Test Street 123',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'country_code' => 'LT',
            'country_id' => $country->id,
            'is_default' => true,
        ];

        $response = $this->actingAs($user)->post(route('frontend.addresses.store'), $addressData);

        $response->assertRedirect(route('frontend.addresses.index'));
        $response->assertSessionHas('success', __('translations.address_created_successfully'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'type' => AddressType::SHIPPING->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => 'Test Street 123',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'is_default' => true,
        ]);
    }

    public function test_user_can_view_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('frontend.addresses.show', $address));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.addresses.show');
        $response->assertSee($address->display_name);
    }

    public function test_user_can_view_edit_address_form(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('frontend.addresses.edit', $address));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.addresses.edit');
        $response->assertSee($address->first_name);
    }

    public function test_user_can_update_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'type' => AddressType::BILLING->value,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address_line_1' => 'Updated Street 456',
            'city' => 'Kaunas',
            'postal_code' => 'LT-56789',
            'country_code' => 'LT',
            'is_default' => false,
        ];

        $response = $this->actingAs($user)->put(route('frontend.addresses.update', $address), $updateData);

        $response->assertRedirect(route('frontend.addresses.index'));
        $response->assertSessionHas('success', __('translations.address_updated_successfully'));

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'type' => AddressType::BILLING->value,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address_line_1' => 'Updated Street 456',
            'city' => 'Kaunas',
            'postal_code' => 'LT-56789',
            'is_default' => false,
        ]);
    }

    public function test_user_can_delete_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('frontend.addresses.destroy', $address));

        $response->assertRedirect(route('frontend.addresses.index'));
        $response->assertSessionHas('success', __('translations.address_deleted_successfully'));

        $this->assertSoftDeleted('addresses', ['id' => $address->id]);
    }

    public function test_user_can_set_address_as_default(): void
    {
        $user = User::factory()->create();
        $defaultAddress = Address::factory()->create([
            'user_id' => $user->id,
            'is_default' => true,
        ]);
        $newDefaultAddress = Address::factory()->create([
            'user_id' => $user->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->post(route('frontend.addresses.set-default', $newDefaultAddress));

        $response->assertRedirect(route('frontend.addresses.index'));
        $response->assertSessionHas('success', __('translations.address_set_as_default'));

        $this->assertDatabaseHas('addresses', [
            'id' => $newDefaultAddress->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $defaultAddress->id,
            'is_default' => false,
        ]);
    }

    public function test_user_can_duplicate_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('frontend.addresses.duplicate', $address));

        $response->assertRedirect();
        $response->assertSessionHas('success', __('translations.address_duplicated'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'address_line_1' => $address->address_line_1,
            'is_default' => false, // Duplicated address should not be default
        ]);
    }

    public function test_user_cannot_access_other_users_addresses(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('frontend.addresses.show', $address));

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_other_users_addresses(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user2->id]);

        $updateData = [
            'type' => AddressType::BILLING->value,
            'first_name' => 'Hacker',
            'last_name' => 'User',
            'address_line_1' => 'Hacked Street',
            'city' => 'Hacked City',
            'postal_code' => 'LT-00000',
            'country_code' => 'LT',
        ];

        $response = $this->actingAs($user1)->put(route('frontend.addresses.update', $address), $updateData);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_other_users_addresses(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->delete(route('frontend.addresses.destroy', $address));

        $response->assertStatus(403);
    }

    public function test_address_creation_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('frontend.addresses.store'), []);

        $response->assertSessionHasErrors([
            'type',
            'first_name',
            'last_name',
            'address_line_1',
            'city',
            'postal_code',
            'country_code',
        ]);
    }

    public function test_address_update_validation(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('frontend.addresses.update', $address), []);

        $response->assertSessionHasErrors([
            'type',
            'first_name',
            'last_name',
            'address_line_1',
            'city',
            'postal_code',
            'country_code',
        ]);
    }

    public function test_guest_cannot_access_addresses(): void
    {
        $response = $this->get(route('frontend.addresses.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_create_addresses(): void
    {
        $response = $this->get(route('frontend.addresses.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_address_with_company_information(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();

        $addressData = [
            'type' => AddressType::SHIPPING->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_name' => 'Test Company Ltd',
            'company_vat' => 'LT123456789',
            'address_line_1' => 'Business Street 123',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'country_code' => 'LT',
            'country_id' => $country->id,
        ];

        $response = $this->actingAs($user)->post(route('frontend.addresses.store'), $addressData);

        $response->assertRedirect(route('frontend.addresses.index'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'company_name' => 'Test Company Ltd',
            'company_vat' => 'LT123456789',
        ]);
    }

    public function test_address_with_additional_information(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();

        $addressData = [
            'type' => AddressType::HOME->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => 'Home Street 123',
            'apartment' => 'Apt 5B',
            'floor' => '3rd Floor',
            'building' => 'Building A',
            'landmark' => 'Near the shopping center',
            'instructions' => 'Ring the doorbell twice',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'country_code' => 'LT',
            'country_id' => $country->id,
        ];

        $response = $this->actingAs($user)->post(route('frontend.addresses.store'), $addressData);

        $response->assertRedirect(route('frontend.addresses.index'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'apartment' => 'Apt 5B',
            'floor' => '3rd Floor',
            'building' => 'Building A',
            'landmark' => 'Near the shopping center',
            'instructions' => 'Ring the doorbell twice',
        ]);
    }

    public function test_address_with_contact_information(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();

        $addressData = [
            'type' => AddressType::WORK->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => 'Work Street 123',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'country_code' => 'LT',
            'country_id' => $country->id,
            'phone' => '+37012345678',
            'email' => 'john.doe@example.com',
        ];

        $response = $this->actingAs($user)->post(route('frontend.addresses.store'), $addressData);

        $response->assertRedirect(route('frontend.addresses.index'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'phone' => '+37012345678',
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_address_types_are_handled_correctly(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();

        foreach (AddressType::cases() as $type) {
            $addressData = [
                'type' => $type->value,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => 'Test Street 123',
                'city' => 'Vilnius',
                'postal_code' => 'LT-01234',
                'country_code' => 'LT',
                'country_id' => $country->id,
            ];

            $response = $this->actingAs($user)->post(route('frontend.addresses.store'), $addressData);

            $response->assertRedirect(route('frontend.addresses.index'));

            $this->assertDatabaseHas('addresses', [
                'user_id' => $user->id,
                'type' => $type->value,
            ]);
        }
    }

    public function test_address_billing_and_shipping_flags(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();

        $addressData = [
            'type' => AddressType::SHIPPING->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => 'Test Street 123',
            'city' => 'Vilnius',
            'postal_code' => 'LT-01234',
            'country_code' => 'LT',
            'country_id' => $country->id,
            'is_billing' => true,
            'is_shipping' => true,
        ];

        $response = $this->actingAs($user)->post(route('frontend.addresses.store'), $addressData);

        $response->assertRedirect(route('frontend.addresses.index'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'is_billing' => true,
            'is_shipping' => true,
        ]);
    }
}
