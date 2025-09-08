<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalyticsEvent;
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
                'button_click',
                'product_view',
                'add_to_cart',
                'remove_from_cart',
                'purchase',
                'search',
                'filter_applied',
                'form_submission',
                'video_play',
                'download',
                'signup',
                'login',
                'logout',
            ]),
            'session_id' => $this->faker->uuid(),
            'user_id' => $this->faker->optional(0.7)->passthrough(User::factory()->create()->id),
            'properties' => $this->generateProperties(),
            'url' => $this->faker->url(),
            'referrer' => $this->faker->optional(0.6)->url(),
            'user_agent' => $this->faker->userAgent(),
            'ip_address' => $this->faker->ipv4(),
            'country_code' => $this->faker->optional(0.8)->countryCode(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Generate realistic properties based on event type.
     */
    private function generateProperties(): array
    {
        $eventType = $this->faker->randomElement([
            'page_view',
            'product_view',
            'add_to_cart',
            'purchase',
            'search',
        ]);

        return match ($eventType) {
            'page_view' => [
                'page' => $this->faker->randomElement(['home', 'products', 'about', 'contact']),
                'section' => $this->faker->optional()->randomElement(['hero', 'features', 'testimonials']),
                'scroll_depth' => $this->faker->numberBetween(10, 100),
            ],
            'product_view' => [
                'product_id' => $this->faker->numberBetween(1, 1000),
                'product_name' => $this->faker->words(3, true),
                'category' => $this->faker->randomElement(['Electronics', 'Clothing', 'Books', 'Home']),
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'brand' => $this->faker->company(),
            ],
            'add_to_cart' => [
                'product_id' => $this->faker->numberBetween(1, 1000),
                'quantity' => $this->faker->numberBetween(1, 5),
                'price' => $this->faker->randomFloat(2, 10, 500),
                'variant' => $this->faker->optional()->randomElement(['Small', 'Medium', 'Large']),
            ],
            'purchase' => [
                'order_id' => 'ORD-' . $this->faker->unique()->numerify('#####'),
                'total' => $this->faker->randomFloat(2, 50, 2000),
                'items_count' => $this->faker->numberBetween(1, 10),
                'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
                'currency' => $this->faker->currencyCode(),
            ],
            'search' => [
                'query' => $this->faker->words(2, true),
                'results_count' => $this->faker->numberBetween(0, 100),
                'filters_applied' => $this->faker->optional()->randomElements(['category', 'price', 'brand'], 2),
            ],
            default => [
                'action' => $this->faker->word(),
                'value' => $this->faker->optional()->randomFloat(2, 1, 100),
            ],
        };
    }

    /**
     * Indicate that the event is a page view.
     */
    public function pageView(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_type' => 'page_view',
            'properties' => [
                'page' => $this->faker->randomElement(['home', 'products', 'about', 'contact']),
                'title' => $this->faker->sentence(4),
                'scroll_depth' => $this->faker->numberBetween(10, 100),
                'time_on_page' => $this->faker->numberBetween(5, 300),  // seconds
            ],
        ]);
    }

    /**
     * Indicate that the event is a product view.
     */
    public function productView(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_type' => 'product_view',
            'properties' => [
                'product_id' => $this->faker->numberBetween(1, 1000),
                'product_name' => $this->faker->words(3, true),
                'category' => $this->faker->randomElement(['Electronics', 'Clothing', 'Books']),
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'brand' => $this->faker->company(),
                'sku' => $this->faker->bothify('SKU-###-???'),
            ],
        ]);
    }

    /**
     * Indicate that the event is an add to cart action.
     */
    public function addToCart(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_type' => 'add_to_cart',
            'properties' => [
                'product_id' => $this->faker->numberBetween(1, 1000),
                'quantity' => $this->faker->numberBetween(1, 5),
                'price' => $this->faker->randomFloat(2, 10, 500),
                'variant' => $this->faker->optional()->randomElement(['Small', 'Medium', 'Large']),
                'color' => $this->faker->optional()->colorName(),
            ],
        ]);
    }

    /**
     * Indicate that the event is a purchase.
     */
    public function purchase(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_type' => 'purchase',
            'properties' => [
                'order_id' => 'ORD-' . $this->faker->unique()->numerify('#####'),
                'total' => $this->faker->randomFloat(2, 50, 2000),
                'items_count' => $this->faker->numberBetween(1, 10),
                'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
                'currency' => 'EUR',
                'discount_amount' => $this->faker->optional(0.3)->randomFloat(2, 5, 100),
            ],
        ]);
    }

    /**
     * Indicate that the event is a search.
     */
    public function search(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_type' => 'search',
            'properties' => [
                'query' => $this->faker->words(2, true),
                'results_count' => $this->faker->numberBetween(0, 100),
                'filters_applied' => $this->faker->optional()->randomElements(['category', 'price', 'brand'], 2),
                'sort_order' => $this->faker->randomElement(['relevance', 'price_asc', 'price_desc', 'newest']),
            ],
        ]);
    }

    /**
     * Indicate that the event is for an authenticated user.
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user?->id ?? User::factory()->create()->id,
        ]);
    }

    /**
     * Indicate that the event is anonymous (no user).
     */
    public function anonymous(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the event is for a specific session.
     */
    public function forSession(string $sessionId): static
    {
        return $this->state(fn(array $attributes) => [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Indicate that the event happened recently.
     */
    public function recent(): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Indicate that the event happened today.
     */
    public function today(): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('today', 'now'),
        ]);
    }

    /**
     * Set custom properties for the event.
     */
    public function withProperties(array $properties): static
    {
        return $this->state(fn(array $attributes) => [
            'properties' => array_merge($attributes['properties'] ?? [], $properties),
        ]);
    }

    /**
     * Set a specific country code.
     */
    public function fromCountry(string $countryCode): static
    {
        return $this->state(fn(array $attributes) => [
            'country_code' => $countryCode,
        ]);
    }
}
