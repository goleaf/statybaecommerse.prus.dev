<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Address;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_fillable_attributes_are_expected(): void
    {
        // Ensure the model accepts the intended mass-assignable attributes.
        $expected = [
            'name',
            'email',
            'phone',
            'address',
            'city_id',
            'country_id',
            'postal_code',
            'company_id',
            'is_active',
            'metadata',
        ];

        $customer = new Customer;

        // Validate that the fillable property matches the documented contract.
        $this->assertSame($expected, $customer->getFillable());
    }

    public function test_customer_casts_are_configured(): void
    {
        // Instantiate the model so we can read the attribute casting rules.
        $customer = new Customer;
        $casts = $customer->getCasts();

        // Confirm boolean and array casts to protect data integrity.
        $this->assertSame('boolean', $casts['is_active']);
        $this->assertSame('array', $casts['metadata']);
    }

    public function test_customer_uses_expected_traits(): void
    {
        // Capture the traits currently applied to the model instance.
        $traits = class_uses(new Customer);

        // Verify both translation and soft delete behaviours remain active.
        $this->assertContains('App\\Traits\\HasTranslations', $traits);
        $this->assertContains('Illuminate\\Database\\Eloquent\\SoftDeletes', $traits);
    }

    public function test_city_relationship_definition(): void
    {
        // Resolve the relationship instance to ensure it is configured correctly.
        $relationship = (new Customer)->city();

        // Assert that the relationship matches the expected type and target model.
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertSame(City::class, $relationship->getRelated()::class);
    }

    public function test_country_relationship_definition(): void
    {
        // Resolve the relationship instance to ensure it is configured correctly.
        $relationship = (new Customer)->country();

        // Assert that the relationship matches the expected type and target model.
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertSame(Country::class, $relationship->getRelated()::class);
    }

    public function test_company_relationship_definition(): void
    {
        // Resolve the relationship instance to ensure it is configured correctly.
        $relationship = (new Customer)->company();

        // Assert that the relationship matches the expected type and target model.
        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertSame(Company::class, $relationship->getRelated()::class);
    }

    public function test_orders_relationship_definition(): void
    {
        // Resolve the relationship instance to ensure it is configured correctly.
        $relationship = (new Customer)->orders();

        // Assert that the relationship matches the expected type and target model.
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertSame(Order::class, $relationship->getRelated()::class);
    }

    public function test_addresses_relationship_definition(): void
    {
        // Resolve the relationship instance to ensure it is configured correctly.
        $relationship = (new Customer)->addresses();

        // Assert that the relationship matches the expected type and target model.
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertSame(Address::class, $relationship->getRelated()::class);
    }

    public function test_reviews_relationship_definition(): void
    {
        // Resolve the relationship instance to ensure it is configured correctly.
        $relationship = (new Customer)->reviews();

        // Assert that the relationship matches the expected type and target model.
        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertSame(Review::class, $relationship->getRelated()::class);
    }

    public function test_scope_active_filters_inactive_records(): void
    {
        // Create customers in both active and inactive states for comparison.
        $activeCustomer = Customer::factory()->create();
        $inactiveCustomer = Customer::factory()->inactive()->create();

        // Execute the scope to confirm that only active customers are retrieved.
        $ids = Customer::query()->active()->pluck('id')->all();

        $this->assertContains($activeCustomer->id, $ids);
        $this->assertNotContains($inactiveCustomer->id, $ids);
    }

    public function test_scope_by_city_limits_results(): void
    {
        // Prepare distinct cities to verify the filtering behaviour.
        $targetCity = City::factory()->create();
        $otherCity = City::factory()->create();

        // Attach customers to each city to exercise the scope.
        $matchingCustomer = Customer::factory()->for($targetCity, 'city')->create();
        Customer::factory()->for($otherCity, 'city')->create();

        // Ensure the scope only returns the customer bound to the requested city.
        $ids = Customer::query()->byCity($targetCity->id)->pluck('id')->all();

        $this->assertSame([$matchingCustomer->id], $ids);
    }

    public function test_scope_by_country_limits_results(): void
    {
        // Prepare distinct countries to verify the filtering behaviour.
        $targetCountry = Country::factory()->create();
        $otherCountry = Country::factory()->create();

        // Attach customers to each country to exercise the scope.
        $matchingCustomer = Customer::factory()->for($targetCountry, 'country')->create();
        Customer::factory()->for($otherCountry, 'country')->create();

        // Ensure the scope only returns the customer bound to the requested country.
        $ids = Customer::query()->byCountry($targetCountry->id)->pluck('id')->all();

        $this->assertSame([$matchingCustomer->id], $ids);
    }

    public function test_scope_by_company_limits_results(): void
    {
        // Prepare distinct companies to verify the filtering behaviour.
        $targetCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        // Attach customers to each company to exercise the scope.
        $matchingCustomer = Customer::factory()->for($targetCompany, 'company')->create();
        Customer::factory()->for($otherCompany, 'company')->create();

        // Ensure the scope only returns the customer bound to the requested company.
        $ids = Customer::query()->byCompany($targetCompany->id)->pluck('id')->all();

        $this->assertSame([$matchingCustomer->id], $ids);
    }

    public function test_scope_ordered_by_name_sorts_results(): void
    {
        // Create a deterministic data set to verify alphabetical ordering.
        $second = Customer::factory()->create(['name' => 'Brigita']);
        $first = Customer::factory()->create(['name' => 'Austeja']);
        $third = Customer::factory()->create(['name' => 'Ceslovas']);

        // Assert ascending ordering aligns with the alphabetical expectation.
        $ascending = Customer::query()->orderedByName()->pluck('name')->all();
        $this->assertSame([
            $first->name,
            $second->name,
            $third->name,
        ], $ascending);

        // Assert descending ordering is also supported for caller flexibility.
        $descending = Customer::query()->orderedByName('desc')->pluck('name')->all();
        $this->assertSame([
            $third->name,
            $second->name,
            $first->name,
        ], $descending);
    }
}
