<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CampaignConversion;
use App\Models\CampaignConversionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignConversionTranslation>
 */
class CampaignConversionTranslationFactory extends Factory
{
    protected $model = CampaignConversionTranslation::class;

    public function definition(): array
    {
        return [
            'campaign_conversion_id' => CampaignConversion::factory(),
            'locale' => fake()->randomElement(['lt', 'en']),
            'notes' => fake()->optional()->sentence(),
            'custom_attributes' => [
                'translation_notes' => fake()->optional()->sentence(),
                'localized_tags' => fake()->optional()->words(3),
                'region_specific_data' => fake()->optional()->word(),
            ],
        ];
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'notes' => fake('lt_LT')->sentence(),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'notes' => fake()->sentence(),
        ]);
    }
}
