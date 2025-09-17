<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NormalSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NormalSetting>
 */
final class EnhancedSettingFactory extends Factory
{
    protected $model = NormalSetting::class;

    public function definition(): array
    {
        $groups = ['general', 'ecommerce', 'email', 'payment', 'shipping', 'seo', 'security', 'api', 'appearance', 'notifications'];
        $types = ['text', 'textarea', 'number', 'boolean', 'json', 'array', 'select', 'file', 'color', 'date', 'datetime'];

        $type = $this->faker->randomElement($types);

        return [
            'group' => $this->faker->randomElement($groups),
            'key' => $this->faker->unique()->slug(2).'_'.$this->faker->word(),
            'value' => $this->generateValueByType($type),
            'type' => $type,
            'description' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(70),  // 70% chance of being public
            'is_encrypted' => $this->faker->boolean(20),  // 20% chance of being encrypted
            'validation_rules' => $this->faker->boolean(30) ? $this->generateValidationRules() : null,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Generate a value based on the type
     */
    private function generateValueByType(string $type): mixed
    {
        return match ($type) {
            'text' => $this->faker->words(3, true),
            'textarea' => $this->faker->paragraph(),
            'number' => $this->faker->numberBetween(1, 1000),
            'boolean' => $this->faker->boolean(),
            'json', 'array' => json_encode([
                'setting1' => $this->faker->word(),
                'setting2' => $this->faker->numberBetween(1, 100),
                'setting3' => $this->faker->boolean(),
            ]),
            'select' => $this->faker->randomElement(['option1', 'option2', 'option3']),
            'file' => $this->faker->filePath(),
            'color' => $this->faker->hexColor(),
            'date' => $this->faker->date(),
            'datetime' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            default => $this->faker->word(),
        };
    }

    /**
     * Generate validation rules array
     */
    private function generateValidationRules(): array
    {
        $rules = ['required', 'string', 'nullable', 'numeric', 'boolean', 'email', 'url', 'date'];
        $maxRules = ['max:255', 'max:1000', 'min:1', 'min:10'];

        $selectedRules = $this->faker->randomElements($rules, $this->faker->numberBetween(1, 3));

        if ($this->faker->boolean(50)) {
            $selectedRules[] = $this->faker->randomElement($maxRules);
        }

        return $selectedRules;
    }

    /**
     * Create a setting for general group
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'general',
        ]);
    }

    /**
     * Create a setting for ecommerce group
     */
    public function ecommerce(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'ecommerce',
        ]);
    }

    /**
     * Create a public setting
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Create a private setting
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Create an encrypted setting
     */
    public function encrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    /**
     * Create a text type setting
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'text',
            'value' => $this->faker->words(3, true),
        ]);
    }

    /**
     * Create a boolean type setting
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'boolean',
            'value' => $this->faker->boolean(),
        ]);
    }

    /**
     * Create a number type setting
     */
    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'number',
            'value' => $this->faker->numberBetween(1, 1000),
        ]);
    }

    /**
     * Create a JSON type setting
     */
    public function json(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'json',
            'value' => json_encode([
                'setting1' => $this->faker->word(),
                'setting2' => $this->faker->numberBetween(1, 100),
                'setting3' => $this->faker->boolean(),
                'nested' => [
                    'key' => $this->faker->word(),
                    'value' => $this->faker->sentence(),
                ],
            ]),
        ]);
    }

    /**
     * Create a color type setting
     */
    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'color',
            'value' => $this->faker->hexColor(),
        ]);
    }

    /**
     * Create a setting with validation rules
     */
    public function withValidation(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_rules' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * Create a setting with specific key
     */
    public function withKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }

    /**
     * Create a setting with specific value
     */
    public function withValue(mixed $value): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $value,
        ]);
    }

    /**
     * Create a setting with specific sort order
     */
    public function withSortOrder(int $sortOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Create multilingual settings
     */
    public function multilingual(string $baseKey): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $baseKey.'_'.$this->faker->randomElement(['lt', 'en']),
            'description' => $this->faker->sentence().' (multilingual)',
        ]);
    }
}
