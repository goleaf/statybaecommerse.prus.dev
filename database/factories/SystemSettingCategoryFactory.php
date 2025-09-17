<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSettingCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SystemSettingCategoryFactory extends Factory
{
    protected $model = SystemSettingCategory::class;

    public function definition(): array
    {
        $colors = ['primary', 'secondary', 'success', 'warning', 'danger', 'info'];
        $icons = [
            'heroicon-o-cog-6-tooth',
            'heroicon-o-shield-check',
            'heroicon-o-envelope',
            'heroicon-o-credit-card',
            'heroicon-o-truck',
            'heroicon-o-globe-alt',
            'heroicon-o-lock-closed',
            'heroicon-o-code-bracket',
            'heroicon-o-paint-brush',
            'heroicon-o-bell',
        ];

        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug(2),
            'description' => $this->faker->paragraph(),
            'icon' => $this->faker->randomElement($icons),
            'color' => $this->faker->randomElement($colors),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'parent_id' => null,
            'template' => $this->faker->boolean(30) ? $this->faker->word() : null,
            'metadata' => $this->faker->boolean(20) ? ['custom_field' => $this->faker->word()] : null,
            'is_collapsible' => $this->faker->boolean(80), // 80% chance of being collapsible
            'show_in_sidebar' => $this->faker->boolean(90), // 90% chance of showing in sidebar
            'permission' => $this->faker->boolean(40) ? $this->faker->word() : null,
            'tags' => $this->faker->boolean(50) ? $this->faker->words(3) : null,
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

    public function withParent(SystemSettingCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    public function collapsible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_collapsible' => true,
        ]);
    }

    public function notCollapsible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_collapsible' => false,
        ]);
    }

    public function showInSidebar(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_sidebar' => true,
        ]);
    }

    public function hideFromSidebar(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_in_sidebar' => false,
        ]);
    }

    public function withPermission(string $permission): static
    {
        return $this->state(fn (array $attributes) => [
            'permission' => $permission,
        ]);
    }

    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    public function withIcon(string $icon): static
    {
        return $this->state(fn (array $attributes) => [
            'icon' => $icon,
        ]);
    }
}
