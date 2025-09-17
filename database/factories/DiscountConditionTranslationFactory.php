<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DiscountCondition;
use App\Models\Translations\DiscountConditionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\DiscountConditionTranslation>
 */
final class DiscountConditionTranslationFactory extends Factory
{
    protected $model = DiscountConditionTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'discount_condition_id' => DiscountCondition::factory(),
            'locale' => $this->faker->randomElement(['lt', 'en']),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'metadata' => $this->faker->optional(0.3)->randomElements([
                'category' => 'electronics',
                'brand' => 'test_brand',
                'season' => 'winter',
            ]),
        ];
    }

    /**
     * Create Lithuanian translation
     */
    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'name' => 'Nuolaidos sąlyga',
            'description' => 'Ši sąlyga nustato nuolaidos taikymo kriterijus.',
        ]);
    }

    /**
     * Create English translation
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'name' => 'Discount Condition',
            'description' => 'This condition sets the criteria for applying the discount.',
        ]);
    }
}

