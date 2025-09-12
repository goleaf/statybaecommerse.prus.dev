<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Referral;
use App\Models\Translations\ReferralTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\ReferralTranslation>
 */
final class ReferralTranslationFactory extends Factory
{
    protected $model = ReferralTranslation::class;

    public function definition(): array
    {
        return [
            'referral_id' => Referral::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'terms_conditions' => $this->faker->paragraphs(3, true),
            'benefits_description' => $this->faker->paragraph(2),
            'how_it_works' => $this->faker->paragraphs(2, true),
            'seo_title' => $this->faker->sentence(4),
            'seo_description' => $this->faker->paragraph(1),
            'seo_keywords' => $this->faker->words(5),
        ];
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'en',
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'terms_conditions' => $this->faker->paragraphs(3, true),
            'benefits_description' => $this->faker->paragraph(2),
            'how_it_works' => $this->faker->paragraphs(2, true),
            'seo_title' => $this->faker->sentence(4),
            'seo_description' => $this->faker->paragraph(1),
            'seo_keywords' => $this->faker->words(5),
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'lt',
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'terms_conditions' => $this->faker->paragraphs(3, true),
            'benefits_description' => $this->faker->paragraph(2),
            'how_it_works' => $this->faker->paragraphs(2, true),
            'seo_title' => $this->faker->sentence(4),
            'seo_description' => $this->faker->paragraph(1),
            'seo_keywords' => $this->faker->words(5),
        ]);
    }
}
