<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    public function definition(): array
    {
        return [
            'wishlist_id' => UserWishlist::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'quantity' => $this->faker->numberBetween(1, 5),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Create a wishlist item with a specific product
     */
    public function withProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product->id,
                'variant_id' => $product->variants()->exists()
                    ? $product->variants()->inRandomOrder()->first()->id
                    : null,
            ];
        });
    }

    /**
     * Create a wishlist item with a specific variant
     */
    public function withVariant(ProductVariant $variant): static
    {
        return $this->state(function (array $attributes) use ($variant) {
            return [
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
            ];
        });
    }

    /**
     * Create a wishlist item with high quantity
     */
    public function highQuantity(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => $this->faker->numberBetween(10, 50),
                'notes' => 'Bulk order',
            ];
        });
    }

    /**
     * Create a wishlist item with notes
     */
    public function withNotes(): static
    {
        return $this->state(function (array $attributes) {
            $notes = [
                'Really want this!',
                'For next month',
                'Gift for someone',
                'Need to check reviews first',
                'Waiting for sale',
                'High priority',
                'For my collection',
                'Need to compare prices',
                'Check if available in store',
                'Perfect for my needs',
            ];

            return [
                'notes' => $this->faker->randomElement($notes),
            ];
        });
    }
}
