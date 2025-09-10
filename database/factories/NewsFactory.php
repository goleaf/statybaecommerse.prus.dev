<?php declare(strict_types=1);

namespace Database\Factories;

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
            'is_visible' => true,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'author_name' => fake()->name(),
        ];
    }
}
