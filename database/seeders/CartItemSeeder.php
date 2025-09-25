<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have users and products to work with
        $users = User::query()->limit(10)->get();
        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
        }

        $products = Product::query()->limit(50)->get();
        if ($products->isEmpty()) {
            $products = Product::factory()->count(20)->create();
        }

        // Create cart items for registered users using factory relationships
        $users->each(function (User $user, int $index): void {
            $sessionId = 'session-' . ($index + 1);

            // Create 1-3 cart items per user
            CartItem::factory()
                ->count(fake()->numberBetween(1, 3))
                ->for($user)
                ->for(Product::query()->inRandomOrder()->first())
                ->state([
                    'session_id' => $sessionId,
                ])
                ->create();
        });

        // Create guest cart items using factory
        collect(range(1, 5))->each(function (int $i): void {
            $sessionId = 'guest-session-' . $i;

            CartItem::factory()
                ->count(fake()->numberBetween(1, 2))
                ->for(Product::query()->inRandomOrder()->first())
                ->state([
                    'session_id' => $sessionId,
                    'user_id' => null,  // Guest cart
                ])
                ->create();
        });
    }
}
