<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
final class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'key' => fake()->unique()->slug(),
            'location' => fake()->randomElement(['header', 'footer', 'sidebar', 'mobile']),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the menu is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the menu is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a header menu.
     */
    public function header(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => 'header',
        ]);
    }

    /**
     * Create a footer menu.
     */
    public function footer(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => 'footer',
        ]);
    }

    /**
     * Create a sidebar menu.
     */
    public function sidebar(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => 'sidebar',
        ]);
    }

    /**
     * Create a mobile menu.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => 'mobile',
        ]);
    }
}
