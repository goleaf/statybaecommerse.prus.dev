<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\NewsImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\NewsImage>
 */
final class NewsImageFactory extends Factory
{
    protected $model = NewsImage::class;

    public function definition(): array
    {
        return [
            'file_path' => 'news/images/' . fake()->uuid() . '.jpg',
            'alt_text' => fake()->sentence(3),
            'caption' => fake()->sentence(),
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'file_size' => fake()->numberBetween(1000, 5000000), // 1KB to 5MB
            'mime_type' => 'image/jpeg',
            'dimensions' => [
                'width' => fake()->numberBetween(800, 2000),
                'height' => fake()->numberBetween(600, 1500),
            ],
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function png(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path' => 'news/images/' . fake()->uuid() . '.png',
            'mime_type' => 'image/png',
        ]);
    }

    public function webp(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path' => 'news/images/' . fake()->uuid() . '.webp',
            'mime_type' => 'image/webp',
        ]);
    }
}
