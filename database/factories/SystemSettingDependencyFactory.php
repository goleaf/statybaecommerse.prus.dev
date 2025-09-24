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
            'setting.value == "enabled"',
            'setting.value == "disabled"',
            'setting.value > 0',
            'setting.value < 100',
            'setting.value contains "test"',
            'setting.value not contains "test"',
            'setting.value is empty',
            'setting.value is not empty',
            'setting.value is true',
            'setting.value is false',
        ];

        return [
            'setting_id' => SystemSetting::factory(),
            'depends_on_setting_id' => SystemSetting::factory(),
            'condition' => $this->faker->randomElement($conditions),
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
            'condition' => "setting.value == \"{$value}\"",
        ]);
    }

    public function notEquals(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => "setting.value != \"{$value}\"",
        ]);
    }

    public function greaterThan(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => "setting.value > \"{$value}\"",
        ]);
    }

    public function lessThan(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => "setting.value < \"{$value}\"",
        ]);
    }

    public function contains(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => "setting.value contains \"{$value}\"",
        ]);
    }

    public function notContains(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => "setting.value not contains \"{$value}\"",
        ]);
    }

    public function isEmpty(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'setting.value is empty',
        ]);
    }

    public function isNotEmpty(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'setting.value is not empty',
        ]);
    }

    public function isTrue(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'setting.value is true',
        ]);
    }

    public function isFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'setting.value is false',
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
