<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NewsTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsTag>
 */
final class NewsTagFactory extends Factory
{
    protected $model = NewsTag::class;

    public function definition(): array
    {
        return [
            'is_visible' => $this->faker->boolean(80),  // 80% chance of being visible
            'color' => $this->faker->randomElement([
                '#3B82F6',  // Blue
                '#10B981',  // Green
                '#F59E0B',  // Yellow
                '#EF4444',  // Red
                '#8B5CF6',  // Purple
                '#EC4899',  // Pink
                '#06B6D4',  // Cyan
                '#84CC16',  // Lime
                '#F97316',  // Orange
                '#6366F1',  // Indigo
            ]),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#3B82F6',
        ]);
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#10B981',
        ]);
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#F59E0B',
        ]);
    }

    public function danger(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#EF4444',
        ]);
    }

    public function purple(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#8B5CF6',
        ]);
    }

    public function pink(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#EC4899',
        ]);
    }

    public function cyan(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#06B6D4',
        ]);
    }

    public function lime(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#84CC16',
        ]);
    }

    public function orange(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#F97316',
        ]);
    }

    public function indigo(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#6366F1',
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $this->faker->numberBetween(1, 10),
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $this->faker->numberBetween(90, 100),
        ]);
    }

    public function breaking(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#EF4444',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }

    public function exclusive(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#8B5CF6',
            'sort_order' => 2,
            'is_visible' => true,
        ]);
    }

    public function trending(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#F59E0B',
            'sort_order' => 3,
            'is_visible' => true,
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#10B981',
            'sort_order' => 4,
            'is_visible' => true,
        ]);
    }

    public function latest(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#3B82F6',
            'sort_order' => 5,
            'is_visible' => true,
        ]);
    }

    public function important(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#DC2626',
            'sort_order' => 6,
            'is_visible' => true,
        ]);
    }

    public function update(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#06B6D4',
            'sort_order' => 7,
            'is_visible' => true,
        ]);
    }

    public function announcement(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#8B5CF6',
            'sort_order' => 8,
            'is_visible' => true,
        ]);
    }

    public function event(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#EC4899',
            'sort_order' => 9,
            'is_visible' => true,
        ]);
    }

    public function news(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#6366F1',
            'sort_order' => 10,
            'is_visible' => true,
        ]);
    }

    public function report(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#059669',
            'sort_order' => 11,
            'is_visible' => true,
        ]);
    }

    public function analysis(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#7C3AED',
            'sort_order' => 12,
            'is_visible' => true,
        ]);
    }

    public function technology(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#0EA5E9',
            'sort_order' => 13,
            'is_visible' => true,
        ]);
    }

    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#059669',
            'sort_order' => 14,
            'is_visible' => true,
        ]);
    }

    public function sports(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#DC2626',
            'sort_order' => 15,
            'is_visible' => true,
        ]);
    }

    public function entertainment(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#EC4899',
            'sort_order' => 16,
            'is_visible' => true,
        ]);
    }

    public function health(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#10B981',
            'sort_order' => 17,
            'is_visible' => true,
        ]);
    }

    public function science(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#3B82F6',
            'sort_order' => 18,
            'is_visible' => true,
        ]);
    }

    public function politics(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#EF4444',
            'sort_order' => 19,
            'is_visible' => true,
        ]);
    }

    public function education(): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => '#8B5CF6',
            'sort_order' => 20,
            'is_visible' => true,
        ]);
    }
}
