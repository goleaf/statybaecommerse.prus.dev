<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignProductTarget;
use App\Models\Category;
use App\Models\Collection;
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
        $targetType = $this->faker->randomElement(['product', 'category', 'brand', 'collection']);

        $data = [
            'campaign_id' => Campaign::factory(),
            'target_type' => $targetType,
            'priority' => $this->faker->numberBetween(0, 100),
            'weight' => $this->faker->numberBetween(1, 100),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80),
            'is_featured' => $this->faker->boolean(20),
            'conditions' => $this->faker->optional(0.3)->randomElement([
                '{"min_price": 10, "max_price": 100}',
                '{"category_required": true}',
                '{"brand_required": true}',
                null,
            ]),
            'notes' => $this->faker->optional(0.4)->sentence(),
        ];

        // Set the appropriate target ID based on type
        switch ($targetType) {
            case 'product':
                $data['product_id'] = Product::factory();
                break;
            case 'category':
                $data['category_id'] = Category::factory();
                break;
            case 'brand':
                $data['brand_id'] = Brand::factory();
                break;
            case 'collection':
                $data['collection_id'] = Collection::factory();
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
            'brand_id' => null,
            'collection_id' => null,
        ]);
    }

    public function category(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'category',
            'product_id' => null,
            'category_id' => Category::factory(),
            'brand_id' => null,
            'collection_id' => null,
        ]);
    }

    public function brand(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'brand',
            'product_id' => null,
            'category_id' => null,
            'brand_id' => Brand::factory(),
            'collection_id' => null,
        ]);
    }

    public function collection(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'collection',
            'product_id' => null,
            'category_id' => null,
            'brand_id' => null,
            'collection_id' => Collection::factory(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(80, 100),
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(0, 40),
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
            'brand_id' => null,
            'collection_id' => null,
        ]);
    }

    public function withCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'category',
            'product_id' => null,
            'category_id' => $category->id,
            'brand_id' => null,
            'collection_id' => null,
        ]);
    }

    public function withBrand(Brand $brand): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'brand',
            'product_id' => null,
            'category_id' => null,
            'brand_id' => $brand->id,
            'collection_id' => null,
        ]);
    }

    public function withCollection(Collection $collection): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => 'collection',
            'product_id' => null,
            'category_id' => null,
            'brand_id' => null,
            'collection_id' => $collection->id,
        ]);
    }
}
