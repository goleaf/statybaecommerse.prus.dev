<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\User;
use App\Http\Middleware\TestingLegalResourceStub;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

/**
 * AddressSecurityTest
 *
 * Feature tests asserting that address data remains user owned and validated
 * against the configured allow-lists.
 */
final class AddressSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prepare the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(TestingLegalResourceStub::class);
    }

    /**
     * Ensure a user cannot view another user's address record.
     */
    public function test_user_cannot_view_address_owned_by_another_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $address = Address::factory()->for($owner)->create([
            'type' => AddressType::SHIPPING->value,
            'country_code' => 'LT',
            'state' => 'Vilnius County',
            'postal_code' => '12345',
        ]);

        $response = $this->actingAs($intruder)
            ->get(route('frontend.addresses.show', $address));

        $response->assertForbidden();
    }

    /**
     * Ensure a user cannot update another user's address record.
     */
    public function test_user_cannot_update_address_owned_by_another_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $address = Address::factory()->for($owner)->create([
            'type' => AddressType::SHIPPING->value,
            'country_code' => 'LT',
            'state' => 'Vilnius County',
            'postal_code' => '12345',
        ]);

        $payload = [
            'type' => AddressType::BILLING->value,
            'first_name' => 'Safe',
            'last_name' => 'User',
            'company' => null,
            'company_name' => null,
            'company_vat' => null,
            'address_line_1' => 'Konstitucijos pr. 3',
            'address_line_2' => null,
            'apartment' => null,
            'floor' => null,
            'building' => null,
            'city' => 'Vilnius',
            'state' => 'Vilnius County',
            'postal_code' => 'LT-12345',
            'country_code' => 'LT',
            'phone' => '+37060000000',
            'email' => null,
            'notes' => null,
            'instructions' => null,
            'is_default' => true,
        ];

        $response = $this->actingAs($intruder)
            ->put(route('frontend.addresses.update', $address), $payload);

        $response->assertForbidden();
    }

    /**
     * Ensure an invalid country code triggers validation failure.
     */
    public function test_invalid_country_is_rejected(): void
    {
        $user = User::factory()->create();

        $payload = [
            'type' => AddressType::SHIPPING->value,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'company' => null,
            'company_name' => null,
            'company_vat' => null,
            'address_line_1' => 'Gedimino pr. 1',
            'address_line_2' => null,
            'apartment' => null,
            'floor' => null,
            'building' => null,
            'city' => 'Vilnius',
            'state' => 'Vilnius County',
            'postal_code' => 'LT-54321',
            'country_code' => 'XX',
            'phone' => '+37060000001',
            'email' => null,
            'notes' => null,
            'instructions' => null,
            'is_default' => true,
        ];

        $response = $this->actingAs($user)
            ->postJson(route('frontend.addresses.store'), $payload);

        $response->assertStatus(422);
        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('status', 422)
            ->where('error.context.violations.1.field', 'country_code')
            ->etc()
        );
    }
}
