<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\Translations\PostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

final class PostTranslationFactory extends Factory
{
    protected $model = PostTranslation::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'title' => $this->faker->sentence(3),
            'slug' => $this->faker->slug(),
            'content' => $this->faker->paragraphs(3, true),
            'excerpt' => $this->faker->paragraph(),
            'meta_title' => $this->faker->sentence(2),
            'meta_description' => $this->faker->paragraph(),
            'tags' => $this->faker->words(3),
            'metadata' => [
                'reading_time' => $this->faker->numberBetween(1, 10),
                'word_count' => $this->faker->numberBetween(100, 2000),
                'seo_score' => $this->faker->numberBetween(60, 100),
            ],
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(3, true),
            'excerpt' => $this->faker->paragraph(),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(3, true),
            'excerpt' => $this->faker->paragraph(),
        ]);
    }
}
