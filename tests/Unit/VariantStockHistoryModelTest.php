<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantStockHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VariantStockHistoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_stock_history_belongs_to_variant(): void
    {
        $variant = ProductVariant::factory()->create();
        $stockHistory = VariantStockHistory::factory()->create(['variant_id' => $variant->id]);

        $this->assertInstanceOf(ProductVariant::class, $stockHistory->variant);
        $this->assertEquals($variant->id, $stockHistory->variant->id);
    }

    public function test_variant_stock_history_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $stockHistory = VariantStockHistory::factory()->create(['changed_by' => $user->id]);

        $this->assertInstanceOf(User::class, $stockHistory->changedBy);
        $this->assertEquals($user->id, $stockHistory->changedBy->id);
    }

    public function test_variant_stock_history_fillable_attributes(): void
    {
        $fillable = [
            'variant_id',
            'old_quantity',
            'new_quantity',
            'quantity_change',
            'change_type',
            'change_reason',
            'changed_by',
            'reference_type',
            'reference_id',
        ];

        $stockHistory = new VariantStockHistory;

        $this->assertEquals($fillable, $stockHistory->getFillable());
    }

    public function test_variant_stock_history_casts(): void
    {
        $stockHistory = VariantStockHistory::factory()->create([
            'old_quantity'    => '10',
            'new_quantity'    => '15',
            'quantity_change' => '5',
        ]);

        $this->assertIsInt($stockHistory->old_quantity);
        $this->assertIsInt($stockHistory->new_quantity);
        $this->assertIsInt($stockHistory->quantity_change);
    }

    public function test_is_increase_returns_true_for_positive_change(): void
    {
        $stockHistory = VariantStockHistory::factory()->create(['quantity_change' => 10]);

        $this->assertTrue($stockHistory->isIncrease());
    }

    public function test_is_increase_returns_false_for_negative_change(): void
    {
        $stockHistory = VariantStockHistory::factory()->create(['quantity_change' => -10]);

        $this->assertFalse($stockHistory->isIncrease());
    }

    public function test_is_increase_returns_false_for_zero_change(): void
    {
        $stockHistory = VariantStockHistory::factory()->create(['quantity_change' => 0]);

        $this->assertFalse($stockHistory->isIncrease());
    }

    public function test_is_decrease_returns_true_for_negative_change(): void
    {
        $stockHistory = VariantStockHistory::factory()->create(['quantity_change' => -10]);

        $this->assertTrue($stockHistory->isDecrease());
    }

    public function test_is_decrease_returns_false_for_positive_change(): void
    {
        $stockHistory = VariantStockHistory::factory()->create(['quantity_change' => 10]);

        $this->assertFalse($stockHistory->isDecrease());
    }

    public function test_is_decrease_returns_false_for_zero_change(): void
    {
        $stockHistory = VariantStockHistory::factory()->create(['quantity_change' => 0]);

        $this->assertFalse($stockHistory->isDecrease());
    }

    public function test_get_absolute_change_attribute(): void
    {
        $stockHistory1 = VariantStockHistory::factory()->create(['quantity_change' => 10]);
        $stockHistory2 = VariantStockHistory::factory()->create(['quantity_change' => -10]);
        $stockHistory3 = VariantStockHistory::factory()->create(['quantity_change' => 0]);

        $this->assertEquals(10, $stockHistory1->absolute_change);
        $this->assertEquals(10, $stockHistory2->absolute_change);
        $this->assertEquals(0, $stockHistory3->absolute_change);
    }

    public function test_scope_by_change_type(): void
    {
        VariantStockHistory::factory()->create(['change_type' => 'increase']);
        VariantStockHistory::factory()->create(['change_type' => 'decrease']);
        VariantStockHistory::factory()->create(['change_type' => 'increase']);

        $increases = VariantStockHistory::byChangeType('increase')->get();
        $decreases = VariantStockHistory::byChangeType('decrease')->get();

        $this->assertCount(2, $increases);
        $this->assertCount(1, $decreases);
    }

    public function test_scope_in_date_range(): void
    {
        $oldHistory = VariantStockHistory::factory()->create(['created_at' => now()->subDays(60)]);
        $recentHistory = VariantStockHistory::factory()->create(['created_at' => now()->subDays(15)]);
        $currentHistory = VariantStockHistory::factory()->create(['created_at' => now()]);

        $histories = VariantStockHistory::inDateRange(now()->subDays(30), now())->get();

        $this->assertCount(2, $histories);
        $this->assertTrue($histories->contains($recentHistory));
        $this->assertTrue($histories->contains($currentHistory));
        $this->assertFalse($histories->contains($oldHistory));
    }

    public function test_scope_increases(): void
    {
        VariantStockHistory::factory()->create(['quantity_change' => 10]);
        VariantStockHistory::factory()->create(['quantity_change' => -5]);
        VariantStockHistory::factory()->create(['quantity_change' => 15]);
        VariantStockHistory::factory()->create(['quantity_change' => 0]);

        $increases = VariantStockHistory::increases()->get();

        $this->assertCount(2, $increases);
        $increases->each(function ($history) {
            $this->assertGreaterThan(0, $history->quantity_change);
        });
    }

    public function test_scope_decreases(): void
    {
        VariantStockHistory::factory()->create(['quantity_change' => 10]);
        VariantStockHistory::factory()->create(['quantity_change' => -5]);
        VariantStockHistory::factory()->create(['quantity_change' => -15]);
        VariantStockHistory::factory()->create(['quantity_change' => 0]);

        $decreases = VariantStockHistory::decreases()->get();

        $this->assertCount(2, $decreases);
        $decreases->each(function ($history) {
            $this->assertLessThan(0, $history->quantity_change);
        });
    }

    public function test_scope_recent(): void
    {
        VariantStockHistory::factory()->create(['created_at' => now()->subDays(40)]);
        VariantStockHistory::factory()->create(['created_at' => now()->subDays(20)]);
        VariantStockHistory::factory()->create(['created_at' => now()->subDays(5)]);

        $recent30Days = VariantStockHistory::recent(30)->get();
        $recent10Days = VariantStockHistory::recent(10)->get();

        $this->assertCount(2, $recent30Days);
        $this->assertCount(1, $recent10Days);
    }

    public function test_scope_by_reference(): void
    {
        VariantStockHistory::factory()->create([
            'reference_type' => 'order',
            'reference_id'   => 1,
        ]);

        VariantStockHistory::factory()->create([
            'reference_type' => 'order',
            'reference_id'   => 2,
        ]);

        VariantStockHistory::factory()->create([
            'reference_type' => 'return',
            'reference_id'   => 1,
        ]);

        $orderReference1 = VariantStockHistory::byReference('order', 1)->get();
        $orderReference2 = VariantStockHistory::byReference('order', 2)->get();
        $returnReference1 = VariantStockHistory::byReference('return', 1)->get();

        $this->assertCount(1, $orderReference1);
        $this->assertCount(1, $orderReference2);
        $this->assertCount(1, $returnReference1);
    }

    public function test_record_stock_change_creates_history(): void
    {
        $variant = ProductVariant::factory()->create();
        $user = User::factory()->create();

        $history = VariantStockHistory::recordStockChange(
            variantId: $variant->id,
            oldQuantity: 10,
            newQuantity: 15,
            changeType: 'increase',
            changeReason: 'restock',
            changedBy: $user->id,
            referenceType: 'order',
            referenceId: 123
        );

        $this->assertInstanceOf(VariantStockHistory::class, $history);
        $this->assertEquals($variant->id, $history->variant_id);
        $this->assertEquals(10, $history->old_quantity);
        $this->assertEquals(15, $history->new_quantity);
        $this->assertEquals(5, $history->quantity_change);
        $this->assertEquals('increase', $history->change_type);
        $this->assertEquals('restock', $history->change_reason);
        $this->assertEquals($user->id, $history->changed_by);
        $this->assertEquals('order', $history->reference_type);
        $this->assertEquals(123, $history->reference_id);
    }

    public function test_record_stock_change_with_defaults(): void
    {
        $variant = ProductVariant::factory()->create();

        $history = VariantStockHistory::recordStockChange(
            variantId: $variant->id,
            oldQuantity: 10,
            newQuantity: 8
        );

        $this->assertInstanceOf(VariantStockHistory::class, $history);
        $this->assertEquals($variant->id, $history->variant_id);
        $this->assertEquals(10, $history->old_quantity);
        $this->assertEquals(8, $history->new_quantity);
        $this->assertEquals(-2, $history->quantity_change);
        $this->assertEquals('adjustment', $history->change_type);
        $this->assertNull($history->change_reason);
        $this->assertNull($history->changed_by);
        $this->assertNull($history->reference_type);
        $this->assertNull($history->reference_id);
    }

    public function test_record_stock_change_calculates_quantity_change_correctly(): void
    {
        $variant = ProductVariant::factory()->create();

        $increase = VariantStockHistory::recordStockChange(
            variantId: $variant->id,
            oldQuantity: 10,
            newQuantity: 25
        );

        $decrease = VariantStockHistory::recordStockChange(
            variantId: $variant->id,
            oldQuantity: 25,
            newQuantity: 15
        );

        $this->assertEquals(15, $increase->quantity_change);
        $this->assertEquals(-10, $decrease->quantity_change);
    }

    public function test_factory_creates_valid_stock_history(): void
    {
        $stockHistory = VariantStockHistory::factory()->create();

        $this->assertNotNull($stockHistory->variant_id);
        $this->assertIsInt($stockHistory->old_quantity);
        $this->assertIsInt($stockHistory->new_quantity);
        $this->assertIsInt($stockHistory->quantity_change);
        $this->assertNotNull($stockHistory->change_type);
        $this->assertNotNull($stockHistory->change_reason);
    }

    public function test_factory_increase_state(): void
    {
        $stockHistory = VariantStockHistory::factory()->increase()->create();

        $this->assertEquals('increase', $stockHistory->change_type);
        $this->assertGreaterThan(0, $stockHistory->quantity_change);
    }

    public function test_factory_decrease_state(): void
    {
        $stockHistory = VariantStockHistory::factory()->decrease()->create();

        $this->assertEquals('decrease', $stockHistory->change_type);
        $this->assertLessThan(0, $stockHistory->quantity_change);
    }

    public function test_factory_adjustment_state(): void
    {
        $stockHistory = VariantStockHistory::factory()->adjustment()->create();

        $this->assertEquals('adjustment', $stockHistory->change_type);
    }

    public function test_factory_reserve_state(): void
    {
        $stockHistory = VariantStockHistory::factory()->reserve()->create();

        $this->assertEquals('reserve', $stockHistory->change_type);
        $this->assertLessThan(0, $stockHistory->quantity_change);
    }

    public function test_factory_unreserve_state(): void
    {
        $stockHistory = VariantStockHistory::factory()->unreserve()->create();

        $this->assertEquals('unreserve', $stockHistory->change_type);
        $this->assertGreaterThan(0, $stockHistory->quantity_change);
    }

    public function test_timestamps_are_set(): void
    {
        $stockHistory = VariantStockHistory::factory()->create();

        $this->assertNotNull($stockHistory->created_at);
        $this->assertNotNull($stockHistory->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $stockHistory->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $stockHistory->updated_at);
    }

    public function test_can_update_stock_history(): void
    {
        $stockHistory = VariantStockHistory::factory()->create(['change_reason' => 'old_reason']);

        $stockHistory->update(['change_reason' => 'new_reason']);

        $this->assertEquals('new_reason', $stockHistory->fresh()->change_reason);
    }

    public function test_morph_to_reference_can_be_null(): void
    {
        $stockHistory = VariantStockHistory::factory()->create([
            'reference_type' => null,
            'reference_id'   => null,
        ]);

        $this->assertNull($stockHistory->reference_type);
        $this->assertNull($stockHistory->reference_id);
        $this->assertNull($stockHistory->reference);
    }
}
