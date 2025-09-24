<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NormalSetting;
use App\Models\NormalSettingTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NormalSettingTranslation>
 */
final class NormalSettingTranslationFactory extends Factory
{
    protected $model = NormalSettingTranslation::class;

    public function definition(): array
    {
        return [
            'enhanced_setting_id' => NormalSetting::factory(),
            'locale' => $this->faker->randomElement(['en', 'lt', 'de', 'fr', 'es']),
            'display_name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'help_text' => $this->faker->optional(0.7)->sentence(),
        ];
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }

    public function forSetting(NormalSetting $setting): static
    {
        return $this->state(fn (array $attributes) => [
            'enhanced_setting_id' => $setting->id,
        ]);
    }

    public function withHelpText(): static
    {
        return $this->state(fn (array $attributes) => [
            'help_text' => $this->faker->sentence(),
        ]);
    }

    public function withoutHelpText(): static
    {
        return $this->state(fn (array $attributes) => [
            'help_text' => null,
        ]);
    }
}
