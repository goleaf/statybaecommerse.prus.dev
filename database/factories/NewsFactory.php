<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ModerationState;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\News>
 */
final class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'is_visible'              => true,
            'is_featured'             => fake()->boolean(20),
            'is_breaking'             => fake()->boolean(10),
            'moderation_state'        => ModerationState::Published->value,
            'submitted_for_review_at' => now()->subDays(fake()->numberBetween(2, 5)),
            'approved_at'             => now()->subDay(),
            'approved_by_id'          => null,
            'published_at'            => now()->subDays(fake()->numberBetween(0, 30)),
            'author_name'             => fake()->name(),
        ];
    }
}
