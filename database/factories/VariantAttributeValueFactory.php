<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\VariantAttributeValue>
 */
class VariantAttributeValueFactory extends Factory
{
    protected $model = VariantAttributeValue::class;

    public function definition(): array
    {
        $baseValue = Str::title($this->faker->unique()->words(2, true));

        return [
            'variant_id' => fn () => ProductVariant::factory()->create()->id,
            'attribute_id' => fn () => Attribute::factory()->create()->id,
            'attribute_name' => $this->faker->randomElement(['Color', 'Size', 'Material', 'Style']),
            'attribute_value' => $baseValue,
            'attribute_value_display' => $this->faker->boolean(50) ? $baseValue.' Display' : null,
            'attribute_value_lt' => $this->faker->boolean(60) ? $baseValue.' LT' : null,
            'attribute_value_en' => $this->faker->boolean(60) ? $baseValue.' EN' : null,
            'attribute_value_slug' => Str::slug($baseValue),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_filterable' => $this->faker->boolean(70),
            'is_searchable' => $this->faker->boolean(80),
        ];
    }
}
