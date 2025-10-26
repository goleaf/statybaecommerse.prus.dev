<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * UserProductInteractionFactory
 *
 * Factory for creating UserProductInteraction test data with realistic interaction patterns.
 */
final class UserProductInteractionFactory extends Factory
{
    protected $model = UserProductInteraction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $interactionTypes = ['click', 'add_to_cart', 'purchase', 'review', 'share', 'favorite', 'compare'];
        // Rotate through the type list so repeated factory calls for the same
        // user/product pair stay unique even when a test does not override
        // the `event` attribute, keeping the SQLite unique index satisfied.
        static $typePointer = 0;
        $interactionType = $interactionTypes[$typePointer % count($interactionTypes)];
        $typePointer++;

        // Anchor the interaction window to the past month so tests relying
        // on the "recent" scope receive deterministic timestamps.
        $firstInteraction = fake()->dateTimeBetween('-30 days', '-1 day');
        $lastInteraction = fake()->dateTimeBetween('-1 day', 'now');

        $rating = $interactionType === 'review' ? fake()->randomFloat(1, 1, 5) : null;
        $count = fake()->numberBetween(1, 20);

        return [
            'user_id'            => User::factory(),
            'product_id'         => Product::factory(),
            'product_variant_id' => null,
            'event'              => $interactionType,
            'occurred_at'        => $lastInteraction,
            'meta'               => [
                'rating'            => $rating,
                'count'             => $count,
                'first_interaction' => $firstInteraction,
                'last_interaction'  => $lastInteraction,
            ],
        ];
    }

    /**
     * Create a view interaction.
     */
    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => 'view',
            'meta'  => array_merge($attributes['meta'] ?? [], [
                'rating' => null,
                'count'  => fake()->numberBetween(1, 50),
            ]),
        ]);
    }

    /**
     * Create a click interaction.
     */
    public function click(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => 'click',
            'meta'  => array_merge($attributes['meta'] ?? [], [
                'rating' => null,
                'count'  => fake()->numberBetween(1, 10),
            ]),
        ]);
    }

    /**
     * Create an add to cart interaction.
     */
    public function addToCart(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => 'add_to_cart',
            'meta'  => array_merge($attributes['meta'] ?? [], [
                'rating' => null,
                'count'  => fake()->numberBetween(1, 5),
            ]),
        ]);
    }

    /**
     * Create a purchase interaction.
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => 'purchase',
            'meta'  => array_merge($attributes['meta'] ?? [], [
                'rating' => null,
                'count'  => fake()->numberBetween(1, 3),
            ]),
        ]);
    }

    /**
     * Create a review interaction.
     */
    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => 'review',
            'meta'  => array_merge($attributes['meta'] ?? [], [
                'rating' => fake()->randomFloat(1, 1, 5),
                'count'  => 1,
            ]),
        ]);
    }

    /**
     * Create a share interaction.
     */
    public function share(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => 'share',
            'meta'  => array_merge($attributes['meta'] ?? [], [
                'rating' => null,
                'count'  => fake()->numberBetween(1, 5),
            ]),
        ]);
    }

    /**
     * Create a high-rated interaction.
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => array_merge($attributes['meta'] ?? [], [
                'rating' => fake()->randomFloat(1, 4, 5),
            ]),
        ]);
    }

    /**
     * Create a low-rated interaction.
     */
    public function lowRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => array_merge($attributes['meta'] ?? [], [
                'rating' => fake()->randomFloat(1, 1, 3),
            ]),
        ]);
    }

    /**
     * Create a recent interaction.
     */
    public function recent(): static
    {
        $now = now();

        return $this->state(function (array $attributes) use ($now) {
            $first = fake()->dateTimeBetween('-7 days', '-1 day');
            $last = fake()->dateTimeBetween('-1 day', $now);

            return [
                'occurred_at' => $last,
                'meta'        => array_merge($attributes['meta'] ?? [], [
                    'first_interaction' => $first,
                    'last_interaction'  => $last,
                ]),
            ];
        });
    }

    /**
     * Create an old interaction.
     */
    public function old(): static
    {
        return $this->state(function (array $attributes) {
            $first = fake()->dateTimeBetween('-1 year', '-6 months');
            $last = fake()->dateTimeBetween('-6 months', '-1 month');

            return [
                'occurred_at' => $last,
                'meta'        => array_merge($attributes['meta'] ?? [], [
                    'first_interaction' => $first,
                    'last_interaction'  => $last,
                ]),
            ];
        });
    }

    /**
     * Create a frequent interaction.
     */
    public function frequent(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => array_merge($attributes['meta'] ?? [], [
                'count' => fake()->numberBetween(20, 100),
            ]),
        ]);
    }

    /**
     * Create a rare interaction.
     */
    public function rare(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => array_merge($attributes['meta'] ?? [], [
                'count' => 1,
            ]),
        ]);
    }
}
