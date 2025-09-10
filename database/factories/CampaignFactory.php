<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Campaign>
 */
final class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'starts_at' => now()->subDays(rand(0, 10)),
            'ends_at' => rand(0, 1) ? now()->addDays(rand(5, 30)) : null,
            'channel_id' => null,
            'zone_id' => null,
            'status' => $this->faker->randomElement(['draft', 'active', 'scheduled', 'paused', 'completed', 'cancelled']),
            'metadata' => [],
            'is_featured' => $this->faker->boolean(20),
            'send_notifications' => $this->faker->boolean(80),
            'track_conversions' => $this->faker->boolean(90),
            'max_uses' => $this->faker->optional()->numberBetween(10, 1000),
            'budget_limit' => $this->faker->optional()->randomFloat(2, 100, 10000),
        ];
    }
}


