<?php

declare(strict_types=1);

namespace Tests\Unit\Eloquent;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_relationships_work(): void
    {
        $product = Product::factory()->create();

        // BelongsTo: brand
        $this->assertInstanceOf(BelongsTo::class, $product->brand());
        $this->assertNotNull($product->brand()->first());

        // HasMany: variants
        ProductVariant::factory()->create(['product_id' => $product->getKey()]);
        $this->assertInstanceOf(HasMany::class, $product->variants());
        $this->assertNotNull($product->variants()->first());

        // HasOne (latestOfMany): latestVariant
        $this->assertInstanceOf(HasOne::class, $product->latestVariant());
        $this->assertNotNull($product->latestVariant()->first());

        // BelongsToMany: categories
        $category = Category::factory()->create();
        $product->categories()->sync([$category->getKey()]);
        $this->assertInstanceOf(BelongsToMany::class, $product->categories());
        $this->assertNotNull($product->categories()->first());
    }

    public function test_order_relationships_work(): void
    {
        // Ensure the user() belongsTo relation has a real parent
        $user  = User::factory()->create();
        $order = Order::factory()->for($user, 'user')->create();

        // BelongsTo: user
        $this->assertInstanceOf(BelongsTo::class, $order->user());
        $this->assertNotNull($order->user()->first());

        // HasMany: items
        OrderItem::factory()->forOrder($order)->create();
        $this->assertInstanceOf(HasMany::class, $order->items());
        $this->assertNotNull($order->items()->first());

        // HasOne: shipping relation type (only asserting relation type here)
        $this->assertInstanceOf(HasOne::class, $order->shipping());
    }
}
