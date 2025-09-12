<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignProductTarget;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CampaignProductTargetFactory extends Factory
{
    protected $model = CampaignProductTarget::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'product_id' => Product::factory(),
            'category_id' => Category::factory(),
            'target_type' => $this->faker->randomElement(['product', 'category', 'brand', 'collection']),
        ];
    }
}
