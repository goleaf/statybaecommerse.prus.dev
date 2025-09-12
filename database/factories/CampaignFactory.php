<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-30 days', '+30 days');
        $endsAt = $this->faker->dateTimeBetween($startsAt, '+60 days');

        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'channel_id' => Channel::factory(),
            'zone_id' => Zone::factory(),
            'status' => $this->faker->randomElement(['active', 'scheduled', 'paused', 'draft']),
            'metadata' => [
                'theme' => $this->faker->randomElement(['summer', 'winter', 'spring', 'autumn']),
                'color_scheme' => $this->faker->hexColor(),
                'target_device' => $this->faker->randomElement(['mobile', 'desktop', 'all']),
            ],
            'is_featured' => $this->faker->boolean(30),
            'send_notifications' => $this->faker->boolean(80),
            'track_conversions' => $this->faker->boolean(90),
            'max_uses' => $this->faker->numberBetween(100, 10000),
            'budget_limit' => $this->faker->randomFloat(2, 1000, 50000),
            'total_views' => $this->faker->numberBetween(0, 10000),
            'total_clicks' => $this->faker->numberBetween(0, 1000),
            'total_conversions' => $this->faker->numberBetween(0, 100),
            'total_revenue' => $this->faker->randomFloat(2, 0, 10000),
            'conversion_rate' => $this->faker->randomFloat(2, 0, 25),
            'target_audience' => [
                'age_range' => $this->faker->randomElement(['18-25', '26-35', '36-45', '46-55', '55+']),
                'gender' => $this->faker->randomElement(['male', 'female', 'all']),
                'interests' => $this->faker->randomElements(['fashion', 'technology', 'sports', 'travel', 'food'], 2),
            ],
            'target_categories' => $this->faker->randomElements([1, 2, 3, 4, 5], 2),
            'target_products' => $this->faker->randomElements([1, 2, 3, 4, 5], 3),
            'target_customer_groups' => $this->faker->randomElements([1, 2, 3], 1),
            'display_priority' => $this->faker->numberBetween(1, 10),
            'banner_image' => $this->faker->imageUrl(1200, 400, 'business'),
            'banner_alt_text' => $this->faker->sentence(),
            'cta_text' => $this->faker->randomElement(['Shop Now', 'Learn More', 'Get Started', 'Discover', 'Explore']),
            'cta_url' => $this->faker->url(),
            'auto_start' => $this->faker->boolean(20),
            'auto_end' => $this->faker->boolean(30),
            'auto_pause_on_budget' => $this->faker->boolean(40),
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->paragraph(2),
            'social_media_ready' => $this->faker->boolean(70),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(30),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(37),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'display_priority' => $this->faker->numberBetween(8, 10),
        ]);
    }

    public function highPerformance(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_views' => $this->faker->numberBetween(5000, 20000),
            'total_clicks' => $this->faker->numberBetween(500, 2000),
            'total_conversions' => $this->faker->numberBetween(50, 200),
            'total_revenue' => $this->faker->randomFloat(2, 5000, 25000),
            'conversion_rate' => $this->faker->randomFloat(2, 5, 15),
        ]);
    }
}