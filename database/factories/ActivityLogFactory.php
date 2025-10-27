<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

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

        $attributes = [
            'log_name'    => fake()->randomElement($logNames),
            'description' => fake()->sentence(),
        ];

        if ($this->tableHasColumn('event')) {
            $attributes['event'] = fake()->randomElement($events);
        }

        if ($this->tableHasColumn('subject_type')) {
            $attributes['subject_type'] = fake()->randomElement($subjectTypes);
        }

        if ($this->tableHasColumn('subject_id')) {
            $attributes['subject_id'] = fake()->numberBetween(1, 100);
        }

        if ($this->tableHasColumn('causer_type')) {
            $attributes['causer_type'] = User::class;
        }

        if ($this->tableHasColumn('causer_id')) {
            $attributes['causer_id'] = static fn (): int => User::factory()->createQuietly()->getKey();
        }

        if ($this->tableHasColumn('properties')) {
            $attributes['properties'] = [
                'old_values' => fake()->words(3),
                'new_values' => fake()->words(3),
                'changes'    => fake()->words(2),
                'metadata'   => [
                    'ip'         => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'timestamp'  => fake()->dateTime()->format('Y-m-d H:i:s'),
                ],
            ];
        }

        if ($this->tableHasColumn('batch_uuid')) {
            $attributes['batch_uuid'] = Str::uuid();
        }

        if ($this->tableHasColumn('ip_address')) {
            $attributes['ip_address'] = fake()->ipv4();
        }

        if ($this->tableHasColumn('user_agent')) {
            $attributes['user_agent'] = fake()->userAgent();
        }

        if ($this->tableHasColumn('device_type')) {
            $attributes['device_type'] = fake()->randomElement($deviceTypes);
        }

        if ($this->tableHasColumn('browser')) {
            $attributes['browser'] = fake()->randomElement($browsers);
        }

        if ($this->tableHasColumn('os')) {
            $attributes['os'] = fake()->randomElement($operatingSystems);
        }

        if ($this->tableHasColumn('country')) {
            $attributes['country'] = fake()->randomElement($countries);
        }

        if ($this->tableHasColumn('is_important')) {
            $attributes['is_important'] = fake()->boolean(20); // 20% chance of being important
        }

        if ($this->tableHasColumn('is_system')) {
            $attributes['is_system'] = fake()->boolean(30); // 30% chance of being system
        }

        if ($this->tableHasColumn('severity')) {
            $attributes['severity'] = fake()->randomElement($severities);
        }

        if ($this->tableHasColumn('category')) {
            $attributes['category'] = fake()->randomElement($categories);
        }

        if ($this->tableHasColumn('notes')) {
            $attributes['notes'] = fake()->optional(0.3)->sentence();
        }

        if ($this->tableHasColumn('created_at')) {
            $attributes['created_at'] = fake()->dateTimeBetween('-30 days', 'now');
        }

        if ($this->tableHasColumn('updated_at')) {
            $attributes['updated_at'] = fake()->dateTimeBetween('-30 days', 'now');
        }

        return $attributes;
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
        return $this->state(function (array $attributes) use ($user): array {
            $state = [];

            if ($this->tableHasColumn('causer_id')) {
                $state['causer_id'] = $user->id;
            }

            if ($this->tableHasColumn('causer_type')) {
                $state['causer_type'] = User::class;
            }

            return $state;
        });
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

    private function tableHasColumn(string $column): bool
    {
        $model = $this->newModel();
        $connection = $model->getConnectionName() ?? config('database.default');
        $table = $model->getTable();

        try {
            $schema = Schema::connection($connection);

            if (! $schema->hasTable($table)) {
                return false;
            }

            return $schema->hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }
}
