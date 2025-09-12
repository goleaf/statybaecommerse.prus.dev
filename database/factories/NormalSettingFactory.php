<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\NormalSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NormalSetting>
 */
final class NormalSettingFactory extends Factory
{
    protected $model = NormalSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['text', 'number', 'boolean', 'json', 'array']);
        $value = match($type) {
            'json', 'array' => json_encode(['key1' => 'value1', 'key2' => 'value2']),
            'number' => (string) $this->faker->numberBetween(1, 1000),
            'boolean' => $this->faker->boolean() ? '1' : '0',
            default => $this->faker->sentence(),
        };

        return [
            'group' => $this->faker->randomElement(['general', 'admin', 'frontend', 'api', 'email']),
            'key' => $this->faker->unique()->slug(2),
            'locale' => $this->faker->randomElement(['en', 'lt']),
            'value' => $value,
            'type' => $type,
            'description' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(70),
            'is_encrypted' => $this->faker->boolean(20),
            'validation_rules' => json_encode(['required' => true]),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the setting is public.
     */
    public function public(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the setting is private.
     */
    public function private(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the setting is encrypted.
     */
    public function encrypted(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    /**
     * Indicate that the setting is not encrypted.
     */
    public function notEncrypted(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_encrypted' => false,
        ]);
    }

    /**
     * Create a setting with a specific group.
     */
    public function group(string $group): static
    {
        return $this->state(fn(array $attributes) => [
            'group' => $group,
        ]);
    }

    /**
     * Create a setting with a specific key.
     */
    public function key(string $key): static
    {
        return $this->state(fn(array $attributes) => [
            'key' => $key,
        ]);
    }

    /**
     * Create a setting with a specific value.
     */
    public function value(string $value): static
    {
        return $this->state(fn(array $attributes) => [
            'value' => $value,
        ]);
    }

    /**
     * Create a text type setting.
     */
    public function text(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'text',
            'value' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create a number type setting.
     */
    public function number(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'number',
            'value' => (string) $this->faker->numberBetween(1, 1000),
        ]);
    }

    /**
     * Create a boolean type setting.
     */
    public function boolean(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'boolean',
            'value' => $this->faker->boolean() ? '1' : '0',
        ]);
    }

    /**
     * Create a JSON type setting.
     */
    public function json(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'json',
            'value' => json_encode([
                'option1' => $this->faker->word(),
                'option2' => $this->faker->numberBetween(1, 100),
                'option3' => $this->faker->boolean(),
            ]),
        ]);
    }
}
