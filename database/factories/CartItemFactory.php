<?php declare(strict_types=1);

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
        $totalPrice = $quantity * $unitPrice;

        return [
            'session_id' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,  // Will be set when needed
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'product_snapshot' => [
                'name' => $this->faker->words(3, true),
                'price' => $unitPrice,
                'sku' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{3}'),
                'description' => $this->faker->sentence(),
                'image' => $this->faker->imageUrl(300, 300, 'products'),
                'attributes' => [
                    'color' => $this->faker->safeColorName(),
                    'size' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
                    'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Linen']),
                ],
                'category' => $this->faker->word(),
                'brand' => $this->faker->company(),
                'weight' => $this->faker->randomFloat(2, 0.1, 5.0),
                'dimensions' => [
                    'length' => $this->faker->randomFloat(1, 10, 100),
                    'width' => $this->faker->randomFloat(1, 10, 100),
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
        return $this->state(fn(array $attributes) => [
            'user_id' => null,
            'session_id' => 'guest-' . $this->faker->uuid(),
        ]);
    }

    /**
     * Create a cart item with a specific product variant
     */
    public function withVariant(): static
    {
        return $this->state(function (array $attributes) {
            $variant = ProductVariant::factory()->create([
                'product_id' => $attributes['product_id'] ?? Product::factory(),
            ]);

            return [
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'unit_price' => $variant->price ?? $attributes['unit_price'],
                'total_price' => ($variant->price ?? $attributes['unit_price']) * $attributes['quantity'],
            ];
        });
    }

    /**
     * Create a cart item with high quantity
     */
    public function highQuantity(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->numberBetween(10, 50);

            return [
                'quantity' => $quantity,
                'total_price' => $attributes['unit_price'] * $quantity,
            ];
        });
    }

    /**
     * Create a cart item with expensive product
     */
    public function expensive(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $this->faker->randomFloat(2, 500, 2000);

            return [
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $attributes['quantity'],
                'product_snapshot' => array_merge($attributes['product_snapshot'] ?? [], [
                    'price' => $unitPrice,
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
        return $this->state(fn(array $attributes) => [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Create a cart item for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a cart item with specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product->id,
                'unit_price' => $product->price ?? $attributes['unit_price'],
                'total_price' => ($product->price ?? $attributes['unit_price']) * $attributes['quantity'],
                'product_snapshot' => array_merge($attributes['product_snapshot'] ?? [], [
                    'name' => $product->name,
                    'price' => $product->price,
                    'sku' => $product->sku,
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
        return $this->state(fn(array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-30 days', '-8 days'),
            'updated_at' => $this->faker->dateTimeBetween('-30 days', '-8 days'),
        ]);
    }

    /**
     * Create a recent cart item
     */
    public function recent(): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Create a cart item with minimal product snapshot
     */
    public function minimalSnapshot(): static
    {
        return $this->state(fn(array $attributes) => [
            'product_snapshot' => [
                'name' => $this->faker->words(2, true),
                'price' => $attributes['unit_price'],
                'sku' => $this->faker->regexify('[A-Z]{3}-[0-9]{3}'),
            ],
        ]);
    }
}
