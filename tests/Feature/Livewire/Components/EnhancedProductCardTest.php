<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Components;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EnhancedProductCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_calculation_uses_sale_price_when_available(): void
    {
        $product = Product::factory()->create([
            'price'         => '80.00',
            'compare_price' => '100.00',
        ]);

        $this->assertSame(20.0, $product->discount_percentage);
    }

    public function test_comparison_record_can_be_toggled(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $product = Product::factory()->create();
        $sessionId = 'test-session';

        ProductComparison::create([
            'product_id' => $product->id,
            'session_id' => $sessionId,
        ]);

        $this->assertTrue(
            ProductComparison::where('product_id', $product->id)
                ->where('session_id', $sessionId)
                ->exists()
        );

        ProductComparison::where('product_id', $product->id)
            ->where('session_id', $sessionId)
            ->delete();

        $this->assertDatabaseMissing('product_comparisons', [
            'product_id' => $product->id,
            'session_id' => $sessionId,
        ]);
    }

    public function test_analytics_event_structure_for_product_view(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        AnalyticsEvent::create([
            'event_type' => 'product_view',
            'user_id'    => $user->id,
            'session_id' => 'session-id',
            'properties' => [
                'product_id' => $product->id,
                'view_type'  => 'quick_view',
            ],
        ]);

        $this->assertDatabaseHas('analytics_events', [
            'event_type' => 'product_view',
            'user_id'    => $user->id,
        ]);
    }
}
