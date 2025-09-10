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
            'slug' => str($value)->slug()->toString(),
            'color_code' => $this->faker->boolean(40) ? $this->faker->hexColor() : null,
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_enabled' => true,
        ];
    }
}
