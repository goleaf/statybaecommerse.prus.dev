<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prepare a predictable application locale for JSON name assertions.
     */
    protected function setUp(): void
    {
        // Call the parent setup to bootstrap the database transactions.
        parent::setUp();

        // Force the locale to English because our scope relies on translated JSON columns.
        app()->setLocale('en');
    }

    public function test_it_exposes_expected_fillable_attributes(): void
    {
        // Instantiate a fresh model so we can check the mass-assignable whitelist.
        $group = new CustomerGroup;

        // Verify a representative subset of attributes is guarded correctly.
        $this->assertContains('name', $group->getFillable());
        $this->assertContains('slug', $group->getFillable());
        $this->assertContains('is_enabled', $group->getFillable());
        $this->assertContains('conditions', $group->getFillable());
    }

    public function test_it_exposes_expected_casts(): void
    {
        // Build the casts array and inspect the keys we rely on in business logic.
        $casts = (new CustomerGroup)->getCasts();

        // Confirm that numeric and boolean flags are converted into their proper types.
        $this->assertSame('decimal:2', $casts['discount_fixed']);
        $this->assertSame('boolean', $casts['is_enabled']);
        $this->assertSame('array', $casts['metadata']);
    }

    public function test_it_generates_slug_from_code_when_missing(): void
    {
        // Create a customer group without a slug so the model hook has to generate one.
        $group = CustomerGroup::factory()->create([
            'code' => 'VIP-001',
            'slug' => null,
            'name' => [
                'en' => 'Very Important',
                'lt' => 'Labai Svarbi',
            ],
        ]);

        // The slug should mirror the deterministic value derived from the provided code.
        $this->assertSame('vip-001', $group->slug);
    }

    public function test_discount_percentage_accessor_rounds_values(): void
    {
        // Store a noisy float value to ensure the accessor normalises precision.
        $group = CustomerGroup::factory()->create([
            'discount_percentage' => 12.3456,
        ]);

        // Accessing the attribute should produce a rounded float that downstream math can trust.
        $this->assertSame(12.35, $group->discount_percentage);
    }

    public function test_scope_enabled_filters_records(): void
    {
        // Seed both an enabled and a disabled group for comparison.
        $enabled = CustomerGroup::factory()->create(['is_enabled' => true]);
        CustomerGroup::factory()->create(['is_enabled' => false]);

        // The scope should only surface the enabled record.
        $ids = CustomerGroup::query()->enabled()->pluck('id')->all();

        $this->assertSame([$enabled->id], $ids);
    }

    public function test_scope_ordered_by_name_uses_locale_path(): void
    {
        // Create groups with deliberately unordered translated names.
        $alpha = CustomerGroup::factory()->create([
            'name' => ['en' => 'Alpha', 'lt' => 'Alfa'],
        ]);
        $charlie = CustomerGroup::factory()->create([
            'name' => ['en' => 'Charlie', 'lt' => 'Carolis'],
        ]);
        $bravo = CustomerGroup::factory()->create([
            'name' => ['en' => 'Bravo', 'lt' => 'Bravas'],
        ]);

        // Fetch ordered results and map the translated names for assertion clarity.
        $orderedNames = CustomerGroup::query()
            ->withoutGlobalScopes()
            ->orderedByName('en')
            ->get()
            ->map(fn (CustomerGroup $group) => $group->getTranslation('name', 'en'))
            ->all();

        $this->assertSame([
            'Alpha',
            'Bravo',
            'Charlie',
        ], $orderedNames);
    }

    public function test_has_any_discount_detects_percentage_or_fixed_values(): void
    {
        // Combine both percentage and fixed discounts to ensure the helper evaluates truthy states correctly.
        $group = CustomerGroup::factory()->create([
            'discount_percentage' => 0,
            'discount_fixed'      => 5,
        ]);

        // The helper should consider the fixed discount sufficient for a positive response.
        $this->assertTrue($group->hasAnyDiscount());
    }

    public function test_users_relationship_counts_attached_models(): void
    {
        // Establish a group and a couple of related users that will be attached via the pivot table.
        $group = CustomerGroup::factory()->create();
        $users = User::factory()->count(2)->create();

        // Link the users to the group to exercise the relationship definition.
        $group->users()->attach($users->pluck('id'));

        // Refresh the relation to assert the correct count and type.
        $group->load('users');

        $this->assertCount(2, $group->users);
        $this->assertInstanceOf(User::class, $group->users->first());
    }
}
