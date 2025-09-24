<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

final class NewsCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'news_id' => 1,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'content' => fake()->sentence(12),
            'is_approved' => true,
            'is_visible' => true,
            'is_active' => true,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

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
            'author_name' => fake()->name(),
            'author_email' => fake()->email(),
            'content' => fake()->paragraph(),
            'is_approved' => fake()->boolean(80), // 80% chance of being approved
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

    public function reply(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => fake()->numberBetween(1, 100), // This should be set properly in tests
        ]);
    }
}
