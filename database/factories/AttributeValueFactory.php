<?php declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeValueFactory extends Factory
{
    protected $model = \App\Models\AttributeValue::class;

    public function definition(): array
    {
        $value = $this->faker->unique()->safeColorName();
        return [
            'attribute_id' => fn() => \App\Models\Attribute::factory(),
            'value' => $value,
            'key' => $this->faker->boolean(50) ? $this->faker->hexColor() : null,
            'position' => $this->faker->numberBetween(0, 50),
            'metadata' => null,
        ];
    }
}
