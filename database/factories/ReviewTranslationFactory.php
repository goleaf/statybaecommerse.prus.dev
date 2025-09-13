<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Review;
use App\Models\Translations\ReviewTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ReviewTranslationFactory extends Factory
{
    protected $model = ReviewTranslation::class;

    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'title' => $this->faker->sentence(3),
            'comment' => $this->faker->paragraph(3),
            'metadata' => [
                'helpful_count' => $this->faker->numberBetween(0, 50),
                'not_helpful_count' => $this->faker->numberBetween(0, 10),
                'verified_purchase' => $this->faker->boolean(70),
            ],
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'title' => $this->faker->sentence(3),
            'comment' => $this->faker->paragraph(3),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'title' => $this->faker->sentence(3),
            'comment' => $this->faker->paragraph(3),
        ]);
    }
}
