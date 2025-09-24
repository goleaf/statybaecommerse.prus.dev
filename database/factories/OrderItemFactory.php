<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 1, 100);
        $total = $quantity * $unitPrice;

        $variant = $this->faker->boolean(30) ? ProductVariant::factory()->create() : null;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => $variant?->id,
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'price' => $unitPrice, // Same as unit_price for consistency
            'total' => $total,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'price' => $product->price,
        ]);
    }

    public function forVariant(ProductVariant $variant): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'unit_price' => $variant->price,
            'price' => $variant->price,
        ]);
    }

    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(10, 100),
        ]);
    }

    public function lowQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1, 3),
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => $this->faker->randomFloat(2, 100, 1000),
            'price' => $this->faker->randomFloat(2, 100, 1000),
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => $this->faker->randomFloat(2, 0.1, 10),
            'price' => $this->faker->randomFloat(2, 0.1, 10),
        ]);
    }
}
