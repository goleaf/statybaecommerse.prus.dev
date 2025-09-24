<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsImage>
 */
final class NewsImageFactory extends Factory
{
    protected $model = NewsImage::class;

    public function definition(): array
    {
        return [
            'news_id' => News::factory(),
            'file_path' => 'news-images/'.$this->faker->uuid().'.jpg',
            'alt_text' => $this->faker->sentence(6),
            'caption' => $this->faker->sentence(10),
            'is_featured' => $this->faker->boolean(30),  // 30% chance of being featured
            'sort_order' => $this->faker->numberBetween(0, 100),
            'file_size' => $this->faker->numberBetween(100000, 5000000),  // 100KB to 5MB
            'mime_type' => $this->faker->randomElement([
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
            ]),
            'dimensions' => [
                'width' => $this->faker->numberBetween(400, 2000),
                'height' => $this->faker->numberBetween(300, 1500),
            ],
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function notFeatured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => false,
        ]);
    }

    public function withAltText(): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => $this->faker->sentence(6),
        ]);
    }

    public function withoutAltText(): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => null,
        ]);
    }

    public function withCaption(): static
    {
        return $this->state(fn (array $attributes) => [
            'caption' => $this->faker->sentence(10),
        ]);
    }

    public function withoutCaption(): static
    {
        return $this->state(fn (array $attributes) => [
            'caption' => null,
        ]);
    }

    public function largeFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(2000000, 10000000),  // 2MB to 10MB
        ]);
    }

    public function smallFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(10000, 500000),  // 10KB to 500KB
        ]);
    }

    public function jpeg(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/jpeg',
            'file_path' => 'news-images/'.$this->faker->uuid().'.jpg',
        ]);
    }

    public function png(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/png',
            'file_path' => 'news-images/'.$this->faker->uuid().'.png',
        ]);
    }

    public function gif(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/gif',
            'file_path' => 'news-images/'.$this->faker->uuid().'.gif',
        ]);
    }

    public function webp(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => 'image/webp',
            'file_path' => 'news-images/'.$this->faker->uuid().'.webp',
        ]);
    }

    public function highResolution(): static
    {
        return $this->state(fn (array $attributes) => [
            'dimensions' => [
                'width' => $this->faker->numberBetween(1920, 4000),
                'height' => $this->faker->numberBetween(1080, 3000),
            ],
        ]);
    }

    public function lowResolution(): static
    {
        return $this->state(fn (array $attributes) => [
            'dimensions' => [
                'width' => $this->faker->numberBetween(100, 400),
                'height' => $this->faker->numberBetween(100, 300),
            ],
        ]);
    }

    public function square(): static
    {
        $size = $this->faker->numberBetween(200, 1000);

        return $this->state(fn (array $attributes) => [
            'dimensions' => [
                'width' => $size,
                'height' => $size,
            ],
        ]);
    }

    public function landscape(): static
    {
        return $this->state(fn (array $attributes) => [
            'dimensions' => [
                'width' => $this->faker->numberBetween(800, 2000),
                'height' => $this->faker->numberBetween(400, 1000),
            ],
        ]);
    }

    public function portrait(): static
    {
        return $this->state(fn (array $attributes) => [
            'dimensions' => [
                'width' => $this->faker->numberBetween(400, 1000),
                'height' => $this->faker->numberBetween(800, 2000),
            ],
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }

    public function withSortOrder(int $sortOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $sortOrder,
        ]);
    }

    public function forNews(News $news): static
    {
        return $this->state(fn (array $attributes) => [
            'news_id' => $news->id,
        ]);
    }
}
