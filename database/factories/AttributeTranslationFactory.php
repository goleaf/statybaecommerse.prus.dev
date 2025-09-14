<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\AttributeTranslation>
 */
final class AttributeTranslationFactory extends Factory
{
    protected $model = AttributeTranslation::class;

    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'placeholder' => $this->faker->sentence(),
            'help_text' => $this->faker->paragraph(),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'placeholder' => $this->faker->sentence(),
            'help_text' => $this->faker->paragraph(),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'placeholder' => $this->faker->sentence(),
            'help_text' => $this->faker->paragraph(),
        ]);
    }

    public function withPlaceholder(): static
    {
        return $this->state(fn (array $attributes) => [
            'placeholder' => $this->faker->sentence(),
        ]);
    }

    public function withHelpText(): static
    {
        return $this->state(fn (array $attributes) => [
            'help_text' => $this->faker->paragraph(),
        ]);
    }
}
