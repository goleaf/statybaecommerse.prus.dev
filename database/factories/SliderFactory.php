<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Slider>
 */
class SliderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'button_text' => fake()->randomElement(['Learn More', 'Get Started', 'Shop Now', 'Discover', 'Explore']),
            'button_url' => fake()->url(),
            'background_color' => fake()->hexColor(),
            'text_color' => fake()->randomElement(['#000000', '#ffffff', '#333333']),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'settings' => [
                'animation_duration' => fake()->numberBetween(500, 2000),
                'show_indicators' => fake()->boolean(),
                'show_arrows' => fake()->boolean(),
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
}
