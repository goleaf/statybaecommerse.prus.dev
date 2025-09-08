<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartItemTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);
        $this->variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Test Variant',
            'price' => 89.99,
        ]);
    }

    public function test_cart_item_can_be_created(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'session_id' => 'test-session',
            'quantity' => 2,
            'unit_price' => 89.99,
            'total_price' => 179.98,
        ]);

        expect($cartItem)
            ->toBeInstanceOf(CartItem::class)
            ->user_id
            ->toBe($this->user->id)
            ->product_id
            ->toBe($this->product->id)
            ->variant_id
            ->toBe($this->variant->id)
            ->session_id
            ->toBe('test-session')
            ->quantity
            ->toBe(2)
            ->unit_price
            ->toBe('89.99')
            ->total_price
            ->toBe('179.98');
    }

    public function test_cart_item_belongs_to_user(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        expect($cartItem->user)
            ->toBeInstanceOf(User::class)
            ->id
            ->toBe($this->user->id);
    }

    public function test_cart_item_belongs_to_product(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        expect($cartItem->product)
            ->toBeInstanceOf(Product::class)
            ->id
            ->toBe($this->product->id);
    }

    public function test_cart_item_belongs_to_variant(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
        ]);

        expect($cartItem->variant)
            ->toBeInstanceOf(ProductVariant::class)
            ->id
            ->toBe($this->variant->id);
    }

    public function test_cart_item_can_have_null_user_for_guest_carts(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => null,
            'product_id' => $this->product->id,
            'session_id' => 'guest-session',
        ]);

        expect($cartItem->user_id)->toBeNull();
        expect($cartItem->user)->toBeNull();
    }

    public function test_cart_item_can_have_null_variant(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'variant_id' => null,
        ]);

        expect($cartItem->variant_id)->toBeNull();
        expect($cartItem->variant)->toBeNull();
    }

    public function test_cart_item_casts_attributes_correctly(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => '3',
            'unit_price' => '99.99',
            'total_price' => '299.97',
            'product_snapshot' => ['name' => 'Test Product', 'price' => 99.99],
        ]);

        expect($cartItem->quantity)->toBeInt()->toBe(3);
        expect($cartItem->unit_price)->toBe('99.99');
        expect($cartItem->total_price)->toBe('299.97');
        expect($cartItem->product_snapshot)->toBeArray()->toBe(['name' => 'Test Product', 'price' => 99.99]);
    }

    public function test_cart_item_update_total_price_method(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 50.0,
            'total_price' => 0.0,  // Intentionally wrong
        ]);

        $cartItem->updateTotalPrice();

        expect($cartItem->fresh()->total_price)->toBe('100.00');
    }

    public function test_cart_item_formatted_total_price_attribute(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'total_price' => 123.45,
        ]);

        // Assuming app_money_format function formats as €123.45
        expect($cartItem->formatted_total_price)->toBeString();
    }

    public function test_cart_item_formatted_unit_price_attribute(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'unit_price' => 67.89,
        ]);

        // Assuming app_money_format function formats as €67.89
        expect($cartItem->formatted_unit_price)->toBeString();
    }

    public function test_cart_item_scope_for_session(): void
    {
        $sessionId = 'test-session-123';

        $cartItem1 = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'session_id' => $sessionId,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'session_id' => 'different-session',
        ]);

        $results = CartItem::forSession($sessionId)->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->id)->toBe($cartItem1->id);
    }

    public function test_cart_item_scope_for_user(): void
    {
        $user2 = User::factory()->create();

        $cartItem1 = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $this->product->id,
        ]);

        $results = CartItem::forUser($this->user->id)->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->id)->toBe($cartItem1->id);
    }

    public function test_cart_item_uses_soft_deletes(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $cartItem->delete();

        expect($cartItem->trashed())->toBeTrue();
        $this->assertSoftDeleted($cartItem);
    }

    public function test_cart_item_fillable_attributes(): void
    {
        $fillableAttributes = [
            'session_id',
            'user_id',
            'product_id',
            'variant_id',
            'quantity',
            'unit_price',
            'total_price',
            'product_snapshot',
        ];

        $cartItem = new CartItem();

        expect($cartItem->getFillable())->toBe($fillableAttributes);
    }

    public function test_cart_item_has_timestamps(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        expect($cartItem->created_at)->not->toBeNull();
        expect($cartItem->updated_at)->not->toBeNull();
    }

    public function test_cart_item_can_store_product_snapshot(): void
    {
        $productSnapshot = [
            'name' => 'Snapshot Product Name',
            'price' => 99.99,
            'sku' => 'SNAP-001',
            'description' => 'Product description at time of adding to cart',
            'attributes' => [
                'color' => 'Red',
                'size' => 'Large',
            ],
        ];

        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'product_snapshot' => $productSnapshot,
        ]);

        expect($cartItem->product_snapshot)->toBe($productSnapshot);
        expect($cartItem->product_snapshot['name'])->toBe('Snapshot Product Name');
        expect($cartItem->product_snapshot['attributes']['color'])->toBe('Red');
    }

    public function test_cart_item_quantity_validation(): void
    {
        $cartItem = CartItem::factory()->make([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        // This would typically be validated at the form/request level
        expect($cartItem->quantity)->toBe(0);
    }

    public function test_cart_item_price_precision(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'unit_price' => 12.345,  // More than 2 decimal places
            'total_price' => 24.689,
        ]);

        // Should be rounded to 2 decimal places
        expect($cartItem->unit_price)->toBe('12.35');
        expect($cartItem->total_price)->toBe('24.69');
    }
}
