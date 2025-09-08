<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        $types = ['string', 'boolean', 'number', 'json', 'text', 'email', 'url', 'color', 'date', 'datetime'];
        $groups = ['general', 'email', 'payment', 'shipping', 'seo', 'social', 'appearance', 'security'];

        return [
            'key' => $this->faker->unique()->slug(2),
            'display_name' => $this->faker->words(3, true),
            'value' => $this->faker->sentence(),
            'type' => $this->faker->randomElement($types),
            'group' => $this->faker->randomElement($groups),
            'description' => $this->faker->optional()->sentence(),
            'is_public' => $this->faker->boolean(30),  // 30% chance of being public
            'is_required' => $this->faker->boolean(20),  // 20% chance of being required
            'is_encrypted' => $this->faker->boolean(10),  // 10% chance of being encrypted
        ];
    }

    public function public(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function encrypted(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => $type,
            'value' => match ($type) {
                'boolean' => $this->faker->boolean(),
                'number' => $this->faker->numberBetween(1, 1000),
                'json' => json_encode(['key' => $this->faker->word(), 'value' => $this->faker->word()]),
                'email' => $this->faker->email(),
                'url' => $this->faker->url(),
                'color' => $this->faker->hexColor(),
                'date' => $this->faker->date(),
                'datetime' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
                default => $this->faker->sentence(),
            },
        ]);
    }

    public function inGroup(string $group): static
    {
        return $this->state(fn(array $attributes) => [
            'group' => $group,
        ]);
    }
}
