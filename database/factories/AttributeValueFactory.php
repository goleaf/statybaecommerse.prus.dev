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
            'color_code' => $this->faker->boolean(40) ? $this->faker->hexColor() : null,
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_enabled' => true,
            'is_active' => true,
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

    public function withColor(?string $colorCode = null): static
    {
        return $this->state(fn (array $attributes) => [
            'color_code' => $colorCode ?? $this->faker->hexColor(),
        ]);
    }

    public function colorValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->colorName(),
            'color_code' => $this->faker->hexColor(),
        ]);
    }

    public function sizeValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
        ]);
    }

    public function materialValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather']),
        ]);
    }
}
