<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReferralCode>
 */
final class ReferralCodeFactory extends Factory
{
    protected $model = ReferralCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('??????')),
            'is_active' => $this->faker->boolean(80),
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'metadata' => $this->faker->optional(0.2)->randomElements([
                'generated_via' => $this->faker->randomElement(['manual', 'automatic', 'api']),
                'usage_count' => $this->faker->numberBetween(0, 50),
                'last_used_at' => $this->faker->optional(0.4)->dateTimeBetween('-6 months', 'now'),
            ]),
        ];
    }

    /**
     * Indicate that the code is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the code is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the code is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the code never expires.
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * Indicate that the code expires soon.
     */
    public function expiresSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('now', '+7 days'),
        ]);
    }

    /**
     * Create a code with a specific code string.
     */
    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}