<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VariantAnalytics>
 */
final class VariantAnalyticsFactory extends Factory
{
    protected $model = VariantAnalytics::class;

    public function definition(): array
    {
        $views = $this->faker->numberBetween(100, 5000);
        $clicks = $this->faker->numberBetween(10, (int) ($views * 0.8));
        $addToCart = $this->faker->numberBetween(1, (int) ($clicks * 0.5));
        $purchases = $this->faker->numberBetween(0, (int) ($addToCart * 0.8));
        $revenue = $purchases * $this->faker->numberBetween(20, 200);

        return [
            'product_id'      => Product::factory(),
            'variant_id'      => ProductVariant::factory(),
            'date'            => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'date_bucket'     => function (array $attributes) {
                return 'daily:' . $attributes['date'];
            },
            'views'           => $views,
            'clicks'          => $clicks,
            'add_to_cart'     => $addToCart,
            'purchases'       => $purchases,
            'revenue'         => $revenue,
            'conversion_rate' => $views > 0 ? round(($purchases / $views) * 100, 4) : 0,
        ];
    }
}
