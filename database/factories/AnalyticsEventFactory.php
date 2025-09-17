<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnalyticsEvent>
 */
final class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        return [
            'event_type' => $this->faker->randomElement([
                'page_view',
                'product_view',
                'add_to_cart',
                'purchase',
                'search',
                'click',
                'scroll',
                'form_submit',
                'download',
                'video_play',
            ]),
            'session_id' => $this->faker->uuid(),
            'user_id' => $this->faker->optional()->randomElement([User::factory()]),
            'url' => $this->faker->url(),
            'referrer' => $this->faker->optional()->url(),
            'ip_address' => $this->faker->ipv4(),
            'country_code' => $this->faker->countryCode(),
            'device_type' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera']),
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'screen_resolution' => $this->faker->randomElement([
                '1920x1080',
                '1366x768',
                '1440x900',
                '1536x864',
                '375x667',
                '414x896',
            ]),
            'trackable_type' => $this->faker->optional()->randomElement([Product::class]),
            'trackable_id' => $this->faker->optional()->randomElement([Product::factory()]),
            'value' => $this->faker->optional()->randomFloat(2, 0, 1000),
            'currency' => $this->faker->randomElement(['EUR', 'USD', 'GBP']),
            'properties' => [
                'category' => $this->faker->randomElement(['electronics', 'clothing', 'books', 'home']),
                'brand' => $this->faker->company(),
                'color' => $this->faker->colorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'rating' => $this->faker->numberBetween(1, 5),
            ],
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    public function pageView(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'page_view',
            'value' => null,
            'trackable_type' => null,
            'trackable_id' => null,
        ]);
    }

    public function productView(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'product_view',
            'trackable_type' => Product::class,
            'trackable_id' => Product::factory(),
        ]);
    }

    public function addToCart(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'add_to_cart',
            'value' => $this->faker->randomFloat(2, 10, 500),
            'trackable_type' => Product::class,
            'trackable_id' => Product::factory(),
        ]);
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'purchase',
            'value' => $this->faker->randomFloat(2, 50, 2000),
            'trackable_type' => Product::class,
            'trackable_id' => Product::factory(),
        ]);
    }

    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'os' => $this->faker->randomElement(['iOS', 'Android']),
            'screen_resolution' => $this->faker->randomElement(['375x667', '414x896', '360x640']),
        ]);
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux']),
            'screen_resolution' => $this->faker->randomElement(['1920x1080', '1366x768', '1440x900']),
        ]);
    }

    public function chrome(): static
    {
        return $this->state(fn (array $attributes) => [
            'browser' => 'Chrome',
        ]);
    }

    public function firefox(): static
    {
        return $this->state(fn (array $attributes) => [
            'browser' => 'Firefox',
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }
}