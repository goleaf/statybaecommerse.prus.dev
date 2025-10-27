<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CustomerGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_group_can_be_created(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'name'       => ['lt' => 'VIP Klientai', 'en' => 'VIP Customers'],
            'code'       => 'VIP001',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('customer_groups', [
            'code'       => 'VIP001',
            'is_enabled' => true,
        ]);
        $this->assertInstanceOf(CustomerGroup::class, $customerGroup);
    }

    public function test_customer_group_casts_work_correctly(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'is_enabled'           => true,
            'is_active'            => true,
            'has_special_pricing'  => true,
            'discount_percentage'  => 15.5,
            'discount_fixed'       => 10.0,
            'sort_order'           => 5,
            'metadata'             => ['key' => 'value'],
            'conditions'           => ['min_order' => 100],
            'minimum_order_amount' => 250.75,
            'credit_limit'         => 5000.00,
            'payment_terms'        => 'Net 30',
        ]);

        $this->assertTrue($customerGroup->is_enabled);
        $this->assertTrue($customerGroup->is_active);
        $this->assertTrue($customerGroup->has_special_pricing);
        $this->assertIsFloat($customerGroup->discount_percentage);
        $this->assertSame(15.5, $customerGroup->discount_percentage);
        $this->assertIsString($customerGroup->discount_fixed);
        $this->assertSame('10.00', $customerGroup->discount_fixed);
        $this->assertSame(5, $customerGroup->sort_order);
        $this->assertIsArray($customerGroup->metadata);
        $this->assertIsArray($customerGroup->conditions);
        $this->assertSame('250.75', $customerGroup->minimum_order_amount);
        $this->assertSame('5000.00', $customerGroup->credit_limit);
        $this->assertSame('net_30', $customerGroup->payment_terms);
        $this->assertSame('Net 30', $customerGroup->getPaymentTerms());
    }

    public function test_customer_group_has_users_relationship(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $users = User::factory()->count(3)->create();

        $customerGroup->users()->attach($users->pluck('id'));

        $this->assertCount(3, $customerGroup->users);
        $this->assertInstanceOf(User::class, $customerGroup->users->first());
    }

    public function test_customer_group_has_customers_relationship(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $customers = User::factory()->count(2)->create();

        $customerGroup->customers()->attach($customers->pluck('id'));

        $this->assertCount(2, $customerGroup->customers);
        $this->assertInstanceOf(User::class, $customerGroup->customers->first());
    }

    public function test_customer_group_has_discounts_relationship(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $discounts = Discount::factory()->count(2)->create();

        $customerGroup->discounts()->attach($discounts->pluck('id'));

        $this->assertCount(2, $customerGroup->discounts);
        $this->assertInstanceOf(Discount::class, $customerGroup->discounts->first());
    }

    public function test_customer_group_has_price_lists_relationship(): void
    {
        $customerGroup = CustomerGroup::factory()->create();

        // Verify the relationship method exists and returns BelongsToMany
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $customerGroup->priceLists()
        );
    }

    public function test_customer_group_scope_enabled(): void
    {
        CustomerGroup::factory()->create(['is_enabled' => true]);
        CustomerGroup::factory()->create(['is_enabled' => false]);

        $enabledGroups = CustomerGroup::enabled()->get();

        $this->assertCount(1, $enabledGroups);
        $first = $enabledGroups->first();
        $this->assertNotNull($first);
        $this->assertTrue($first->is_enabled);
    }

    public function test_customer_group_scope_enabled_handles_string_storage(): void
    {
        $enabledGroup = CustomerGroup::factory()->create(['is_enabled' => true, 'is_active' => true]);
        $disabledGroup = CustomerGroup::factory()->create(['is_enabled' => false, 'is_active' => false]);

        // Simulate legacy datasets where boolean flags were persisted as strings while newer rows
        // continue to use integer storage. This mirrors production databases that switched drivers.
        DB::table('customer_groups')->whereKey($enabledGroup->getKey())->update([
            'is_enabled' => 'TRUE',
            'is_active'  => 'TRUE',
        ]);
        DB::table('customer_groups')->whereKey($disabledGroup->getKey())->update([
            'is_enabled' => '0',
            'is_active'  => '0',
        ]);

        $sql = CustomerGroup::withoutGlobalScopes()->enabled()->toSql();
        $resolved = CustomerGroup::withoutGlobalScopes()->enabled()->pluck('id')->all();

        $this->assertStringContainsString('CAST(', $sql);
        $this->assertSame([$enabledGroup->getKey()], $resolved);
    }

    public function test_customer_group_scope_disabled(): void
    {
        CustomerGroup::factory()->create(['is_enabled' => true]);
        CustomerGroup::factory()->create(['is_enabled' => false]);

        $disabledGroups = CustomerGroup::withoutGlobalScopes()->disabled()->get();

        $this->assertCount(1, $disabledGroups);
        $firstDisabled = $disabledGroups->first();
        $this->assertNotNull($firstDisabled);
        $this->assertFalse($firstDisabled->is_enabled);
    }

    public function test_customer_group_scope_active(): void
    {
        CustomerGroup::factory()->create(['is_enabled' => true, 'is_active' => true]);
        CustomerGroup::factory()->create(['is_enabled' => false, 'is_active' => false]);
        $enabledButInactive = CustomerGroup::factory()->create(['is_enabled' => true, 'is_active' => false])->fresh();

        $this->assertTrue($enabledButInactive->is_enabled);
        $this->assertFalse($enabledButInactive->is_active);

        $activeGroups = CustomerGroup::active()->get();

        $this->assertCount(1, $activeGroups);
        $firstActive = $activeGroups->first();
        $this->assertNotNull($firstActive);
        $this->assertTrue($firstActive->is_enabled);
        $this->assertTrue($firstActive->is_active);
    }

    public function test_customer_group_scope_inactive(): void
    {
        CustomerGroup::factory()->create(['is_enabled' => true, 'is_active' => true]);
        CustomerGroup::factory()->create(['is_enabled' => false, 'is_active' => false]);

        $inactiveGroups = CustomerGroup::withoutGlobalScopes()->inactive()->get();

        $this->assertCount(1, $inactiveGroups);
        $firstInactive = $inactiveGroups->first();
        $this->assertNotNull($firstInactive);
        $this->assertFalse($firstInactive->is_enabled);
        $this->assertFalse($firstInactive->is_active);
    }

    public function test_customer_group_scope_with_discount(): void
    {
        CustomerGroup::factory()->create(['discount_percentage' => 10.0, 'discount_fixed' => 0.0]);
        CustomerGroup::factory()->create(['discount_percentage' => 0.0, 'discount_fixed' => 25.0]);
        CustomerGroup::factory()->create(['discount_percentage' => 0.0, 'discount_fixed' => 0.0]);

        $groupsWithDiscount = CustomerGroup::withoutGlobalScopes()->withDiscount()->get();

        $this->assertCount(2, $groupsWithDiscount);
        // Ensure both percentage and fixed only configurations are considered active discounts.
        $this->assertTrue($groupsWithDiscount->every(fn (CustomerGroup $group): bool => $group->hasAnyDiscount()));
    }

    public function test_customer_group_scope_with_special_pricing(): void
    {
        CustomerGroup::factory()->create(['has_special_pricing' => true]);
        CustomerGroup::factory()->create(['has_special_pricing' => false]);

        $groupsWithSpecialPricing = CustomerGroup::withoutGlobalScopes()->withSpecialPricing()->get();

        $this->assertCount(1, $groupsWithSpecialPricing);
        $first = $groupsWithSpecialPricing->first();
        $this->assertNotNull($first);
        $this->assertTrue($first->has_special_pricing);
    }

    public function test_customer_group_scope_by_type(): void
    {
        CustomerGroup::factory()->create(['type' => 'vip']);
        CustomerGroup::factory()->create(['type' => 'regular']);

        $vipGroups = CustomerGroup::withoutGlobalScopes()->byType('vip')->get();

        $this->assertCount(1, $vipGroups);
        $vip = $vipGroups->first();
        $this->assertNotNull($vip);
        $this->assertSame('vip', $vip->type);
    }

    public function test_customer_group_scope_default(): void
    {
        CustomerGroup::factory()->create(['is_default' => true]);
        CustomerGroup::factory()->create(['is_default' => false]);

        $defaultGroups = CustomerGroup::withoutGlobalScopes()->default()->get();

        $this->assertCount(1, $defaultGroups);
        $default = $defaultGroups->first();
        $this->assertNotNull($default);
        $this->assertTrue($default->is_default);
    }

    public function test_customer_group_scope_order_by_priority(): void
    {
        CustomerGroup::factory()->create(['sort_order' => 3]);
        CustomerGroup::factory()->create(['sort_order' => 1]);
        CustomerGroup::factory()->create(['sort_order' => 2]);

        $orderedGroups = CustomerGroup::withoutGlobalScopes()->orderByPriority()->get();

        $firstOrdered = $orderedGroups->first();
        $lastOrdered = $orderedGroups->last();

        $this->assertNotNull($firstOrdered);
        $this->assertNotNull($lastOrdered);
        $this->assertSame(1, $firstOrdered->sort_order);
        $this->assertSame(3, $lastOrdered->sort_order);
    }

    public function test_customer_group_has_discount_rate_method(): void
    {
        $groupWithDiscount = CustomerGroup::factory()->create(['discount_percentage' => 10.0]);
        $groupWithoutDiscount = CustomerGroup::factory()->create(['discount_percentage' => 0.0]);

        $this->assertTrue($groupWithDiscount->hasDiscountRate());
        $this->assertFalse($groupWithoutDiscount->hasDiscountRate());
    }

    public function test_customer_group_has_fixed_discount_method(): void
    {
        $groupWithFixed = CustomerGroup::factory()->create(['discount_fixed' => 15.0]);
        $groupWithoutFixed = CustomerGroup::factory()->create(['discount_fixed' => 0.0]);

        $this->assertTrue($groupWithFixed->hasFixedDiscount());
        $this->assertFalse($groupWithoutFixed->hasFixedDiscount());
    }

    public function test_customer_group_has_any_discount_method(): void
    {
        $groupWithPercentage = CustomerGroup::factory()->create(['discount_percentage' => 10.0, 'discount_fixed' => 0.0]);
        $groupWithFixed = CustomerGroup::factory()->create(['discount_percentage' => 0.0, 'discount_fixed' => 15.0]);
        $groupWithBoth = CustomerGroup::factory()->create(['discount_percentage' => 10.0, 'discount_fixed' => 15.0]);
        $groupWithNone = CustomerGroup::factory()->create(['discount_percentage' => 0.0, 'discount_fixed' => 0.0]);

        $this->assertTrue($groupWithPercentage->hasAnyDiscount());
        $this->assertTrue($groupWithFixed->hasAnyDiscount());
        $this->assertTrue($groupWithBoth->hasAnyDiscount());
        $this->assertFalse($groupWithNone->hasAnyDiscount());
    }

    public function test_customer_group_permission_methods(): void
    {
        $group = CustomerGroup::factory()->create([
            'can_view_catalog' => true,
            'can_view_prices'  => true,
            'can_place_orders' => true,
            'can_use_coupons'  => false,
        ]);

        $this->assertTrue($group->canViewCatalog());
        $this->assertTrue($group->canViewPrices());
        $this->assertTrue($group->canPlaceOrders());
        $this->assertFalse($group->canUseCoupons());
    }

    public function test_customer_group_is_default_method(): void
    {
        $defaultGroup = CustomerGroup::factory()->create(['is_default' => true]);
        $regularGroup = CustomerGroup::factory()->create(['is_default' => false]);

        $this->assertTrue($defaultGroup->isDefault());
        $this->assertFalse($regularGroup->isDefault());
    }

    public function test_customer_group_get_total_discount_for_amount(): void
    {
        $groupWithPercentage = CustomerGroup::factory()->create(['discount_percentage' => 10.0, 'discount_fixed' => 0.0]);
        $groupWithFixed = CustomerGroup::factory()->create(['discount_percentage' => 0.0, 'discount_fixed' => 15.0]);
        $groupWithBoth = CustomerGroup::factory()->create(['discount_percentage' => 10.0, 'discount_fixed' => 5.0]);

        // 10% of 100 = 10
        $this->assertSame(10.0, $groupWithPercentage->getTotalDiscountForAmount(100.0));

        // Fixed 15
        $this->assertSame(15.0, $groupWithFixed->getTotalDiscountForAmount(100.0));

        // 10% of 100 = 10 + 5 fixed = 15
        $this->assertSame(15.0, $groupWithBoth->getTotalDiscountForAmount(100.0));
    }

    public function test_customer_group_has_volume_discounts_method(): void
    {
        $groupWith = CustomerGroup::factory()->create(['has_volume_discounts' => true]);
        $groupWithout = CustomerGroup::factory()->create(['has_volume_discounts' => false]);

        $this->assertTrue($groupWith->hasVolumeDiscounts());
        $this->assertFalse($groupWithout->hasVolumeDiscounts());
    }

    public function test_customer_group_has_special_pricing_method(): void
    {
        $groupWith = CustomerGroup::factory()->create(['has_special_pricing' => true]);
        $groupWithout = CustomerGroup::factory()->create(['has_special_pricing' => false]);

        $this->assertTrue($groupWith->hasSpecialPricing());
        $this->assertFalse($groupWithout->hasSpecialPricing());
    }

    public function test_customer_group_financial_helpers(): void
    {
        $group = CustomerGroup::factory()->create([
            'credit_limit'         => 2000.0,
            'minimum_order_amount' => 150.5,
            'payment_terms'        => 'Net 45',
        ]);

        // Verify the convenience helpers expose the B2B financial rules clearly.
        $this->assertTrue($group->hasCreditLimit());
        $this->assertSame(2000.0, $group->getCreditLimitAmount());
        $this->assertTrue($group->requiresMinimumOrderAmount());
        $this->assertSame(150.5, $group->getMinimumOrderAmount());
        $this->assertSame('Net 45', $group->getPaymentTerms());
    }

    public function test_customer_group_permission_lookup(): void
    {
        $group = CustomerGroup::factory()->create([
            'can_view_prices'  => true,
            'can_place_orders' => false,
            'can_view_catalog' => true,
        ]);

        // Friendly aliases should map to the appropriate boolean attributes.
        $this->assertTrue($group->hasPermission('view_prices'));
        $this->assertFalse($group->hasPermission('place_orders'));
        $this->assertTrue($group->hasPermission('view_catalog'));
        // Unknown permissions should safely default to false to avoid accidental grants.
        $this->assertFalse($group->hasPermission('unknown_permission'));
        // Direct column names should continue to behave identically.
        $this->assertTrue($group->hasPermission('can_view_prices'));
    }

    public function test_customer_group_get_users_count_attribute(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $users = User::factory()->count(5)->create();

        $customerGroup->users()->attach($users->pluck('id'));

        $this->assertSame(5, $customerGroup->users_count);
    }

    public function test_customer_group_metadata_methods(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'metadata' => ['tier' => 'gold', 'points' => 1000],
        ]);

        $this->assertSame('gold', $customerGroup->getMetadata('tier'));
        $this->assertSame(1000, $customerGroup->getMetadata('points'));
        $this->assertSame('default', $customerGroup->getMetadata('nonexistent', 'default'));

        $customerGroup->setMetadata('tier', 'platinum');
        $this->assertSame('platinum', $customerGroup->getMetadata('tier'));
    }

    public function test_customer_group_slug_is_generated_automatically(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'name' => ['lt' => 'Test Grupė', 'en' => 'Test Group'],
            'slug' => null,
        ]);

        $this->assertNotEmpty($customerGroup->slug);
    }

    public function test_customer_group_factory_active_state(): void
    {
        $customerGroup = CustomerGroup::factory()->active()->create();

        $this->assertTrue($customerGroup->is_active);
        $this->assertTrue($customerGroup->is_enabled);
    }

    public function test_customer_group_factory_inactive_state(): void
    {
        $customerGroup = CustomerGroup::factory()->inactive()->create();

        $this->assertFalse($customerGroup->is_active);
        $this->assertFalse($customerGroup->is_enabled);
    }

    public function test_customer_group_factory_default_state(): void
    {
        $customerGroup = CustomerGroup::factory()->default()->create();

        $this->assertTrue($customerGroup->is_default);
        $this->assertTrue($customerGroup->is_active);
        $this->assertTrue($customerGroup->is_enabled);
    }

    public function test_customer_group_factory_with_special_pricing_state(): void
    {
        $customerGroup = CustomerGroup::factory()->withSpecialPricing()->create();

        $this->assertTrue($customerGroup->has_special_pricing);
        $this->assertGreaterThan(0, $customerGroup->discount_percentage);
    }

    public function test_customer_group_factory_with_volume_discounts_state(): void
    {
        $customerGroup = CustomerGroup::factory()->withVolumeDiscounts()->create();

        $this->assertTrue($customerGroup->has_volume_discounts);
    }

    public function test_customer_group_factory_with_fixed_discount_state(): void
    {
        $customerGroup = CustomerGroup::factory()->withFixedDiscount(25.0)->create();

        $this->assertSame('25.00', $customerGroup->discount_fixed);
    }

    public function test_customer_group_factory_with_percentage_discount_state(): void
    {
        $customerGroup = CustomerGroup::factory()->withPercentageDiscount(20.0)->create();

        $this->assertSame(20.0, $customerGroup->discount_percentage);
    }

    public function test_customer_group_factory_vip_state(): void
    {
        $customerGroup = CustomerGroup::factory()->vip()->create();

        $this->assertSame('vip', $customerGroup->type);
        $this->assertTrue($customerGroup->has_special_pricing);
        $this->assertTrue($customerGroup->has_volume_discounts);
    }

    public function test_customer_group_factory_wholesale_state(): void
    {
        $customerGroup = CustomerGroup::factory()->wholesale()->create();

        $this->assertSame('wholesale', $customerGroup->type);
        $this->assertTrue($customerGroup->has_special_pricing);
        $this->assertNotEmpty($customerGroup->minimum_order_amount);
    }

    public function test_customer_group_factory_corporate_state(): void
    {
        $customerGroup = CustomerGroup::factory()->corporate()->create();

        $this->assertSame('corporate', $customerGroup->type);
        $this->assertTrue($customerGroup->has_special_pricing);
        $this->assertSame('net_30', $customerGroup->payment_terms);
        $this->assertNotEmpty($customerGroup->credit_limit);
    }

    public function test_customer_group_soft_deletes(): void
    {
        $customerGroup = CustomerGroup::factory()->create();
        $id = $customerGroup->id;

        $customerGroup->delete();

        $this->assertSoftDeleted('customer_groups', ['id' => $id]);
        $deleted = CustomerGroup::withTrashed()->find($id);
        $this->assertNotNull($deleted);
        $this->assertNotNull($deleted->deleted_at);
    }

    public function test_customer_group_translations_work(): void
    {
        $customerGroup = CustomerGroup::factory()->create([
            'name'        => ['lt' => 'Lietuviškas Pavadinimas', 'en' => 'English Name'],
            'description' => ['lt' => 'Lietuviškas Aprašymas', 'en' => 'English Description'],
        ]);

        $translations = $customerGroup->getTranslations('name');

        $this->assertArrayHasKey('lt', $translations);
        $this->assertArrayHasKey('en', $translations);
    }
}
