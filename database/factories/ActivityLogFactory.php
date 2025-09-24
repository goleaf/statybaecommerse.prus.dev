<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $events = ['created', 'updated', 'deleted', 'restored', 'login', 'logout', 'failed_login', 'password_changed', 'email_verified', 'custom'];
        $logNames = ['default', 'auth', 'user', 'order', 'product', 'system', 'payment', 'notification'];
        $severities = ['low', 'medium', 'high', 'critical'];
        $categories = ['authentication', 'user_management', 'order_processing', 'product_management', 'system', 'payment', 'notification'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
        $operatingSystems = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];
        $countries = ['LT', 'EN', 'DE', 'US', 'FR', 'ES', 'IT', 'PL'];
        $subjectTypes = ['App\Models\User', 'App\Models\Product', 'App\Models\Order', 'App\Models\Category'];

        return [
            'log_name' => fake()->randomElement($logNames),
            'description' => fake()->sentence(),
            'event' => fake()->randomElement($events),
            'subject_type' => fake()->randomElement($subjectTypes),
            'subject_id' => fake()->numberBetween(1, 100),
            'causer_type' => 'App\Models\User',
            'causer_id' => User::factory(),
            'properties' => [
                'old_values' => fake()->words(3),
                'new_values' => fake()->words(3),
                'changes' => fake()->words(2),
                'metadata' => [
                    'ip' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'timestamp' => fake()->dateTime()->format('Y-m-d H:i:s'),
                ],
            ],
            'batch_uuid' => Str::uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'device_type' => fake()->randomElement($deviceTypes),
            'browser' => fake()->randomElement($browsers),
            'os' => fake()->randomElement($operatingSystems),
            'country' => fake()->randomElement($countries),
            'is_important' => fake()->boolean(20),  // 20% chance of being important
            'is_system' => fake()->boolean(30),  // 30% chance of being system
            'severity' => fake()->randomElement($severities),
            'category' => fake()->randomElement($categories),
            'notes' => fake()->optional(0.3)->sentence(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the activity log is important.
     */
    public function important(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_important' => true,
        ]);
    }

    /**
     * Indicate that the activity log is system generated.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    /**
     * Indicate that the activity log has high severity.
     */
    public function highSeverity(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'high',
        ]);
    }

    /**
     * Indicate that the activity log has critical severity.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => 'critical',
        ]);
    }

    /**
     * Create an activity log for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'causer_id' => $user->id,
            'causer_type' => 'App\Models\User',
        ]);
    }

    /**
     * Create an activity log for a specific event.
     */
    public function event(string $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => $event,
        ]);
    }

    /**
     * Create an activity log for a specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
