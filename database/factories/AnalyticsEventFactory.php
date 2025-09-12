<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnalyticsEvent>
 */
final class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = [
            'page_view',
            'product_view',
            'add_to_cart',
            'remove_from_cart',
            'purchase',
            'search',
            'user_register',
            'user_login',
            'user_logout',
            'newsletter_signup',
            'contact_form',
            'download',
            'video_play',
            'social_share',
        ];

        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $countries = ['LT', 'EN', 'DE', 'US', 'FR', 'ES'];
        $operatingSystems = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];

        $eventType = fake()->randomElement($eventTypes);
        $hasUser = fake()->boolean(70); // 70% chance of having a user
        $hasValue = in_array($eventType, ['purchase', 'add_to_cart']) && fake()->boolean(60);

        $trackableType = null;
        $trackableId = null;
        if (in_array($eventType, ['product_view', 'add_to_cart', 'remove_from_cart'])) {
            $trackableType = Product::class;
            $trackableId = Product::factory()->create()->id;
        } elseif ($eventType === 'purchase') {
            $trackableType = Order::class;
            $trackableId = Order::factory()->create()->id;
        }

        return [
            'event_type' => $eventType,
            'session_id' => fake()->uuid(),
            'user_id' => $hasUser ? User::factory()->create()->id : null,
            'url' => fake()->url(),
            'referrer' => fake()->optional(0.3)->url(),
            'ip_address' => fake()->ipv4(),
            'country_code' => fake()->randomElement($countries),
            'device_type' => fake()->randomElement($deviceTypes),
            'browser' => fake()->randomElement($browsers),
            'os' => fake()->randomElement($operatingSystems),
            'screen_resolution' => fake()->randomElement(['1920x1080', '1366x768', '1440x900', '1536x864', '375x667']),
            'trackable_type' => $trackableType,
            'trackable_id' => $trackableId,
            'value' => $hasValue ? fake()->randomFloat(2, 10, 1000) : null,
            'currency' => 'EUR',
            'properties' => [
                'page_title' => fake()->sentence(3),
                'category' => fake()->randomElement(['electronics', 'clothing', 'books', 'home', 'sports']),
                'search_query' => $eventType === 'search' ? fake()->words(2, true) : null,
                'product_name' => $trackableType === Product::class ? fake()->words(3, true) : null,
            ],
            'user_agent' => fake()->userAgent(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the event is a page view.
     */
    public function pageView(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'page_view',
            'value' => null,
        ]);
    }

    /**
     * Indicate that the event is a product view.
     */
    public function productView(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'product_view',
            'trackable_type' => Product::class,
            'trackable_id' => Product::factory()->create()->id,
            'value' => null,
        ]);
    }

    /**
     * Indicate that the event is a purchase.
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'purchase',
            'trackable_type' => Order::class,
            'trackable_id' => Order::factory()->create()->id,
            'value' => fake()->randomFloat(2, 50, 500),
            'currency' => 'EUR',
        ]);
    }

    /**
     * Indicate that the event is from a registered user.
     */
    public function registeredUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->create()->id,
        ]);
    }

    /**
     * Indicate that the event is from an anonymous user.
     */
    public function anonymousUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the event is from a mobile device.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'os' => fake()->randomElement(['iOS', 'Android']),
            'browser' => fake()->randomElement(['Safari', 'Chrome']),
        ]);
    }

    /**
     * Indicate that the event is from a desktop device.
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'os' => fake()->randomElement(['Windows', 'macOS', 'Linux']),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
        ]);
    }

    /**
     * Indicate that the event has a value.
     */
    public function withValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'EUR',
        ]);
    }
}