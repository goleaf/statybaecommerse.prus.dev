<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
final class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'title_translations' => [
                'lt' => $title,
                'en' => fake()->sentence(4),
            ],
            'slug' => \Illuminate\Support\Str::slug($title),
            'content' => fake()->paragraphs(5, true),
            'content_translations' => [
                'lt' => fake()->paragraphs(5, true),
                'en' => fake()->paragraphs(5, true),
            ],
            'excerpt' => fake()->sentence(10),
            'excerpt_translations' => [
                'lt' => fake()->sentence(10),
                'en' => fake()->sentence(10),
            ],
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'published_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'user_id' => User::factory(),
            'meta_title' => fake()->sentence(3),
            'meta_title_translations' => [
                'lt' => fake()->sentence(3),
                'en' => fake()->sentence(3),
            ],
            'meta_description' => fake()->sentence(15),
            'meta_description_translations' => [
                'lt' => fake()->sentence(15),
                'en' => fake()->sentence(15),
            ],
            'featured' => fake()->boolean(20),
            'tags' => fake()->words(3, true),
            'tags_translations' => [
                'lt' => fake()->words(3, true),
                'en' => fake()->words(3, true),
            ],
            'views_count' => fake()->numberBetween(0, 1000),
            'likes_count' => fake()->numberBetween(0, 100),
            'comments_count' => fake()->numberBetween(0, 50),
            'allow_comments' => fake()->boolean(80),
            'is_pinned' => fake()->boolean(5),
        ];
    }

    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'featured' => true,
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_pinned' => true,
        ]);
    }
}
