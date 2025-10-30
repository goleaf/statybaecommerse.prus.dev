<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_is_exposed(): void
    {
        // Arrange: instantiate the model to inspect the fillable list.
        $model = new Address;

        // Assert: verify a curated subset of important attributes is whitelisted.
        $this->assertContains('first_name', $model->getFillable());
        $this->assertContains('last_name', $model->getFillable());
        $this->assertContains('address_line_1', $model->getFillable());
        $this->assertContains('postal_code', $model->getFillable());
    }

    public function test_casts_configuration_matches_expectations(): void
    {
        // Arrange: inspect the model for its cast definitions to guard regressions.
        $model = new Address;

        // Assert: ensure boolean and enum casts are preserved.
        foreach ([
            'is_default'  => 'boolean',
            'is_billing'  => 'boolean',
            'is_shipping' => 'boolean',
            'is_active'   => 'boolean',
            'type'        => 'string',
        ] as $attribute => $cast) {
            // Each expected cast should exist with the proper configuration value.
            $this->assertArrayHasKey($attribute, $model->getCasts());
            $this->assertSame($cast, $model->getCasts()[$attribute]);
        }
    }

    public function test_scope_ordered_by_name_applies_expected_ordering(): void
    {
        // Arrange: build address records with shuffled names to exercise the scope.
        Address::factory()->create(['first_name' => 'Zoe', 'last_name' => 'Adams']);
        Address::factory()->create(['first_name' => 'Amy', 'last_name' => 'Brown']);
        Address::factory()->create(['first_name' => 'Amy', 'last_name' => 'Adams']);

        // Act: gather the ordered combinations of names.
        $orderedNames = Address::query()
            ->orderedByName()
            ->get()
            ->map(fn (Address $address): string => $address->first_name . ' ' . $address->last_name);

        // Assert: confirm the records follow first-name then last-name sorting semantics.
        $this->assertInstanceOf(Collection::class, $orderedNames);
        $this->assertSame([
            'Amy Adams',
            'Amy Brown',
            'Zoe Adams',
        ], $orderedNames->all());
    }

    public function test_scope_by_type_filters_addresses(): void
    {
        // Arrange: create addresses for a single user across different types.
        $user = User::factory()->create();
        Address::factory()->create(['user_id' => $user->id, 'type' => AddressType::BILLING]);
        Address::factory()->create(['user_id' => $user->id, 'type' => AddressType::SHIPPING]);

        // Act: fetch addresses filtered by a specific type.
        $filtered = Address::query()->forUser($user->id)->byType(AddressType::BILLING->value)->get();

        // Assert: the resulting collection should contain only billing addresses.
        $this->assertCount(1, $filtered);
        $this->assertTrue($filtered->first()->type_enum === AddressType::BILLING);
    }
}
