<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignView>
 */
final class CampaignViewFactory extends Factory
{
    protected $model = CampaignView::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'referer' => $this->faker->optional()->url(),
            'customer_id' => $this->faker->optional()->randomElement([User::factory()]),
            'viewed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => User::factory(),
        ]);
    }

    public function withoutCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => null,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'viewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'viewed_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }

    public function withReferer(): static
    {
        return $this->state(fn (array $attributes) => [
            'referer' => $this->faker->url(),
        ]);
    }

    public function withoutReferer(): static
    {
        return $this->state(fn (array $attributes) => [
            'referer' => null,
        ]);
    }
}
