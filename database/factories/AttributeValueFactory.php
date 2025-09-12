<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValue>
 */
final class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    public function definition(): array
    {
        $value = $this->faker->unique()->safeColorName();

        return [
            'attribute_id' => fn () => \App\Models\Attribute::factory(),
            'value' => $value,
            'slug' => str($value)->slug()->toString(),
            'description' => $this->faker->optional(0.6)->sentence(),
            'color_code' => $this->faker->boolean(40) ? $this->faker->hexColor() : null,
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_enabled' => $this->faker->boolean(90),
            'is_required' => $this->faker->boolean(20),
            'is_default' => $this->faker->boolean(10),
            'meta_data' => $this->faker->optional(0.3)->randomElements([
                'created_by' => $this->faker->userName(),
                'version' => $this->faker->semver(),
                'tags' => $this->faker->words(3),
                'category' => $this->faker->word(),
            ]),
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function withColor(?string $colorCode = null): static
    {
        return $this->state(fn (array $attributes) => [
            'color_code' => $colorCode ?? $this->faker->hexColor(),
        ]);
    }

    public function withDescription(?string $description = null): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description ?? $this->faker->sentence(),
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
            'color_code' => $this->faker->hexColor(),
            'description' => 'Color option for products',
            'meta_data' => [
                'type' => 'color',
                'hex' => $this->faker->hexColor(),
                'rgb' => $this->faker->rgbColorAsArray(),
            ],
        ]);
    }

    public function sizeValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'description' => 'Size option for clothing',
            'meta_data' => [
                'type' => 'size',
                'measurement' => 'clothing',
                'order' => $this->faker->numberBetween(1, 10),
            ],
        ]);
    }

    public function materialValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather']),
            'description' => 'Material composition',
            'meta_data' => [
                'type' => 'material',
                'durability' => $this->faker->randomElement(['low', 'medium', 'high']),
                'care_instructions' => $this->faker->sentence(),
            ],
        ]);
    }
}
