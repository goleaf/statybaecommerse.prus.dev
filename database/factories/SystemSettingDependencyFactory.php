<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SystemSettingDependencyFactory extends Factory
{
    protected $model = SystemSettingDependency::class;

    public function definition(): array
    {
        $conditions = [
            'equals',
            'not_equals',
            'greater_than',
            'less_than',
            'contains',
            'not_contains',
            'is_empty',
            'is_not_empty',
            'is_true',
            'is_false',
        ];

        return [
            'setting_id' => SystemSetting::factory(),
            'depends_on_setting_id' => SystemSetting::factory(),
            'condition' => $this->faker->randomElement($conditions),
            'condition_value' => $this->faker->boolean(70) ? $this->faker->sentence() : null,
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
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

    public function equals(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'equals',
            'condition_value' => $value,
        ]);
    }

    public function notEquals(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'not_equals',
            'condition_value' => $value,
        ]);
    }

    public function greaterThan(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'greater_than',
            'condition_value' => $value,
        ]);
    }

    public function lessThan(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'less_than',
            'condition_value' => $value,
        ]);
    }

    public function contains(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'contains',
            'condition_value' => $value,
        ]);
    }

    public function notContains(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'not_contains',
            'condition_value' => $value,
        ]);
    }

    public function isEmpty(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'is_empty',
            'condition_value' => null,
        ]);
    }

    public function isNotEmpty(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'is_not_empty',
            'condition_value' => null,
        ]);
    }

    public function isTrue(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'is_true',
            'condition_value' => null,
        ]);
    }

    public function isFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'is_false',
            'condition_value' => null,
        ]);
    }

    public function between(SystemSetting $setting, SystemSetting $dependsOn): static
    {
        return $this->state(fn (array $attributes) => [
            'setting_id' => $setting->id,
            'depends_on_setting_id' => $dependsOn->id,
        ]);
    }
}

