<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\VariantPricingRuleResource;
use App\Models\CustomerGroup;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantPricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class VariantPricingRuleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_variant_pricing_rules(): void
    {
        $variantPricingRule = VariantPricingRule::factory()->create();

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->assertCanSeeTableRecords([$variantPricingRule]);
    }

    public function test_can_create_variant_pricing_rule(): void
    {
        $productVariant = ProductVariant::factory()->create();
        $customerGroup = CustomerGroup::factory()->create();

        Livewire::test(VariantPricingRuleResource\Pages\CreateVariantPricingRule::class)
            ->fillForm([
                'name' => 'Test Pricing Rule',
                'type' => 'percentage',
                'value' => 10.50,
                'product_variant_id' => $productVariant->id,
                'customer_group_id' => $customerGroup->id,
                'min_quantity' => 1,
                'max_quantity' => 100,
                'priority' => 1,
                'is_active' => true,
                'is_cumulative' => false,
                'description' => 'Test description',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_pricing_rules', [
            'name' => 'Test Pricing Rule',
            'type' => 'percentage',
            'value' => 10.50,
            'product_variant_id' => $productVariant->id,
            'customer_group_id' => $customerGroup->id,
            'min_quantity' => 1,
            'max_quantity' => 100,
            'priority' => 1,
            'is_active' => true,
            'is_cumulative' => false,
        ]);
    }

    public function test_can_edit_variant_pricing_rule(): void
    {
        $variantPricingRule = VariantPricingRule::factory()->create();

        Livewire::test(VariantPricingRuleResource\Pages\EditVariantPricingRule::class, [
            'record' => $variantPricingRule->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Pricing Rule',
                'type' => 'fixed',
                'value' => 25.00,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_pricing_rules', [
            'id' => $variantPricingRule->id,
            'name' => 'Updated Pricing Rule',
            'type' => 'fixed',
            'value' => 25.00,
        ]);
    }

    public function test_can_view_variant_pricing_rule(): void
    {
        $variantPricingRule = VariantPricingRule::factory()->create();

        Livewire::test(VariantPricingRuleResource\Pages\ViewVariantPricingRule::class, [
            'record' => $variantPricingRule->getRouteKey(),
        ])
            ->assertCanSeeText($variantPricingRule->name);
    }

    public function test_can_delete_variant_pricing_rule(): void
    {
        $variantPricingRule = VariantPricingRule::factory()->create();

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->callTableAction('delete', $variantPricingRule);

        $this->assertSoftDeleted('variant_pricing_rules', [
            'id' => $variantPricingRule->id,
        ]);
    }

    public function test_can_toggle_active_status(): void
    {
        $variantPricingRule = VariantPricingRule::factory()->create(['is_active' => false]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->callTableAction('toggle_active', $variantPricingRule);

        $this->assertDatabaseHas('variant_pricing_rules', [
            'id' => $variantPricingRule->id,
            'is_active' => true,
        ]);
    }

    public function test_can_bulk_activate_variant_pricing_rules(): void
    {
        $variantPricingRules = VariantPricingRule::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->callTableBulkAction('activate', $variantPricingRules);

        foreach ($variantPricingRules as $rule) {
            $this->assertDatabaseHas('variant_pricing_rules', [
                'id' => $rule->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_variant_pricing_rules(): void
    {
        $variantPricingRules = VariantPricingRule::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->callTableBulkAction('deactivate', $variantPricingRules);

        foreach ($variantPricingRules as $rule) {
            $this->assertDatabaseHas('variant_pricing_rules', [
                'id' => $rule->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_filter_by_type(): void
    {
        $percentageRule = VariantPricingRule::factory()->create(['type' => 'percentage']);
        $fixedRule = VariantPricingRule::factory()->create(['type' => 'fixed']);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->filterTable('type', 'percentage')
            ->assertCanSeeTableRecords([$percentageRule])
            ->assertCanNotSeeTableRecords([$fixedRule]);
    }

    public function test_can_filter_by_product_variant(): void
    {
        $productVariant1 = ProductVariant::factory()->create();
        $productVariant2 = ProductVariant::factory()->create();

        $rule1 = VariantPricingRule::factory()->create(['product_variant_id' => $productVariant1->id]);
        $rule2 = VariantPricingRule::factory()->create(['product_variant_id' => $productVariant2->id]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->filterTable('product_variant_id', $productVariant1->id)
            ->assertCanSeeTableRecords([$rule1])
            ->assertCanNotSeeTableRecords([$rule2]);
    }

    public function test_can_filter_by_customer_group(): void
    {
        $customerGroup1 = CustomerGroup::factory()->create();
        $customerGroup2 = CustomerGroup::factory()->create();

        $rule1 = VariantPricingRule::factory()->create(['customer_group_id' => $customerGroup1->id]);
        $rule2 = VariantPricingRule::factory()->create(['customer_group_id' => $customerGroup2->id]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->filterTable('customer_group_id', $customerGroup1->id)
            ->assertCanSeeTableRecords([$rule1])
            ->assertCanNotSeeTableRecords([$rule2]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $activeRule = VariantPricingRule::factory()->create(['is_active' => true]);
        $inactiveRule = VariantPricingRule::factory()->create(['is_active' => false]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeRule])
            ->assertCanNotSeeTableRecords([$inactiveRule]);
    }

    public function test_can_filter_by_cumulative_status(): void
    {
        $cumulativeRule = VariantPricingRule::factory()->create(['is_cumulative' => true]);
        $nonCumulativeRule = VariantPricingRule::factory()->create(['is_cumulative' => false]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->filterTable('is_cumulative', true)
            ->assertCanSeeTableRecords([$cumulativeRule])
            ->assertCanNotSeeTableRecords([$nonCumulativeRule]);
    }

    public function test_form_validation_requires_name(): void
    {
        Livewire::test(VariantPricingRuleResource\Pages\CreateVariantPricingRule::class)
            ->fillForm([
                'name' => '',
                'type' => 'percentage',
                'value' => 10.50,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_form_validation_requires_type(): void
    {
        Livewire::test(VariantPricingRuleResource\Pages\CreateVariantPricingRule::class)
            ->fillForm([
                'name' => 'Test Rule',
                'type' => '',
                'value' => 10.50,
            ])
            ->call('create')
            ->assertHasFormErrors(['type' => 'required']);
    }

    public function test_form_validation_requires_value(): void
    {
        Livewire::test(VariantPricingRuleResource\Pages\CreateVariantPricingRule::class)
            ->fillForm([
                'name' => 'Test Rule',
                'type' => 'percentage',
                'value' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['value' => 'required']);
    }

    public function test_form_validation_requires_product_variant(): void
    {
        Livewire::test(VariantPricingRuleResource\Pages\CreateVariantPricingRule::class)
            ->fillForm([
                'name' => 'Test Rule',
                'type' => 'percentage',
                'value' => 10.50,
                'product_variant_id' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['product_variant_id' => 'required']);
    }

    public function test_can_sort_by_priority(): void
    {
        $rule1 = VariantPricingRule::factory()->create(['priority' => 1]);
        $rule2 = VariantPricingRule::factory()->create(['priority' => 3]);
        $rule3 = VariantPricingRule::factory()->create(['priority' => 2]);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->sortTable('priority')
            ->assertCanSeeTableRecordsInOrder([$rule2, $rule3, $rule1]);
    }

    public function test_can_search_by_name(): void
    {
        $rule1 = VariantPricingRule::factory()->create(['name' => 'Special Discount']);
        $rule2 = VariantPricingRule::factory()->create(['name' => 'Bulk Pricing']);

        Livewire::test(VariantPricingRuleResource\Pages\ListVariantPricingRules::class)
            ->searchTable('Special')
            ->assertCanSeeTableRecords([$rule1])
            ->assertCanNotSeeTableRecords([$rule2]);
    }
}
