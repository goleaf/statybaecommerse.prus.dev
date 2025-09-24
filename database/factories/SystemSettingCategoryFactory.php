<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSettingCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemSettingCategory>
 */
final class SystemSettingCategoryFactory extends Factory
{
    protected $model = SystemSettingCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => str($name)->slug(),
            'description' => $this->faker->paragraph(),
            'icon' => $this->faker->randomElement([
                'heroicon-o-cog-6-tooth',
                'heroicon-o-shield-check',
                'heroicon-o-bolt',
                'heroicon-o-paint-brush',
                'heroicon-o-globe-alt',
                'heroicon-o-database',
                'heroicon-o-key',
                'heroicon-o-chart-bar',
            ]),
            'color' => $this->faker->randomElement([
                'primary',
                'secondary',
                'success',
                'warning',
                'danger',
                'info',
                'gray',
            ]),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90),
            'parent_id' => null,
        ];
    }

    /**
     * Indicate that the category is active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the category is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a category with a specific color
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    /**
     * Create a category with a specific icon
     */
    public function withIcon(string $icon): static
    {
        return $this->state(fn (array $attributes) => [
            'icon' => $icon,
        ]);
    }

    /**
     * Create a subcategory with a parent
     */
    public function childOf(SystemSettingCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Create a root category (no parent)
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }
}
