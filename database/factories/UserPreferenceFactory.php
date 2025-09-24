<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
final class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $preferenceTypes = [
            'category',
            'brand',
            'price_range',
            'color',
            'size',
            'material',
            'style',
            'feature',
        ];

        $preferenceKeys = [
            'electronics',
            'apple',
            '100-500',
            'black',
            'large',
            'cotton',
            'modern',
            'wireless',
        ];

        return [
            'user_id' => User::factory(),
            'preference_type' => fake()->randomElement($preferenceTypes),
            'preference_key' => fake()->randomElement($preferenceKeys),
            'preference_score' => fake()->randomFloat(6, 0, 1),
            'last_updated' => fake()->dateTimeBetween('-30 days', 'now'),
            'metadata' => [
                'source' => fake()->randomElement(['purchase_history', 'browsing', 'search', 'recommendation']),
                'frequency' => fake()->randomElement(['low', 'medium', 'high']),
                'confidence' => fake()->randomFloat(2, 0.5, 1.0),
            ],
        ];
    }

    /**
     * Create a preference for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a category preference.
     */
    public function category(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_type' => 'category',
            'preference_key' => fake()->randomElement(['electronics', 'clothing', 'books', 'home', 'sports']),
        ]);
    }

    /**
     * Create a brand preference.
     */
    public function brand(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_type' => 'brand',
            'preference_key' => fake()->randomElement(['apple', 'samsung', 'nike', 'adidas', 'sony']),
        ]);
    }

    /**
     * Create a price range preference.
     */
    public function priceRange(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_type' => 'price_range',
            'preference_key' => fake()->randomElement(['0-50', '50-100', '100-500', '500-1000', '1000+']),
        ]);
    }

    /**
     * Create a high score preference.
     */
    public function highScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_score' => fake()->randomFloat(6, 0.7, 1.0),
        ]);
    }

    /**
     * Create a low score preference.
     */
    public function lowScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_score' => fake()->randomFloat(6, 0.0, 0.3),
        ]);
    }

    /**
     * Create a recent preference.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_updated' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create an old preference.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_updated' => fake()->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }

    /**
     * Create a preference with custom metadata.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }
}
