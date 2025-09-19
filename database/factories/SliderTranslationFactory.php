<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SliderTranslation>
 */
class SliderTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slider_id' => \App\Models\Slider::factory(),
            'locale' => fake()->randomElement(['en', 'lt']),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'button_text' => fake()->randomElement(['Learn More', 'Get Started', 'Shop Now', 'Discover', 'Explore']),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
        ]);
    }
}
