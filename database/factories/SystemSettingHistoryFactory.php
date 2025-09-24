<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SystemSetting;
use App\Models\SystemSettingHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class SystemSettingHistoryFactory extends Factory
{
    protected $model = SystemSettingHistory::class;

    public function definition(): array
    {
        $reasons = [
            'Configuration update',
            'System maintenance',
            'User request',
            'Bug fix',
            'Feature implementation',
            'Security update',
            'Performance optimization',
            'Default value change',
        ];

        return [
            'system_setting_id' => SystemSetting::factory(),
            'old_value' => fake()->boolean(70) ? fake()->sentence() : null,
            'new_value' => fake()->boolean(80) ? fake()->sentence() : null,
            'changed_by' => User::factory(),
            'change_reason' => fake()->randomElement($reasons),
        ];
    }

    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'old_value' => null,
            'new_value' => fake()->sentence(),
        ]);
    }

    public function updated(): static
    {
        return $this->state(fn (array $attributes) => [
            'old_value' => fake()->sentence(),
            'new_value' => fake()->sentence(),
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'old_value' => fake()->sentence(),
            'new_value' => null,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-7 days'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }

    public function withReason(string $reason): static
    {
        return $this->state(fn (array $attributes) => [
            'change_reason' => $reason,
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'changed_by' => $user->id,
        ]);
    }

    public function forSetting(SystemSetting $setting): static
    {
        return $this->state(fn (array $attributes) => [
            'system_setting_id' => $setting->id,
        ]);
    }
}
