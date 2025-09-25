<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\NewsComment>
 */
final class NewsCommentFactory extends Factory
{
    protected $model = NewsComment::class;

    public function definition(): array
    {
        return [
            'news_id' => News::factory(),
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'content' => fake()->paragraph(),
            'is_approved' => true,
            'is_visible' => true,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    public function reply(NewsComment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'news_id' => $parent->news_id,
        ]);
    }
}
