<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserWishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\UserWishlist>
 */
class UserWishlistFactory extends Factory
{
    protected $model = UserWishlist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(2, true) . ' Wishlist',
            'description' => $this->faker->optional()->sentence(),
            'is_public' => $this->faker->boolean(30),
            'is_default' => false,
        ];
    }

    public function public(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_public' => false,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
            'name' => 'My Wishlist',
        ]);
    }
}
