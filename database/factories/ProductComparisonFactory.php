<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductComparison>
 */
class ProductComparisonFactory extends Factory
{
    protected $model = ProductComparison::class;

    public function definition(): array
    {
        return [
            'session_id' => $this->faker->uuid(),
            'user_id' => null,
            'product_id' => Product::factory(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'session_id' => null,
        ]);
    }

    public function forSession(string $sessionId): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => $sessionId,
            'user_id' => null,
        ]);
    }
}
