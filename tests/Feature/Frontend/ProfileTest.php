<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use App\Support\Database\TableAvailability;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\TestCase;

final class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('frontend.profile.index'));

        $response
            ->assertOk()
            ->assertViewIs('profile.index')
            ->assertViewHas('user', static fn (User $value): bool => $value->is($user))
            ->assertViewHas('addresses');
    }

    public function test_edit_page_handles_missing_countries_table(): void
    {
        $user = User::factory()->create();

        $this->withTableAvailability(['countries' => false], function () use ($user): void {
            $response = $this->actingAs($user)->get(route('frontend.profile.edit'));

            $response
                ->assertOk()
                ->assertViewIs('profile.edit')
                ->assertViewHas('countries', static fn ($countries): bool => $countries instanceof Collection && $countries->isEmpty());
        });
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'email'      => 'jane@example.com',
        ]);

        if (Schema::hasTable('customers')) {
            Customer::query()->create([
                'name'        => 'Jane Doe',
                'email'       => $user->email,
                'phone'       => '+37060000000',
                'address'     => 'Old street 1',
                'postal_code' => '12345',
                'is_active'   => true,
            ]);
        }

        $response = $this->actingAs($user)->put(route('frontend.profile.update'), [
            'first_name'  => 'Alice',
            'last_name'   => 'Smith',
            'email'       => 'alice@example.com',
            'phone'       => '+37060000001',
            'address'     => 'New Street 10',
            'postal_code' => '54321',
        ]);

        $response->assertRedirect(route('frontend.profile.index'));

        $user->refresh();

        self::assertSame('Alice', $user->first_name);
        self::assertSame('Smith', $user->last_name);
        self::assertSame('Alice Smith', $user->name);
        self::assertSame('alice@example.com', $user->email);
        self::assertSame('+37060000001', $user->phone);
        self::assertSame('+37060000001', $user->phone_number);

        if (Schema::hasTable('customers')) {
            $this->assertDatabaseHas('customers', [
                'email'       => 'alice@example.com',
                'name'        => 'Alice Smith',
                'postal_code' => '54321',
            ]);
        }
    }

    public function test_authenticated_user_can_view_addresses_page(): void
    {
        $user = User::factory()->create();
        Address::factory()->count(2)->for($user)->create([
            'type'         => AddressType::SHIPPING->value,
            'country_code' => 'LT',
        ]);

        $response = $this->actingAs($user)->get(route('frontend.profile.addresses'));

        $response
            ->assertOk()
            ->assertViewIs('profile.addresses')
            ->assertViewHas('addresses', static function ($addresses) use ($user): bool {
                return $addresses->every(static fn (Address $address): bool => $address->user_id === $user->id);
            });
    }

    public function test_user_can_create_new_address(): void
    {
        $user = User::factory()->create();
        $existing = Address::factory()->for($user)->create([
            'is_default'   => true,
            'type'         => AddressType::BILLING->value,
            'country_code' => 'LT',
        ]);

        $payload = [
            'type'           => AddressType::SHIPPING->value,
            'first_name'     => 'Jonas',
            'last_name'      => 'Jonaitis',
            'address_line_1' => 'Gedimino pr. 1',
            'address_line_2' => '',
            'city'           => 'Vilnius',
            'postal_code'    => '01103',
            'country_code'   => 'LT',
            'phone'          => '+37060000002',
            'email'          => 'jonas@example.com',
            'is_default'     => true,
            'is_shipping'    => true,
        ];

        $response = $this->actingAs($user)->post(route('frontend.profile.store-address'), $payload);

        $response->assertRedirect(route('frontend.profile.addresses'));

        $this->assertDatabaseHas('addresses', [
            'user_id'        => $user->id,
            'address_line_1' => 'Gedimino pr. 1',
            'is_default'     => true,
            'is_shipping'    => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id'         => $existing->id,
            'is_default' => false,
        ]);
    }

    public function test_user_can_create_address_when_lookup_tables_are_missing(): void
    {
        $user = User::factory()->create();

        $this->withTableAvailability(['countries' => false, 'cities' => false], function () use ($user): void {
            $payload = [
                'type'           => AddressType::SHIPPING->value,
                'first_name'     => 'Jonas',
                'last_name'      => 'Jonaitis',
                'address_line_1' => 'Gedimino pr. 1',
                'city'           => 'Vilnius',
                'postal_code'    => '01103',
                'country_code'   => 'LT',
            ];

            $response = $this->actingAs($user)->post(route('frontend.profile.store-address'), $payload);

            $response->assertRedirect(route('frontend.profile.addresses'));

            $this->assertDatabaseHas('addresses', [
                'user_id'        => $user->id,
                'address_line_1' => 'Gedimino pr. 1',
                'city'           => 'Vilnius',
                'postal_code'    => '01103',
            ]);
        });
    }

    public function test_user_can_update_existing_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create([
            'type'         => AddressType::SHIPPING->value,
            'country_code' => 'LT',
            'city'         => 'Vilnius',
            'postal_code'  => '01103',
        ]);

        $payload = [
            'type'           => AddressType::BILLING->value,
            'first_name'     => 'Asta',
            'last_name'      => 'Petrauskienė',
            'address_line_1' => 'Naujoji g. 5',
            'address_line_2' => '2 aukštas',
            'city'           => 'Kaunas',
            'postal_code'    => '50290',
            'country_code'   => 'LT',
            'phone'          => '+37060000003',
            'email'          => 'asta@example.com',
            'is_billing'     => true,
        ];

        $response = $this->actingAs($user)->put(route('frontend.profile.update-address', $address), $payload);

        $response->assertRedirect(route('frontend.profile.addresses'));

        $this->assertDatabaseHas('addresses', [
            'id'          => $address->id,
            'type'        => AddressType::BILLING->value,
            'city'        => 'Kaunas',
            'postal_code' => '50290',
            'is_billing'  => true,
        ]);
    }

    public function test_user_can_delete_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create([
            'type'         => AddressType::OTHER->value,
            'country_code' => 'LT',
        ]);

        $response = $this->actingAs($user)->delete(route('frontend.profile.delete-address', $address));

        $response->assertRedirect(route('frontend.profile.addresses'));

        $this->assertSoftDeleted('addresses', [
            'id' => $address->id,
        ]);
    }

    /**
     * @param array<string, bool> $overrides
     */
    private function withTableAvailability(array $overrides, Closure $callback): void
    {
        $original = app(TableAvailability::class);

        $fake = new class($overrides) extends TableAvailability
        {
            /**
             * @param array<string, bool> $overrides
             */
            public function __construct(private readonly array $overrides) {}

            public function has(string $table, ?string $connection = null): bool
            {
                if (array_key_exists($table, $this->overrides)) {
                    return $this->overrides[$table];
                }

                return parent::has($table, $connection);
            }
        };

        $this->app->instance(TableAvailability::class, $fake);

        try {
            $callback();
        } finally {
            $this->app->instance(TableAvailability::class, $original);
        }
    }
}
