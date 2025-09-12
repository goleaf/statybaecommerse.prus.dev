<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'product_variant_id' => null,
            'name' => $product->name,
            'sku' => $product->sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $quantity,
        ];
    }

    public function withVariant(): static
    {
        return $this->state(function (array $attributes) {
            $variant = ProductVariant::factory()->create();
            return [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'name' => $variant->product->name . ' - ' . $variant->name,
                'sku' => $variant->sku,
            ];
        });
    }
}



