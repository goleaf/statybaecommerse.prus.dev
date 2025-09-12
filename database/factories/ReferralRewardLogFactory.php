<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralRewardLog;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReferralRewardLog>
 */
final class ReferralRewardLogFactory extends Factory
{
    protected $model = ReferralRewardLog::class;

    public function definition(): array
    {
        $actions = ['created', 'applied', 'expired', 'updated', 'viewed', 'deleted'];
        $action = fake()->randomElement($actions);

        return [
            'referral_reward_id' => ReferralReward::factory(),
            'user_id' => User::factory(),
            'action' => $action,
            'data' => match ($action) {
                'created' => ['source' => fake()->randomElement(['admin', 'system', 'api'])],
                'applied' => ['order_id' => fake()->numberBetween(1, 1000), 'applied_by' => fake()->numberBetween(1, 100)],
                'expired' => ['expired_reason' => fake()->randomElement(['timeout', 'manual', 'system'])],
                'updated' => ['changes' => fake()->randomElements(['amount', 'status', 'description'], fake()->numberBetween(1, 3))],
                'viewed' => ['viewed_by' => fake()->numberBetween(1, 100), 'ip_address' => fake()->ipv4()],
                'deleted' => ['deleted_by' => fake()->numberBetween(1, 100), 'reason' => fake()->sentence()],
                default => [],
            },
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'created',
            'data' => ['source' => fake()->randomElement(['admin', 'system', 'api'])],
        ]);
    }

    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'applied',
            'data' => [
                'order_id' => fake()->numberBetween(1, 1000),
                'applied_by' => fake()->numberBetween(1, 100),
            ],
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'expired',
            'data' => [
                'expired_reason' => fake()->randomElement(['timeout', 'manual', 'system']),
            ],
        ]);
    }

    public function viewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'viewed',
            'data' => [
                'viewed_by' => fake()->numberBetween(1, 100),
                'ip_address' => fake()->ipv4(),
            ],
        ]);
    }
}
