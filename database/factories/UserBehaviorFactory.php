<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * UserBehaviorFactory
 *
 * Factory for creating UserBehavior test data with realistic user behavior patterns.
 */
final class UserBehaviorFactory extends Factory
{
    protected $model = UserBehavior::class;

    public function definition(): array
    {
        $behaviorTypes = [
            'view',
            'click',
            'add_to_cart',
            'remove_from_cart',
            'purchase',
            'search',
            'filter',
            'sort',
            'wishlist',
            'share',
        ];

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
        ];

        $referrers = [
            'https://google.com',
            'https://facebook.com',
            'https://twitter.com',
            'https://linkedin.com',
            'https://reddit.com',
            'https://youtube.com',
            'https://instagram.com',
            'https://pinterest.com',
            null,
        ];

        return [
            'user_id' => User::factory(),
            'session_id' => $this->faker->uuid(),
            'product_id' => Product::factory(),
            'category_id' => Category::factory(),
            'behavior_type' => $this->faker->randomElement($behaviorTypes),
            'referrer' => $this->faker->randomElement($referrers),
            'user_agent' => $this->faker->randomElement($userAgents),
            'ip_address' => $this->faker->ipv4(),
            'metadata' => [
                'page_url' => $this->faker->url(),
                'page_title' => $this->faker->sentence(3),
                'viewport_width' => $this->faker->numberBetween(320, 1920),
                'viewport_height' => $this->faker->numberBetween(568, 1080),
                'screen_resolution' => $this->faker->randomElement(['1920x1080', '1366x768', '1440x900', '1536x864', '1280x720']),
                'color_depth' => $this->faker->randomElement([24, 32]),
                'timezone' => $this->faker->timezone(),
                'language' => $this->faker->randomElement(['en', 'lt', 'es', 'fr', 'de']),
                'is_mobile' => $this->faker->boolean(30),
                'is_tablet' => $this->faker->boolean(10),
                'is_desktop' => $this->faker->boolean(60),
            ],
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Create a view behavior
     */
    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'behavior_type' => 'view',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'view_duration' => $this->faker->numberBetween(1, 300),
                'scroll_depth' => $this->faker->numberBetween(0, 100),
            ]),
        ]);
    }

    /**
     * Create a click behavior
     */
    public function click(): static
    {
        return $this->state(fn (array $attributes) => [
            'behavior_type' => 'click',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'click_element' => $this->faker->randomElement(['button', 'link', 'image', 'product_card']),
                'click_position' => [
                    'x' => $this->faker->numberBetween(0, 1920),
                    'y' => $this->faker->numberBetween(0, 1080),
                ],
            ]),
        ]);
    }

    /**
     * Create an add to cart behavior
     */
    public function addToCart(): static
    {
        return $this->state(fn (array $attributes) => [
            'behavior_type' => 'add_to_cart',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'quantity' => $this->faker->numberBetween(1, 5),
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'variant_id' => $this->faker->optional()->uuid(),
            ]),
        ]);
    }

    /**
     * Create a purchase behavior
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'behavior_type' => 'purchase',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'order_id' => $this->faker->uuid(),
                'total_amount' => $this->faker->randomFloat(2, 20, 2000),
                'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer', 'crypto']),
                'currency' => 'EUR',
                'items_count' => $this->faker->numberBetween(1, 10),
            ]),
        ]);
    }

    /**
     * Create a search behavior
     */
    public function search(): static
    {
        return $this->state(fn (array $attributes) => [
            'behavior_type' => 'search',
            'product_id' => null,
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'search_query' => $this->faker->words(3, true),
                'search_results_count' => $this->faker->numberBetween(0, 500),
                'search_filters' => $this->faker->optional()->randomElements(['category', 'price', 'brand', 'color'], $this->faker->numberBetween(1, 3)),
            ]),
        ]);
    }

    /**
     * Create a recent behavior (within last 7 days)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a behavior from today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('today', 'now'),
        ]);
    }

    /**
     * Create a behavior with specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a behavior with specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Create a behavior with specific category
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Create a mobile behavior
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => $this->faker->randomElement([
                'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
                'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36',
            ]),
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'is_mobile' => true,
                'is_tablet' => false,
                'is_desktop' => false,
                'viewport_width' => $this->faker->numberBetween(320, 414),
                'viewport_height' => $this->faker->numberBetween(568, 896),
            ]),
        ]);
    }

    /**
     * Create a wishlist behavior
     */
    public function wishlist(): static
    {
        return $this->state(fn (array $attributes) => [
            'behavior_type' => 'wishlist',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'wishlist_note' => $this->faker->optional()->sentence(),
            ]),
        ]);
    }

    /**
     * Create a filter behavior
     */
    public function filter(): static
    {
        return $this->state(fn (array $attributes) => [
            'behavior_type' => 'filter',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'filters_applied' => $this->faker->randomElements(['price', 'brand', 'color', 'size'], $this->faker->numberBetween(1, 3)),
                'page_url' => $this->faker->url(),
                'page_title' => $this->faker->sentence(3),
            ]),
        ]);
    }

    /**
     * Create a desktop behavior
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => $this->faker->randomElement([
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ]),
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'is_mobile' => false,
                'is_tablet' => false,
                'is_desktop' => true,
                'viewport_width' => $this->faker->numberBetween(1024, 1920),
                'viewport_height' => $this->faker->numberBetween(768, 1080),
            ]),
        ]);
    }
}
