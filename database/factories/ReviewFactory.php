<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'reviewer_name' => $this->faker->name(),
            'reviewer_email' => $this->faker->email(),
            'rating' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'is_approved' => $this->faker->boolean(80),
        ];
    }

    public function approved(): static
    {
        return $this->state(['is_approved' => true]);
    }

    public function pending(): static
    {
        return $this->state(['is_approved' => false]);
    }
}