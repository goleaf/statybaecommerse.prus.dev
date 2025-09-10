<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    protected $model = \App\Models\Attribute::class;

    public function definition(): array
    {
        $label = $this->faker->randomElement(['Color','Size','Material','Fit','Length','Style']) . ' ' . $this->faker->unique()->numerify('###');
        $types = ['boolean','color','date','select','multiselect','text','number'];
        return [
            'name' => $label,
            'slug' => strtolower(str_replace(' ', '_', $label)),
            'type' => $this->faker->randomElement($types),
            'is_filterable' => true,
            'is_searchable' => true,
            'is_enabled' => true,
            'sort_order' => 0,
            'options' => null,
        ];
    }
}


