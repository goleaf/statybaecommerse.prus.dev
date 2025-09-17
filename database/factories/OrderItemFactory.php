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
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 5, 500);
        $total = $unitPrice * $quantity;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => $this->faker->optional(0.3)->randomElement(ProductVariant::pluck('id')->toArray()),
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->bothify('SKU-####-???'),
            'quantity' => $quantity,
            'unit_price' => round($unitPrice, 2),
            'price' => round($unitPrice, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Indicate that the order item has a specific quantity.
     */
    public function quantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $unitPrice = $attributes['unit_price'] ?? $this->faker->randomFloat(2, 5, 500);

            return [
                'quantity' => $quantity,
                'total' => round($unitPrice * $quantity, 2),
            ];
        });
    }

    /**
     * Indicate that the order item has a specific unit price.
     */
    public function unitPrice(float $unitPrice): static
    {
        return $this->state(function (array $attributes) use ($unitPrice) {
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 10);

            return [
                'unit_price' => round($unitPrice, 2),
                'price' => round($unitPrice, 2),
                'total' => round($unitPrice * $quantity, 2),
            ];
        });
    }

    /**
     * Indicate that the order item has a high value.
     */
    public function highValue(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 5);
            $unitPrice = $this->faker->randomFloat(2, 100, 1000);

            return [
                'unit_price' => round($unitPrice, 2),
                'price' => round($unitPrice, 2),
                'total' => round($unitPrice * $quantity, 2),
            ];
        });
    }

    /**
     * Indicate that the order item has a low value.
     */
    public function lowValue(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $attributes['quantity'] ?? $this->faker->numberBetween(1, 10);
            $unitPrice = $this->faker->randomFloat(2, 1, 20);

            return [
                'unit_price' => round($unitPrice, 2),
                'price' => round($unitPrice, 2),
                'total' => round($unitPrice * $quantity, 2),
            ];
        });
    }

    /**
     * Indicate that the order item has a product variant.
     */
    public function withVariant(): static
    {
        return $this->state(function (array $attributes) {
            $product = Product::factory()->create();
            $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

            return [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'name' => $product->name,
                'sku' => $variant->sku,
                'unit_price' => round((float) $variant->price, 2),
                'price' => round((float) $variant->price, 2),
                'total' => round(((float) $variant->price) * ($attributes['quantity'] ?? 1), 2),
            ];
        });
    }
}
