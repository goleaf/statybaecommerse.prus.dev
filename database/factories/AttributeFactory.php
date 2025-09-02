<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    protected $model = \Shop\Core\Models\Attribute::class;

    public function definition(): array
    {
        $label = $this->faker->unique()->randomElement(['Color','Size','Material','Fit','Length','Style']);
        $types = ['checkbox','color','date','richtext','select','text','number'];
        return [
            'name' => $label,
            'code' => strtolower(str_replace(' ', '_', $label)),
            'type' => $this->faker->randomElement($types),
            'is_filterable' => true,
            'is_searchable' => true,
            'is_enabled' => true,
            'metadata' => null,
        ];
    }
}


