<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NewsTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\NewsTag>
 */
final class NewsTagFactory extends Factory
{
    protected $model = NewsTag::class;

    public function definition(): array
    {
        return [
            'is_visible' => true,
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
