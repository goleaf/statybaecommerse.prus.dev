<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductComparison>
 */
final class ProductComparisonFactory extends Factory
{
    protected $model = ProductComparison::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => $this->faker->unique()->uuid(),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
        ];
    }

    /**
     * Create a comparison for an anonymous session
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'session_id' => 'anon_'.$this->faker->uuid(),
        ]);
    }

    /**
     * Create a comparison for a specific session
     */
    public function forSession(string $sessionId): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Create a comparison for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a comparison for a specific product
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Create a recent comparison (within last 7 days)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create an old comparison (older than 30 days)
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }

    /**
     * Create multiple comparisons for the same session
     */
    public function sessionComparisons(int $count = 3): static
    {
        $sessionId = $this->faker->uuid();

        return $this->state(fn (array $attributes) => [
            'session_id' => $sessionId,
        ])->count($count);
    }

    /**
     * Create comparisons with specific session patterns
     */
    public function withSessionPattern(string $pattern): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => $pattern.'_'.$this->faker->randomNumber(5),
        ]);
    }

    /**
     * Create comparisons for mobile sessions
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => 'mobile_'.$this->faker->uuid(),
        ]);
    }

    /**
     * Create comparisons for desktop sessions
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => 'desktop_'.$this->faker->uuid(),
        ]);
    }

    /**
     * Create comparisons for API sessions
     */
    public function api(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => 'api_'.$this->faker->uuid(),
        ]);
    }
}
