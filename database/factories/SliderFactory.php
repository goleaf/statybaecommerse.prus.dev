<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Slider>
 */
class SliderFactory extends Factory
{
    protected $model = Slider::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'button_text' => fake()->words(2, true),
            'button_url' => fake()->url(),
            'background_color' => fake()->hexColor(),
            'text_color' => fake()->hexColor(),
            'sort_order' => fake()->unique()->numberBetween(1, 1000),
            'is_active' => fake()->boolean(80),
            'settings' => [
                'animation' => fake()->randomElement(['fade', 'slide', 'zoom']),
                'duration' => fake()->numberBetween(3000, 8000),
                'autoplay' => fake()->boolean(),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withButton(): static
    {
        return $this->state(fn (array $attributes) => [
            'button_text' => fake()->words(2, true),
            'button_url' => fake()->url(),
        ]);
    }

    public function withoutButton(): static
    {
        return $this->state(fn (array $attributes) => [
            'button_text' => null,
            'button_url' => null,
        ]);
    }
}
