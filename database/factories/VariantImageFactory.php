<?php

declare(strict_types=1);

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
            // Persist a relative path that mirrors the convention used by the resource forms.
            'image_path' => 'variant-images/' . $this->faker->unique()->uuid . '.jpg',
            'alt_text'   => $this->faker->sentence(3),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_primary' => $this->faker->boolean(20),  // 20% chance of being primary
            'is_active'  => true,                               // Default to active so global scopes match admin expectations
            'file_size'  => $this->faker->numberBetween(10240, 5 * 1024 * 1024),
            'dimensions' => $this->faker->numberBetween(600, 1200) . 'x' . $this->faker->numberBetween(400, 900),
        ];
    }

    /**
     * Helper state to guarantee the image is primary for testing scenarios.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    /**
     * Helper state for explicitly non-primary images.
     */
    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => false,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ]);
    }
}
