<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\VariantImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VariantImage>
 */
final class VariantImageFactory extends Factory
{
    protected $model = VariantImage::class;

    public function definition(): array
    {
        return [
            'variant_id' => \App\Models\ProductVariant::factory(),
            'image_path' => $this->faker->imageUrl(800, 600, 'products'),
            'alt_text' => $this->faker->sentence(3),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_primary' => $this->faker->boolean(20),  // 20% chance of being primary
        ];
    }

    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    public function secondary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => false,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ]);
    }
}

