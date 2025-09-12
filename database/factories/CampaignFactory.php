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
            'description' => $this->faker->paragraph(3),
            'type' => $this->faker->randomElement(['email', 'sms', 'push', 'banner', 'popup', 'social']),
            'subject' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(5, true),
            'starts_at' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'budget' => $this->faker->randomFloat(2, 100, 10000),
            'channel_id' => Channel::factory(),
            'zone_id' => Zone::factory(),
            'status' => $this->faker->randomElement(['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled']),
            'metadata' => [
                'source' => $this->faker->randomElement(['manual', 'automated', 'imported']),
                'tags' => $this->faker->words(3),
            ],
            'is_featured' => $this->faker->boolean(20),
            'send_notifications' => $this->faker->boolean(80),
            'track_conversions' => $this->faker->boolean(90),
            'max_uses' => $this->faker->numberBetween(100, 10000),
            'budget_limit' => $this->faker->randomFloat(2, 500, 50000),
            'total_views' => $this->faker->numberBetween(0, 100000),
            'total_clicks' => $this->faker->numberBetween(0, 10000),
            'total_conversions' => $this->faker->numberBetween(0, 1000),
            'total_revenue' => $this->faker->randomFloat(2, 0, 50000),
            'conversion_rate' => $this->faker->randomFloat(2, 0, 100),
            'target_audience' => [
                'age_range' => $this->faker->randomElement(['18-25', '26-35', '36-45', '46-55', '55+']),
                'gender' => $this->faker->randomElement(['all', 'male', 'female']),
                'location' => $this->faker->country(),
            ],
            'target_categories' => [],
            'target_products' => [],
            'target_customer_groups' => [],
            'target_segments' => [
                'behavior' => $this->faker->randomElement(['new_customers', 'returning_customers', 'high_value', 'inactive']),
                'purchase_history' => $this->faker->boolean(),
                'engagement' => $this->faker->randomElement(['low', 'medium', 'high']),
            ],
            'display_priority' => $this->faker->numberBetween(0, 100),
            'banner_image' => $this->faker->optional(0.7)->imageUrl(800, 400, 'business'),
            'banner_alt_text' => $this->faker->optional(0.8)->sentence(6),
            'cta_text' => $this->faker->optional(0.9)->randomElement(['Shop Now', 'Learn More', 'Get Started', 'Sign Up', 'Buy Now']),
            'cta_url' => $this->faker->optional(0.9)->url(),
            'auto_start' => $this->faker->boolean(30),
            'auto_end' => $this->faker->boolean(40),
            'auto_pause_on_budget' => $this->faker->boolean(20),
            'meta_title' => $this->faker->optional(0.8)->sentence(8),
            'meta_description' => $this->faker->optional(0.8)->paragraph(1),
            'social_media_ready' => $this->faker->boolean(60),
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
            'display_priority' => $this->faker->numberBetween(80, 100),
        ]);
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
            'subject' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(8, true),
        ]);
    }

    public function banner(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'banner',
            'banner_image' => $this->faker->imageUrl(1200, 600, 'business'),
            'banner_alt_text' => $this->faker->sentence(8),
            'cta_text' => $this->faker->randomElement(['Shop Now', 'Learn More', 'Get Started']),
            'cta_url' => $this->faker->url(),
        ]);
    }

    public function social(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'social',
            'social_media_ready' => true,
            'meta_title' => $this->faker->sentence(10),
            'meta_description' => $this->faker->paragraph(2),
        ]);
    }

    public function highPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_views' => $this->faker->numberBetween(50000, 200000),
            'total_clicks' => $this->faker->numberBetween(5000, 20000),
            'total_conversions' => $this->faker->numberBetween(500, 2000),
            'total_revenue' => $this->faker->randomFloat(2, 10000, 100000),
            'conversion_rate' => $this->faker->randomFloat(2, 5, 25),
        ]);
    }

    public function lowPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_views' => $this->faker->numberBetween(0, 1000),
            'total_clicks' => $this->faker->numberBetween(0, 50),
            'total_conversions' => $this->faker->numberBetween(0, 5),
            'total_revenue' => $this->faker->randomFloat(2, 0, 100),
            'conversion_rate' => $this->faker->randomFloat(2, 0, 2),
        ]);
    }
}
