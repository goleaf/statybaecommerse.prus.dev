<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NormalSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NormalSetting>
 */
final class NormalSettingFactory extends Factory
{
    protected $model = NormalSetting::class;

    public function definition(): array
    {
        return [
            'group' => $this->faker->randomElement(['general', 'email', 'payment', 'shipping', 'system']),
            'key' => $this->faker->unique()->slug(2),
            'locale' => $this->faker->randomElement(['en', 'lt', 'de', 'fr', 'es']),
            'value' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['text', 'number', 'boolean', 'json', 'array']),
            'description' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(70),
            'is_encrypted' => $this->faker->boolean(20),
            'validation_rules' => $this->faker->optional(0.3)->randomElements(['required', 'min:1', 'max:255'], 2),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
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

    public function encrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    public function forGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
