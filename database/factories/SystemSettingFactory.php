<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemSetting>
 */
final class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['string', 'integer', 'boolean', 'json', 'array', 'file', 'color', 'date', 'datetime', 'email', 'url', 'password'];
        $type = $this->faker->randomElement($types);

        return [
            'category_id' => SystemSettingCategory::factory(),
            'key' => $this->faker->unique()->slug(2),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'help_text' => $this->faker->sentence(),
            'type' => $type,
            'value' => $this->getValueForType($type),
            'group' => $this->faker->randomElement(['general', 'security', 'performance', 'ui_ux', 'api']),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80),
            'is_public' => $this->faker->boolean(20),
            'is_required' => $this->faker->boolean(10),
            'is_readonly' => $this->faker->boolean(5),
            'is_encrypted' => $this->faker->boolean(5),
            'is_cacheable' => $this->faker->boolean(70),
            'validation_rules' => json_encode(['max:255']),
            'default_value' => $this->getDefaultValueForType($type),
            'placeholder' => $this->faker->sentence(2),
            'tooltip' => $this->faker->sentence(),
            'metadata' => json_encode(['created_by' => 'factory']),
            'tags' => json_encode($this->faker->words(3)),
            'version' => '1.0.0',
            'environment' => $this->faker->randomElement(['local', 'staging', 'production']),
            'cache_key' => $this->faker->slug(),
            'cache_ttl' => $this->faker->randomElement([0, 60, 300, 900, 3600, 86400]),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Get appropriate value for the given type
     */
    private function getValueForType(string $type): mixed
    {
        return match ($type) {
            'string', 'text' => $this->faker->sentence(),
            'integer' => $this->faker->numberBetween(1, 1000),
            'boolean' => $this->faker->boolean(),
            'json' => json_encode(['key' => $this->faker->word(), 'value' => $this->faker->sentence()]),
            'array' => json_encode($this->faker->words(3)),
            'file' => $this->faker->filePath(),
            'color' => $this->faker->hexColor(),
            'date' => $this->faker->date(),
            'datetime' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'email' => $this->faker->email(),
            'url' => $this->faker->url(),
            'password' => $this->faker->password(),
            'float' => $this->faker->randomFloat(2, 0, 100),
            default => $this->faker->sentence(),
        };
    }

    /**
     * Get appropriate default value for the given type
     */
    private function getDefaultValueForType(string $type): mixed
    {
        return match ($type) {
            'string', 'text' => $this->faker->word(),
            'integer' => $this->faker->numberBetween(1, 100),
            'boolean' => false,
            'json' => json_encode([]),
            'array' => json_encode([]),
            'file' => null,
            'color' => '#000000',
            'date' => null,
            'datetime' => null,
            'email' => $this->faker->email(),
            'url' => $this->faker->url(),
            'password' => null,
            'float' => 0.0,
            default => null,
        };
    }

    /**
     * Indicate that the setting is active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the setting is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the setting is public
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the setting is private
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the setting is required
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    /**
     * Indicate that the setting is optional
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }

    /**
     * Indicate that the setting is read-only
     */
    public function readonly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_readonly' => true,
        ]);
    }

    /**
     * Indicate that the setting is encrypted
     */
    public function encrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    /**
     * Create a setting with a specific type
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'value' => $this->getValueForType($type),
            'default_value' => $this->getDefaultValueForType($type),
        ]);
    }

    /**
     * Create a setting in a specific group
     */
    public function inGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }

    /**
     * Create a setting with a specific category
     */
    public function inCategory(SystemSettingCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
