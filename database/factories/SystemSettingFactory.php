<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    public function definition(): array
    {
        $types = ['string', 'text', 'number', 'boolean', 'array', 'json', 'file', 'image', 'select', 'color', 'date', 'datetime'];
        $groups = ['general', 'ecommerce', 'email', 'payment', 'shipping', 'seo', 'security', 'api', 'appearance', 'notifications'];

        return [
            'category_id' => SystemSettingCategory::factory(),
            'key' => $this->faker->unique()->slug(2),
            'name' => $this->faker->sentence(3),
            'value' => $this->faker->sentence(),
            'type' => $this->faker->randomElement($types),
            'group' => $this->faker->randomElement($groups),
            'description' => $this->faker->paragraph(),
            'help_text' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(30), // 30% chance of being public
            'is_required' => $this->faker->boolean(20), // 20% chance of being required
            'is_encrypted' => $this->faker->boolean(10), // 10% chance of being encrypted
            'is_readonly' => $this->faker->boolean(15), // 15% chance of being readonly
            'validation_rules' => $this->faker->boolean(40) ? json_encode(['min' => 1, 'max' => 255]) : null,
            'options' => $this->faker->boolean(30) ? json_encode(['option1' => 'Value 1', 'option2' => 'Value 2']) : null,
            'default_value' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'updated_by' => User::factory(),
            'placeholder' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'tooltip' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'metadata' => $this->faker->boolean(20) ? json_encode(['custom_field' => $this->faker->word()]) : null,
            'validation_message' => $this->faker->boolean(25) ? $this->faker->sentence() : null,
            'is_cacheable' => $this->faker->boolean(80), // 80% chance of being cacheable
            'cache_ttl' => $this->faker->numberBetween(300, 86400), // 5 minutes to 24 hours
            'environment' => $this->faker->randomElement(['all', 'production', 'staging', 'development']),
            'tags' => $this->faker->boolean(50) ? implode(',', $this->faker->words(3)) : null,
            'version' => $this->faker->numerify('#.#.#'),
            'access_count' => $this->faker->numberBetween(0, 1000),
            'last_accessed_at' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('-30 days') : null,
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

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function readonly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_readonly' => true,
        ]);
    }

    public function encrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    public function string(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'string',
            'value' => $this->faker->sentence(),
        ]);
    }

    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'boolean',
            'value' => $this->faker->boolean(),
        ]);
    }

    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'number',
            'value' => $this->faker->numberBetween(1, 1000),
        ]);
    }

    public function array(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'array',
            'value' => $this->faker->words(5),
        ]);
    }

    public function json(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'json',
            'value' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);
    }

    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'select',
            'value' => 'option1',
            'options' => ['option1' => 'Option 1', 'option2' => 'Option 2', 'option3' => 'Option 3'],
        ]);
    }

    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'color',
            'value' => $this->faker->hexColor(),
        ]);
    }

    public function group(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }

    public function category(SystemSettingCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
