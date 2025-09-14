<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductRequest>
 */
final class ProductRequestFactory extends Factory
{
    protected $model = ProductRequest::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'message' => $this->faker->paragraph(),
            'requested_quantity' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'admin_notes' => $this->faker->optional()->paragraph(),
            'responded_at' => $this->faker->optional()->dateTime(),
            'responded_by' => $this->faker->optional()->randomElement(User::pluck('id')->toArray()),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'responded_at' => null,
            'responded_by' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'responded_at' => now(),
            'responded_by' => User::factory(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'responded_at' => now(),
            'responded_by' => User::factory(),
        ]);
    }

    public function responded(): static
    {
        return $this->state(fn (array $attributes) => [
            'responded_at' => now(),
            'responded_by' => User::factory(),
        ]);
    }

    public function unresponded(): static
    {
        return $this->state(fn (array $attributes) => [
            'responded_at' => null,
            'responded_by' => null,
        ]);
    }
}
