<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignClick>
 */
final class CampaignClickFactory extends Factory
{
    protected $model = CampaignClick::class;

    public function definition(): array
    {
        return [
            // Only create related campaigns when the backing table exists to keep lightweight API tests stable.
            'campaign_id' => $this->resolveCampaignId(),
            'session_id'  => $this->faker->uuid(),
            'ip_address'  => $this->faker->ipv4(),
            'user_agent'  => $this->faker->userAgent(),
            'click_type'  => $this->faker->randomElement(['cta', 'banner', 'link', 'button']),
            'clicked_url' => $this->faker->optional(0.8)->url(),
            // Safely associate an existing customer when available without crashing SQLite test databases.
            'customer_id' => $this->resolveRandomCustomerId(),
            'clicked_at'  => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function cta(): static
    {
        return $this->state(fn (array $attributes) => [
            'click_type'  => 'cta',
            'clicked_url' => $this->faker->url(),
        ]);
    }

    public function banner(): static
    {
        return $this->state(fn (array $attributes) => [
            'click_type'  => 'banner',
            'clicked_url' => $this->faker->url(),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => User::factory(),
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'user_agent'  => $this->faker->userAgent() . ' Mobile',
        ]);
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'user_agent'  => $this->faker->userAgent(),
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_converted'     => true,
            'conversion_value' => $this->faker->randomFloat(2, 10, 1000),
        ]);
    }

    /**
     * Determine a random existing customer identifier or return null when unavailable.
     */
    private function resolveRandomCustomerId(): ?int
    {
        // Preserve the original 40% association chance from Faker's optional helper.
        if (! $this->faker->boolean(40)) {
            return null;
        }

        $userIds = $this->availableUserIds();

        if ($userIds === []) {
            return null;
        }

        return $this->faker->randomElement($userIds);
    }

    /**
     * Cache and return all existing user identifiers while guarding against missing tables during migrations.
     *
     * @return array<int, int>
     */
    private function availableUserIds(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        if (! Schema::hasTable('users')) {
            return $cache = [];
        }

        return $cache = User::query()->pluck('id')->all();
    }

    /**
     * Generate a campaign relationship lazily when discount campaigns are available.
     */
    private function resolveCampaignId(): mixed
    {
        if (! Schema::hasTable('discount_campaigns')) {
            return null;
        }

        return Campaign::factory();
    }
}
