<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
final class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
            'user_id' => User::factory(),
            'is_approved' => true,
            'is_pinned' => false,
            'likes_count' => $this->faker->numberBetween(0, 100),
            'metadata' => [
                'edited' => $this->faker->boolean(20),
                'source' => $this->faker->randomElement(['web', 'mobile', 'api']),
            ],
        ];
    }

    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }
}