<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSetting;
use App\Models\SystemSettingTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemSettingTranslation>
 */
final class SystemSettingTranslationFactory extends Factory
{
    protected $model = SystemSettingTranslation::class;

    public function definition(): array
    {
        return [
            'system_setting_id' => SystemSetting::factory(),
            'locale' => $this->faker->unique()->randomElement(['en', 'lt', 'de', 'fr', 'es', 'pl', 'ru']),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'help_text' => $this->faker->paragraph(1),
            'is_active' => $this->faker->boolean(80),
            'is_public' => $this->faker->boolean(30),
            'metadata' => [
                'created_by' => 'admin',
                'version' => '1.0',
                'last_updated' => now()->toISOString(),
            ],
            'tags' => $this->faker->words(3),
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

    public function german(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'de',
        ]);
    }

    public function french(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'fr',
        ]);
    }

    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'es',
        ]);
    }

    public function polish(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'pl',
        ]);
    }

    public function russian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'ru',
        ]);
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

    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], $metadata),
        ]);
    }

    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }
}
