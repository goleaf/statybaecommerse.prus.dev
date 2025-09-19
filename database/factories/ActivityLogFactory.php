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
            'subject_type' => $this->faker->randomElement([User::class, 'App\Models\Product']),
            'subject_id' => $this->faker->numberBetween(1, 100),
            'causer_type' => User::class,
            'causer_id' => User::factory(),
            'properties' => [
                'old_values' => $this->faker->words(3),
                'new_values' => $this->faker->words(3),
                'changes' => $this->faker->words(2),
            ],
        ];
    }
}
