<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
final class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'log_name' => $this->faker->randomElement(['default', 'auth', 'user', 'product']),
            'description' => $this->faker->sentence(),
            'event' => $this->faker->randomElement(['created', 'updated', 'deleted', 'login', 'logout', 'custom']),
            'subject_type' => $this->faker->randomElement([User::class, 'App\Models\Product']),
            'subject_id' => $this->faker->numberBetween(1, 100),
            'causer_type' => User::class,
            'causer_id' => User::factory(),
            'properties' => [
                'old_values' => $this->faker->words(3),
                'new_values' => $this->faker->words(3),
                'changes' => $this->faker->words(2),
            ],
            'batch_uuid' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'device_type' => $this->faker->randomElement(['mobile', 'tablet', 'desktop']),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'country' => $this->faker->country(),
            'is_important' => $this->faker->boolean(20),
            'is_system' => $this->faker->boolean(10),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'category' => $this->faker->randomElement(['authentication', 'product', 'order', 'user', 'system']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
