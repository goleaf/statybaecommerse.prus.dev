<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReferralReward>
 */
final class ReferralRewardFactory extends Factory
{
    protected $model = ReferralReward::class;

    public function definition(): array
    {
        $types = ['discount', 'credit'];
        $type = fake()->randomElement($types);

        $currentUserId = auth()->id();

        return [
            'referral_id' => null,
            'user_id' => $currentUserId ?? User::factory(),
            'order_id' => null,
            'type' => $type,
            'title' => [
                'en' => fake()->sentence(3),
                'lt' => fake()->sentence(3),
            ],
            'description' => [
                'en' => fake()->paragraph(),
                'lt' => fake()->paragraph(),
            ],
            'amount' => fake()->randomFloat(2, 5, 100),
            'currency_code' => 'EUR',
            'status' => 'pending',
            'applied_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => fake()->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'is_active' => true,
            'priority' => fake()->numberBetween(0, 100),
            'conditions' => fake()->optional(0.2)->randomElements([
                ['field' => 'order_total', 'operator' => '>=', 'value' => 50],
                ['field' => 'user_type', 'operator' => '=', 'value' => 'premium'],
                ['field' => 'category', 'operator' => 'in', 'value' => ['electronics', 'clothing']],
            ], fake()->numberBetween(1, 3)),
            'metadata' => fake()->optional(0.3)->randomElements([
                'source' => fake()->randomElement(['email', 'social', 'direct']),
                'campaign' => fake()->word(),
                'referrer_level' => fake()->numberBetween(1, 5),
            ]),
            'reward_data' => fake()->optional(0.4)->randomElements([
                'discount_percentage' => fake()->numberBetween(5, 25),
                'free_shipping' => fake()->boolean(),
                'bonus_points' => fake()->numberBetween(100, 1000),
            ]),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'applied_at' => null,
        ]);
    }

    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'applied',
            'applied_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function referrerBonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'referrer_bonus',
        ]);
    }

    public function referredDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'referred_discount',
        ]);
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

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->numberBetween(80, 100),
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->numberBetween(0, 20),
        ]);
    }
}
