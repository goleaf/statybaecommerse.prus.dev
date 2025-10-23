<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NormalSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NormalSetting>
 */
final class NormalSettingFactory extends Factory
{
    protected $model = NormalSetting::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([
            NormalSetting::TYPE_STRING,
            NormalSetting::TYPE_INTEGER,
            NormalSetting::TYPE_BOOLEAN,
            NormalSetting::TYPE_ARRAY,
            NormalSetting::TYPE_JSON,
        ]);

        return [
            'group'            => $this->faker->randomElement(['general', 'email', 'payment', 'shipping', 'system']),
            'key'              => $this->faker->unique()->slug(2),
            'locale'           => $this->faker->randomElement(['en', 'lt', 'de', 'fr', 'es']),
            'type'             => $type,
            'value'            => $this->fakeValueForType($type),
            'description'      => $this->faker->sentence(),
            'is_public'        => $this->faker->boolean(70),
            'is_encrypted'     => $this->faker->boolean(20),
            'validation_rules' => $this->faker->optional(0.3)->randomElements(['required', 'min:1', 'max:255'], 2),
            'sort_order'       => $this->faker->numberBetween(1, 100),
        ];
    }

    private function fakeValueForType(string $type)
    {
        return match ($type) {
            NormalSetting::TYPE_INTEGER => $this->faker->numberBetween(0, 1000),
            NormalSetting::TYPE_BOOLEAN => $this->faker->boolean(),
            NormalSetting::TYPE_ARRAY   => [
                'items'   => $this->faker->words(2),
                'enabled' => $this->faker->boolean(),
            ],
            NormalSetting::TYPE_JSON => [
                'meta' => [
                    'label'  => $this->faker->word(),
                    'active' => $this->faker->boolean(),
                ],
            ],
            default => $this->faker->sentence(),
        };
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function encrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }
}
