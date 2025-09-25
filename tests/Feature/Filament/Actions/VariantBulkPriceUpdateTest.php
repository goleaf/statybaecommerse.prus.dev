<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Actions\VariantBulkPriceUpdate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VariantBulkPriceUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    private Collection $variants;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->product = Product::factory()->create();

        $this->variants = ProductVariant::factory()->count(3)->create([
            'product_id' => $this->product->id,
            'price' => 100.0,
            'wholesale_price' => 80.0,
            'member_price' => 90.0,
            'promotional_price' => 85.0,
            'is_on_sale' => false,
        ]);
    }

    public function test_can_create_variant_bulk_price_update_action(): void
    {
        $action = VariantBulkPriceUpdate::make();

        expect($action)
            ->toBeInstanceOf(Action::class)
            ->and($action->getName())
            ->toBe('bulk_price_update');
    }

    public function test_action_has_correct_properties(): void
    {
        $action = VariantBulkPriceUpdate::make();

        expect($action->getLabel())
            ->toBe(__('product_variants.actions.bulk_price_update'))
            ->and($action->getIcon())
            ->toBe('heroicon-o-currency-euro')
            ->and($action->getColor())
            ->toBe('warning');
    }

    public function test_action_form_has_required_fields(): void
    {
        $action = VariantBulkPriceUpdate::make();
        $form = $action->getForm();

        expect($form->getComponents())
            ->toHaveCount(10);  // All form fields
    }

    public function test_can_update_prices_with_percentage_increase(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'percentage',
            'update_value' => 10,  // 10% increase
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test price update',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->price)
                ->toBe(110.0);  // 100 * 1.10
        }
    }

    public function test_can_update_prices_with_fixed_amount_increase(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'fixed_amount',
            'update_value' => 15.0,
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test fixed amount update',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->price)
                ->toBe(115.0);  // 100 + 15
        }
    }

    public function test_can_multiply_prices(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'multiply_by',
            'update_value' => 1.5,
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test multiply update',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->price)
                ->toBe(150.0);  // 100 * 1.5
        }
    }

    public function test_can_set_prices_to_specific_value(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'set_to',
            'update_value' => 200.0,
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test set to update',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->price)
                ->toBe(200.0);
        }
    }

    public function test_can_update_wholesale_prices(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'wholesale_price',
            'update_type' => 'percentage',
            'update_value' => 20,  // 20% increase
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test wholesale update',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->wholesale_price)
                ->toBe(96.0);  // 80 * 1.20
        }
    }

    public function test_skips_sale_items_when_apply_to_sale_items_is_false(): void
    {
        // Set one variant as on sale
        $this->variants->first()->update(['is_on_sale' => true]);

        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'percentage',
            'update_value' => 10,
            'apply_to_sale_items' => false,  // Don't apply to sale items
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test skip sale items',
        ];

        $action->action($data, $this->variants);

        // Check that sale item wasn't updated
        $saleVariant = $this->variants->first();
        $saleVariant->refresh();
        expect($saleVariant->price)
            ->toBe(100.0);  // Should remain unchanged

        // Check that non-sale items were updated
        foreach ($this->variants->skip(1) as $variant) {
            $variant->refresh();
            expect($variant->price)
                ->toBe(110.0);
        }
    }

    public function test_can_update_compare_price(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'percentage',
            'update_value' => 10,
            'apply_to_sale_items' => true,
            'update_compare_price' => true,
            'compare_price_action' => 'match_new_price',
            'set_sale_period' => false,
            'change_reason' => 'Test compare price update',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->compare_price)
                ->toBe(110.0);  // Should match new price
        }
    }

    public function test_can_set_sale_period(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $saleStartDate = now()->addDay();
        $saleEndDate = now()->addDays(7);

        $data = [
            'price_type' => 'price',
            'update_type' => 'percentage',
            'update_value' => 10,
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => true,
            'sale_start_date' => $saleStartDate,
            'sale_end_date' => $saleEndDate,
            'change_reason' => 'Test sale period',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->is_on_sale)
                ->toBeTrue()
                ->and($variant->sale_start_date->format('Y-m-d H:i:s'))
                ->toBe($saleStartDate->format('Y-m-d H:i:s'))
                ->and($variant->sale_end_date->format('Y-m-d H:i:s'))
                ->toBe($saleEndDate->format('Y-m-d H:i:s'));
        }
    }

    public function test_prevents_negative_prices(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'fixed_amount',
            'update_value' => -150.0,  // This would make price negative
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test negative price prevention',
        ];

        $action->action($data, $this->variants);

        foreach ($this->variants as $variant) {
            $variant->refresh();
            expect($variant->price)
                ->toBe(0.0);  // Should be clamped to 0
        }
    }

    public function test_records_price_change_history(): void
    {
        $action = VariantBulkPriceUpdate::make();

        $data = [
            'price_type' => 'price',
            'update_type' => 'percentage',
            'update_value' => 10,
            'apply_to_sale_items' => true,
            'update_compare_price' => false,
            'set_sale_period' => false,
            'change_reason' => 'Test history recording',
        ];

        $action->action($data, $this->variants);

        // Check that price change history was recorded
        foreach ($this->variants as $variant) {
            $this->assertDatabaseHas('variant_price_histories', [
                'variant_id' => $variant->id,
                'old_price' => 100.0,
                'new_price' => 110.0,
                'price_type' => 'price',
                'reason' => 'Test history recording',
                'changed_by' => $this->user->id,
            ]);
        }
    }
}
