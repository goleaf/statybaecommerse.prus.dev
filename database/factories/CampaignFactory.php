<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
final class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        $name = $this->faker->sentence(3);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'starts_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'ends_at' => $this->faker->dateTimeBetween('now', '+3 months'),
            'channel_id' => Channel::factory(),
            'zone_id' => Zone::factory(),
            'status' => 'active',
            'is_active' => true,
            'metadata' => [
                'source' => $this->faker->randomElement(['manual', 'automated', 'imported']),
                'tags' => $this->faker->words(3),
            ],
            'is_featured' => $this->faker->boolean(20),
            'send_notifications' => $this->faker->boolean(80),
            'track_conversions' => $this->faker->boolean(90),
            'max_uses' => $this->faker->numberBetween(100, 10000),
            'budget_limit' => round($this->faker->randomFloat(2, 500, 50000), 2),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'starts_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'ends_at' => $this->faker->dateTimeBetween('now', '+2 months'),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'starts_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'ends_at' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'starts_at' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
            'ends_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'starts_at' => null,
            'ends_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'type' => 'email',
                'subject' => $this->faker->sentence(6),
                'content' => $this->faker->paragraphs(8, true),
            ]),
        ]);
    }

    public function banner(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'type' => 'banner',
                'banner_image' => $this->faker->imageUrl(1200, 600, 'business'),
                'banner_alt_text' => $this->faker->sentence(8),
                'cta_text' => $this->faker->randomElement(['Shop Now', 'Learn More', 'Get Started']),
                'cta_url' => $this->faker->url(),
            ]),
        ]);
    }

    public function social(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'type' => 'social',
                'social_media_ready' => true,
                'meta_title' => $this->faker->sentence(10),
                'meta_description' => $this->faker->paragraph(2),
            ]),
        ]);
    }

    public function highPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'total_views' => $this->faker->numberBetween(50000, 200000),
                'total_clicks' => $this->faker->numberBetween(5000, 20000),
                'total_conversions' => $this->faker->numberBetween(500, 2000),
                'total_revenue' => round($this->faker->randomFloat(2, 10000, 100000), 2),
                'conversion_rate' => round($this->faker->randomFloat(4, 0.05, 0.25), 4),
            ]),
        ]);
    }

    public function lowPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'total_views' => $this->faker->numberBetween(0, 1000),
                'total_clicks' => $this->faker->numberBetween(0, 50),
                'total_conversions' => $this->faker->numberBetween(0, 5),
                'total_revenue' => round($this->faker->randomFloat(2, 0, 100), 2),
                'conversion_rate' => round($this->faker->randomFloat(4, 0.0, 0.02), 4),
            ]),
        ]);
    }
}
