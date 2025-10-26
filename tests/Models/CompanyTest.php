<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Company;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 */
final class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_is_explicit(): void
    {
        // Instantiate the model to interrogate fillable configuration safely.
        $company = new Company;

        // Confirm the mass-assignable attributes to guard against accidental data exposure.
        self::assertSame([
            'name',
            'email',
            'phone',
            'address',
            'website',
            'industry',
            'size',
            'description',
            'is_active',
            'metadata',
        ], $company->getFillable());
    }

    public function test_casts_are_defined_for_boolean_and_array_fields(): void
    {
        // Instantiate the model to review its casting behaviour in isolation.
        $company = new Company;

        // Ensure metadata is kept as an array and the activity flag as a boolean for type safety.
        self::assertSame([
            'id'        => 'int',
            'metadata'  => 'array',
            'is_active' => 'boolean',
        ], $company->getCasts());
    }

    public function test_subscribers_relationship_uses_company_name_link(): void
    {
        // Create a company with a deterministic name to link subscribers through the string key.
        $company = Company::factory()->create([
            'name' => 'Acme Industries',
        ]);

        // Attach subscribers that share the same company name and a decoy that should not match.
        $matchingSubscribers = Subscriber::factory()->count(2)->active()->withCompany('Acme Industries')->create();
        Subscriber::factory()->active()->withCompany('Different Name')->create();

        // Reload the relation to ensure fresh counts from the database.
        $loadedSubscribers = $company->fresh()->subscribers;

        // Validate that the relationship only returns the intended subscribers.
        self::assertCount($matchingSubscribers->count(), $loadedSubscribers);
        self::assertTrue($loadedSubscribers->every(fn (Subscriber $subscriber): bool => $subscriber->company === $company->name));
    }

    public function test_scope_active_only_returns_active_companies(): void
    {
        // Seed both active and inactive companies using factory helpers for clarity.
        Company::factory()->count(2)->active()->create();
        Company::factory()->count(3)->inactive()->create();

        // Apply the active scope to the query under test.
        $activeCompanies = Company::query()->active()->get();

        // Each returned company should be flagged as active and the count should match the seeded amount.
        self::assertCount(2, $activeCompanies);
        self::assertTrue($activeCompanies->every(fn (Company $company): bool => $company->is_active));
    }

    public function test_scope_by_industry_filters_results(): void
    {
        // Create companies across multiple industries to exercise the filtering behaviour.
        Company::factory()->count(2)->technology()->create();
        Company::factory()->count(2)->healthcare()->create();

        // Execute the industry-specific scope.
        $technologyCompanies = Company::query()->byIndustry('Technology')->get();

        // Assert only technology companies are retrieved.
        self::assertGreaterThan(0, $technologyCompanies->count());
        self::assertTrue($technologyCompanies->every(fn (Company $company): bool => $company->industry === 'Technology'));
    }

    public function test_scope_by_size_filters_results(): void
    {
        // Generate small, medium, and large companies for the size scope validation.
        Company::factory()->small()->create();
        Company::factory()->medium()->create();
        Company::factory()->large()->create();

        // Query only the small companies.
        $smallCompanies = Company::query()->bySize('small')->get();

        // Confirm the filter returns only records that match the requested size.
        self::assertCount(1, $smallCompanies);
        self::assertTrue($smallCompanies->every(fn (Company $company): bool => $company->size === 'small'));
    }

    public function test_scope_ordered_by_name_sorts_alphabetically(): void
    {
        // Create companies in an intentionally unsorted order.
        $third = Company::factory()->create(['name' => 'Zenith Holdings']);
        $first = Company::factory()->create(['name' => 'Alpha Labs']);
        $second = Company::factory()->create(['name' => 'Mosaic Ventures']);

        // Run the ordering scope to verify alphabetical sorting.
        $orderedNames = Company::query()->orderedByName()->pluck('name')->all();

        // Expect names to come back sorted A-Z regardless of insertion order.
        self::assertSame([
            $first->name,
            $second->name,
            $third->name,
        ], $orderedNames);
    }

    public function test_subscriber_count_accessor_counts_all_related_records(): void
    {
        // Create a company and multiple subscribers tied to its name.
        $company = Company::factory()->create(['name' => 'Nova Group']);
        Subscriber::factory()->count(3)->active()->withCompany('Nova Group')->create();

        // Refresh the model to ensure the accessor runs on fresh relationship data.
        $freshCompany = $company->fresh();

        // Verify the accessor reports the expected number of subscribers.
        self::assertSame(3, $freshCompany->subscriber_count);
    }

    public function test_active_subscriber_count_accessor_only_counts_active(): void
    {
        // Seed a company with a mix of active and inactive subscribers.
        $company = Company::factory()->create(['name' => 'Orion Limited']);
        Subscriber::factory()->count(2)->active()->withCompany('Orion Limited')->create();
        Subscriber::factory()->inactive()->withCompany('Orion Limited')->create();

        // Pull a fresh instance for the accessor evaluation.
        $freshCompany = $company->fresh();

        // Confirm only the active subscribers are counted by the accessor.
        self::assertSame(2, $freshCompany->active_subscriber_count);
    }
}
