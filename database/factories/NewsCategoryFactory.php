<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsCategory>
 */
final class NewsCategoryFactory extends Factory
{
    protected $model = NewsCategory::class;

    public function definition(): array
    {
        return [
            'is_visible' => $this->faker->boolean(80),
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'color' => $this->faker->hexColor(),
            'icon' => $this->faker->randomElement([
                'heroicon-o-rectangle-stack',
                'heroicon-o-document-text',
                'heroicon-o-newspaper',
                'heroicon-o-tag',
                'heroicon-o-folder',
            ]),
        ];
    }

    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    public function withParent(NewsCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    public function ordered(int $sortOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $sortOrder,
        ]);
    }
}
