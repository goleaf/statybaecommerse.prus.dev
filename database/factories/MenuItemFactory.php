<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
final class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'label' => fake()->words(2, true),
            'url' => fake()->url(),
            'route_name' => null,
            'route_params' => null,
            'icon' => fake()->randomElement([
                'heroicon-o-home',
                'heroicon-o-user',
                'heroicon-o-cog',
                'heroicon-o-information-circle',
                'heroicon-o-phone',
                'heroicon-o-envelope',
                null,
            ]),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_visible' => fake()->boolean(90), // 90% chance of being visible
        ];
    }

    /**
     * Indicate that the menu item is visible.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => true,
        ]);
    }

    /**
     * Indicate that the menu item is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    /**
     * Create a menu item with a route instead of URL.
     */
    public function withRoute(string $routeName, array $params = []): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => null,
            'route_name' => $routeName,
            'route_params' => $params,
        ]);
    }

    /**
     * Create a child menu item.
     */
    public function child(MenuItem $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'menu_id' => $parent->menu_id,
        ]);
    }

    /**
     * Create a menu item with external link.
     */
    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => fake()->url(),
            'route_name' => null,
            'route_params' => null,
        ]);
    }
}
