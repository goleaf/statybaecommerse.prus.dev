<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('frontend.profile.index'));

        $response->assertOk();
        $response->assertViewIs('profile.index');
        $response->assertSee($user->name);
    }

    public function test_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('frontend.profile.update'), [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('frontend.profile.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'email' => 'updated@example.com']);
    }

    public function test_update_profile_requires_unique_email(): void
    {
        $user = User::factory()->create(['email' => 'first@example.com']);
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($user)->put(route('frontend.profile.update'), [
            'name'  => 'Updated Name',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_address_creates_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('frontend.profile.store-address'), [
            'type'           => AddressType::SHIPPING->value,
            'first_name'     => 'Jonas',
            'last_name'      => 'Jonaitis',
            'address_line_1' => 'Example g. 1',
            'city'           => 'Vilnius',
            'postal_code'    => '01100',
            'country_code'   => 'LT',
            'is_default'     => true,
        ]);

        $response->assertRedirect(route('frontend.profile.addresses'));
        $this->assertDatabaseHas('addresses', [
            'user_id'    => $user->id,
            'first_name' => 'Jonas',
            'is_default' => true,
        ]);
    }

    public function test_update_address_modifies_existing_record(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id'        => $user->id,
            'type'           => AddressType::SHIPPING,
            'first_name'     => 'Old',
            'last_name'      => 'Name',
            'address_line_1' => 'Street 1',
            'city'           => 'Vilnius',
            'postal_code'    => '01100',
            'country_code'   => 'LT',
        ]);

        $response = $this->actingAs($user)->put(route('frontend.profile.update-address', $address), [
            'type'           => AddressType::BILLING->value,
            'first_name'     => 'New',
            'last_name'      => 'Name',
            'address_line_1' => 'Street 2',
            'city'           => 'Kaunas',
            'postal_code'    => '22222',
            'country_code'   => 'LT',
            'is_default'     => true,
        ]);

        $response->assertRedirect(route('frontend.profile.addresses'));
        $this->assertDatabaseHas('addresses', [
            'id'         => $address->id,
            'first_name' => 'New',
            'city'       => 'Kaunas',
            'is_default' => true,
        ]);
    }

    public function test_delete_address_removes_record(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id'        => $user->id,
            'type'           => AddressType::HOME,
            'first_name'     => 'Delete',
            'last_name'      => 'Me',
            'address_line_1' => 'Street 1',
            'city'           => 'Vilnius',
            'postal_code'    => '01100',
            'country_code'   => 'LT',
        ]);

        $response = $this->actingAs($user)->delete(route('frontend.profile.delete-address', $address));

        $response->assertRedirect(route('frontend.profile.addresses'));
        $this->assertSoftDeleted('addresses', ['id' => $address->id]);
    }
}
