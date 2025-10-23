<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PriceListItem>
 */
final class PriceListItemFactory extends Factory
{
    protected $model = PriceListItem::class;

    public function definition(): array
    {
        $netAmount = $this->faker->randomFloat(2, 5, 500);
        $compareAmount = $this->faker->boolean(40)
            ? round($netAmount * $this->faker->randomFloat(2, 1.05, 1.5), 2)
            : null;

        $validFrom = $this->faker->optional(0.55)->dateTimeBetween('-1 month', '+1 month');
        $validUntil = $validFrom
            ? $this->faker->optional(0.5)->dateTimeBetween($validFrom, '+3 months')
            : null;

        $minQuantity = $this->faker->optional(0.4)->numberBetween(1, 5);
        $maxQuantity = $minQuantity
            ? $this->faker->optional(0.4)->numberBetween($minQuantity, $minQuantity + 25)
            : $this->faker->optional(0.4)->numberBetween(6, 40);

        $name = $this->faker->words(3, true);

        return [
            'price_list_id'  => PriceList::factory(),
            'product_id'     => null,
            'variant_id'     => ProductVariant::factory(),
            'net_amount'     => $netAmount,
            'compare_amount' => $compareAmount,
            'name'           => [
                'en' => $name,
                'lt' => $name,
            ],
            'description' => [
                'en' => $this->faker->sentence(),
                'lt' => $this->faker->sentence(),
            ],
            'notes' => [
                'en' => $this->faker->optional(0.3)->sentence(),
                'lt' => $this->faker->optional(0.3)->sentence(),
            ],
            'is_active'    => true,
            'is_featured'  => $this->faker->boolean(30),
            'priority'     => $this->faker->numberBetween(1, 50),
            'min_quantity' => $minQuantity,
            'max_quantity' => $maxQuantity,
            'valid_from'   => $validFrom,
            'valid_until'  => $validUntil,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PriceListItem $item): void {
            $variant = $item->variant()->with('product')->first();

            if (! $variant) {
                $product = Product::factory()->create();
                $variant = ProductVariant::factory()->for($product)->create();
                $item->variant()->associate($variant);
            }

            $product = $variant->product;

            if (! $product) {
                $product = Product::factory()->create();
                $variant->product()->associate($product);
                $variant->save();
            }

            if ($item->product_id !== $product->id) {
                $item->product()->associate($product);
            }

            if ($item->isDirty()) {
                $item->save();
            }
        });
    }
}
