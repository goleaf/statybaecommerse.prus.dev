<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
final class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);

        return [
            'session_id'       => $this->faker->uuid(),
            'user_id'          => User::factory(),
            'product_id'       => Product::factory(),
            'variant_id'       => null,  // Will be set when needed
            'quantity'         => $quantity,
            'minimum_quantity' => 1,
            'unit_price'       => $unitPrice,
            // Seed a nominal discount so calculations that rely on the column remain realistic.
            'discount_amount' => 0.0,
            // Keep the "price" column in sync with the resolved unit price so downstream
            // calculations stay deterministic even when factories override the unit price.
            'price' => static function (array $attributes): float {
                // Default to the resolved unit price so overriding either attribute keeps
                // the persisted values synchronised for pricing calculations.
                /** @var mixed $rawUnitPrice */
                $rawUnitPrice = $attributes['unit_price'] ?? 0.0;

                return is_numeric($rawUnitPrice) ? (float) $rawUnitPrice : 0.0;
            },
            // Mirror the derived subtotal based on whichever quantity/unit price combo the
            // caller finalises, allowing test overrides to remain authoritative.
            'total_price' => static function (array $attributes) use ($quantity): float {
                /** @var mixed $rawUnitPrice */
                $rawUnitPrice = $attributes['unit_price'] ?? 0.0;
                /** @var mixed $rawQuantity */
                $rawQuantity = $attributes['quantity'] ?? $quantity;

                $resolvedUnitPrice = is_numeric($rawUnitPrice) ? (float) $rawUnitPrice : 0.0;
                $resolvedQuantity = is_numeric($rawQuantity) ? (int) $rawQuantity : $quantity;

                return $resolvedUnitPrice * $resolvedQuantity;
            },
            'product_snapshot' => [
                'name'        => $this->faker->words(3, true),
                'price'       => $unitPrice,
                'sku'         => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{3}'),
                'description' => $this->faker->sentence(),
                'image'       => $this->faker->imageUrl(300, 300, 'products'),
                'attributes'  => [
                    'color'    => $this->faker->safeColorName(),
                    'size'     => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
                    'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Linen']),
                ],
                'category'   => $this->faker->word(),
                'brand'      => $this->faker->company(),
                'weight'     => $this->faker->randomFloat(2, 0.1, 5.0),
                'dimensions' => [
                    'length' => $this->faker->randomFloat(1, 10, 100),
                    'width'  => $this->faker->randomFloat(1, 10, 100),
                    'height' => $this->faker->randomFloat(1, 1, 50),
                ],
            ],
        ];
    }

    /**
     * Create a cart item for a guest user (no user_id)
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id'    => null,
            'session_id' => 'guest-' . $this->faker->uuid(),
        ]);
    }

    /**
     * Create a cart item with a specific product variant
     */
    public function withVariant(): static
    {
        return $this->state(function (array $attributes): array {
            $variant = ProductVariant::factory()->create([
                'product_id' => $attributes['product_id'] ?? Product::factory(),
            ]);

            /** @var mixed $rawUnitPrice */
            $rawUnitPrice = $attributes['unit_price'] ?? 0.0;
            /** @var mixed $rawQuantity */
            $rawQuantity = $attributes['quantity'] ?? 1;

            $resolvedUnitPrice = $variant->price ?? (is_numeric($rawUnitPrice) ? (float) $rawUnitPrice : 0.0);
            $resolvedQuantity = is_numeric($rawQuantity) ? (int) $rawQuantity : 1;

            return [
                'variant_id'  => $variant->id,
                'product_id'  => $variant->product_id,
                'unit_price'  => $resolvedUnitPrice,
                'total_price' => $resolvedUnitPrice * $resolvedQuantity,
            ];
        });
    }

    /**
     * Create a cart item with high quantity
     */
    public function highQuantity(): static
    {
        return $this->state(function (array $attributes): array {
            $quantity = $this->faker->numberBetween(10, 50);

            /** @var mixed $rawUnitPrice */
            $rawUnitPrice = $attributes['unit_price'] ?? 0.0;

            $resolvedUnitPrice = is_numeric($rawUnitPrice) ? (float) $rawUnitPrice : 0.0;

            return [
                'quantity'    => $quantity,
                'total_price' => $resolvedUnitPrice * $quantity,
            ];
        });
    }

    /**
     * Create a cart item with expensive product
     */
    public function expensive(): static
    {
        return $this->state(function (array $attributes): array {
            $unitPrice = $this->faker->randomFloat(2, 500, 2000);

            /** @var mixed $rawQuantity */
            $rawQuantity = $attributes['quantity'] ?? 1;
            $resolvedQuantity = is_numeric($rawQuantity) ? (int) $rawQuantity : 1;

            $existingSnapshot = $attributes['product_snapshot'] ?? [];
            $snapshot = is_array($existingSnapshot) ? $existingSnapshot : [];

            return [
                'unit_price'       => $unitPrice,
                'total_price'      => $unitPrice * $resolvedQuantity,
                'product_snapshot' => array_merge($snapshot, [
                    'price'    => $unitPrice,
                    'category' => 'Premium',
                ]),
            ];
        });
    }

    /**
     * Create a cart item with specific session
     */
    public function forSession(string $sessionId): static
    {
        return $this->state(fn (array $attributes): array => [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Create a cart item for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a cart item with specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product): array {
            /** @var mixed $rawUnitPrice */
            $rawUnitPrice = $attributes['unit_price'] ?? 0.0;
            /** @var mixed $rawQuantity */
            $rawQuantity = $attributes['quantity'] ?? 1;

            $resolvedUnitPrice = is_numeric($product->price)
                ? (float) $product->price
                : (is_numeric($rawUnitPrice) ? (float) $rawUnitPrice : 0.0);
            $resolvedQuantity = is_numeric($rawQuantity) ? (int) $rawQuantity : 1;

            $existingSnapshot = $attributes['product_snapshot'] ?? [];
            $snapshot = is_array($existingSnapshot) ? $existingSnapshot : [];

            return [
                'product_id'       => $product->id,
                'unit_price'       => $resolvedUnitPrice,
                'total_price'      => $resolvedUnitPrice * $resolvedQuantity,
                'product_snapshot' => array_merge($snapshot, [
                    'name'        => $product->name,
                    'price'       => $product->price,
                    'sku'         => $product->sku,
                    'description' => $product->description,
                ]),
            ];
        });
    }

    /**
     * Create an old cart item (for testing cleanup functionality)
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => $this->faker->dateTimeBetween('-30 days', '-8 days'),
            'updated_at' => $this->faker->dateTimeBetween('-30 days', '-8 days'),
        ]);
    }

    /**
     * Create a recent cart item
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Create a cart item with minimal product snapshot
     */
    public function minimalSnapshot(): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_snapshot' => [
                'name'  => $this->faker->words(2, true),
                'price' => $attributes['unit_price'],
                'sku'   => $this->faker->regexify('[A-Z]{3}-[0-9]{3}'),
            ],
        ]);
    }
}
