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
        $types = ['text','number','boolean','select','multiselect','color','date','textarea','file','image'];
        $groupNames = ['basic_info','technical_specs','appearance','dimensions','materials','features','compatibility','warranty','shipping','seo'];
        
        return [
            'name' => $label,
            'slug' => strtolower(str_replace(' ', '-', $label)),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->optional(0.7)->sentence(),
            'validation_rules' => $this->faker->optional(0.3)->randomElement([
                ['required' => true, 'max' => 255],
                ['min' => 1, 'max' => 100],
                ['required' => true],
            ]),
            'default_value' => $this->faker->optional(0.4)->randomElement(['red', 'blue', 'green', 'small', 'medium', 'large']),
            'is_required' => $this->faker->boolean(30),
            'is_filterable' => $this->faker->boolean(80),
            'is_searchable' => $this->faker->boolean(60),
            'is_visible' => $this->faker->boolean(90),
            'is_editable' => $this->faker->boolean(85),
            'is_sortable' => $this->faker->boolean(70),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_enabled' => $this->faker->boolean(95),
            'category_id' => null,
            'group_name' => $this->faker->optional(0.6)->randomElement($groupNames),
            'icon' => $this->faker->optional(0.4)->randomElement([
                'heroicon-o-adjustments-horizontal',
                'heroicon-o-color-swatch',
                'heroicon-o-cube',
                'heroicon-o-cog-6-tooth',
                'heroicon-o-tag',
            ]),
            'color' => $this->faker->optional(0.3)->hexColor(),
            'min_value' => $this->faker->optional(0.2)->randomFloat(2, 0, 10),
            'max_value' => $this->faker->optional(0.2)->randomFloat(2, 10, 100),
            'step_value' => $this->faker->optional(0.1)->randomFloat(2, 0.1, 1),
            'placeholder' => $this->faker->optional(0.5)->sentence(3),
            'help_text' => $this->faker->optional(0.3)->sentence(),
            'meta_data' => $this->faker->optional(0.2)->randomElement([
                ['unit' => 'cm', 'precision' => 2],
                ['unit' => 'kg', 'precision' => 1],
                ['format' => 'currency', 'currency' => 'EUR'],
                ['format' => 'percentage'],
            ]),
        ];
    }
}


