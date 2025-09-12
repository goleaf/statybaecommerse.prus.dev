<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AttributeValue;
use App\Models\Translations\AttributeValueTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\AttributeValueTranslation>
 */
final class AttributeValueTranslationFactory extends Factory
{
    protected $model = AttributeValueTranslation::class;

    public function definition(): array
    {
        return [
            'attribute_value_id' => AttributeValue::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt', 'de']),
            'value' => $this->faker->word(),
            'description' => $this->faker->optional(0.7)->sentence(),
            'meta_data' => $this->faker->optional(0.3)->randomElements([
                'hex' => $this->faker->hexColor(),
                'rgb' => $this->faker->rgbColorAsArray(),
                'created_by' => $this->faker->userName(),
                'version' => $this->faker->semver(),
                'tags' => $this->faker->words(3),
            ]),
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

    public function withValue(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $value,
        ]);
    }

    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }

    public function withMetaData(array $metaData): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_data' => $metaData,
        ]);
    }

    public function colorValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->colorName(),
            'meta_data' => [
                'hex' => $this->faker->hexColor(),
                'rgb' => $this->faker->rgbColorAsArray(),
                'hsl' => $this->faker->rgbColorAsArray(),
            ],
        ]);
    }

    public function sizeValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'meta_data' => [
                'measurement' => 'clothing',
                'order' => $this->faker->numberBetween(1, 10),
            ],
        ]);
    }

    public function materialValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather']),
            'description' => $this->faker->sentence(),
            'meta_data' => [
                'durability' => $this->faker->randomElement(['low', 'medium', 'high']),
                'care_instructions' => $this->faker->sentence(),
            ],
        ]);
    }
}
