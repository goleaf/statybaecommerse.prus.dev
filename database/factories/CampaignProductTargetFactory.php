<?php

declare(strict_types=1);

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
        $targetType = $this->faker->randomElement(['product', 'category']);

        $data = [
            'campaign_id' => Campaign::factory(),
            'target_type' => $targetType,
        ];

        // Set the appropriate target ID based on type
        switch ($targetType) {
            case 'product':
                // Use existing products instead of creating new ones
                $existingProducts = Product::query()->inRandomOrder()->limit(10)->get();
                if ($existingProducts->isNotEmpty()) {
                    $data['product_id'] = $existingProducts->random()->id;
                } else {
                    $data['product_id'] = Product::factory();
                }
                break;
            case 'category':
                // Use existing categories instead of creating new ones
                $existingCategories = Category::query()->inRandomOrder()->limit(10)->get();
                if ($existingCategories->isNotEmpty()) {
                    $data['category_id'] = $existingCategories->random()->id;
                } else {
                    $data['category_id'] = Category::factory();
                }
                break;
        }

        return $data;
    }

    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'product',
            'product_id' => Product::factory(),
            'category_id' => null,
        ]);
    }

    public function category(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'category',
            'product_id' => null,
            'category_id' => Category::factory(),
        ]);
    }


    public function withCampaign(Campaign $campaign): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_id' => $campaign->id,
        ]);
    }

    public function withProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'product',
            'product_id' => $product->id,
            'category_id' => null,
        ]);
    }

    public function withCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'category',
            'product_id' => null,
            'category_id' => $category->id,
        ]);
    }
}
