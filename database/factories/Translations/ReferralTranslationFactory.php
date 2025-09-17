<?php

declare(strict_types=1);

namespace Database\Factories\Translations;

use App\Models\Referral;
use App\Models\Translations\ReferralTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translations\ReferralTranslation>
 */
class ReferralTranslationFactory extends Factory
{
    protected $model = ReferralTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referral_id' => Referral::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt', 'ru']),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
        ];
    }
}