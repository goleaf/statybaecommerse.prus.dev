<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignProductTarget;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignProductTarget>
 */
final class CampaignProductTargetFactory extends Factory
{
    protected $model = CampaignProductTarget::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'product_id' => $this->faker->optional(0.6)->randomElement(Product::pluck('id')->toArray()),
            'category_id' => $this->faker->optional(0.4)->randomElement(Category::pluck('id')->toArray()),
            'target_type' => $this->faker->randomElement(['product', 'category', 'brand', 'collection']),
        ];
    }

    public function product(): static
    {
        return $this->state(fn(array $attributes) => [
            'target_type' => 'product',
            'product_id' => Product::factory(),
            'category_id' => null,
        ]);
    }

    public function category(): static
    {
        return $this->state(fn(array $attributes) => [
            'target_type' => 'category',
            'product_id' => null,
            'category_id' => Category::factory(),
        ]);
    }

    public function brand(): static
    {
        return $this->state(fn(array $attributes) => [
            'target_type' => 'brand',
            'product_id' => null,
            'category_id' => null,
        ]);
    }

    public function collection(): static
    {
        return $this->state(fn(array $attributes) => [
            'target_type' => 'collection',
            'product_id' => null,
            'category_id' => null,
        ]);
    }
}
