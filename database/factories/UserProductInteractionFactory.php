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
        $interactionTypes = ['view', 'click', 'add_to_cart', 'purchase', 'review', 'share'];
        $interactionType = fake()->randomElement($interactionTypes);

        $firstInteraction = fake()->dateTimeBetween('-6 months', '-1 month');
        $lastInteraction = fake()->dateTimeBetween($firstInteraction, 'now');

        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'interaction_type' => $interactionType,
            'rating' => $interactionType === 'review' ? fake()->randomFloat(1, 1, 5) : null,
            'count' => fake()->numberBetween(1, 20),
            'first_interaction' => $firstInteraction,
            'last_interaction' => $lastInteraction,
        ];
    }

    /**
     * Create a view interaction.
     */
    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'view',
            'rating' => null,
            'count' => fake()->numberBetween(1, 50),
        ]);
    }

    /**
     * Create a click interaction.
     */
    public function click(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'click',
            'rating' => null,
            'count' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Create an add to cart interaction.
     */
    public function addToCart(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'add_to_cart',
            'rating' => null,
            'count' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Create a purchase interaction.
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'purchase',
            'rating' => null,
            'count' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Create a review interaction.
     */
    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'review',
            'rating' => fake()->randomFloat(1, 1, 5),
            'count' => 1,
        ]);
    }

    /**
     * Create a share interaction.
     */
    public function share(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'share',
            'rating' => null,
            'count' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Create a high-rated interaction.
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 4, 5),
        ]);
    }

    /**
     * Create a low-rated interaction.
     */
    public function lowRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->randomFloat(1, 1, 3),
        ]);
    }

    /**
     * Create a recent interaction.
     */
    public function recent(): static
    {
        $now = now();

        return $this->state(fn (array $attributes) => [
            'first_interaction' => fake()->dateTimeBetween('-7 days', '-1 day'),
            'last_interaction' => fake()->dateTimeBetween('-1 day', $now),
        ]);
    }

    /**
     * Create an old interaction.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_interaction' => fake()->dateTimeBetween('-1 year', '-6 months'),
            'last_interaction' => fake()->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    /**
     * Create a frequent interaction.
     */
    public function frequent(): static
    {
        return $this->state(fn (array $attributes) => [
            'count' => fake()->numberBetween(20, 100),
        ]);
    }

    /**
     * Create a rare interaction.
     */
    public function rare(): static
    {
        return $this->state(fn (array $attributes) => [
            'count' => 1,
        ]);
    }
}
