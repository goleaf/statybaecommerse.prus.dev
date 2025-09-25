<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * UserBehaviorSeeder
 *
 * Seeder for creating realistic user behavior data for testing and development.
 */
final class UserBehaviorSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating user behaviors...');

        // Get existing users, products, and categories
        $categories = Category::factory()->count(8)->create();
        $products = Product::factory()
            ->count(30)
            ->hasAttached($categories->random(3))
            ->create();
        $users = User::factory()
            ->count(10)
            ->hasUserBehaviors(50)
            ->create();

        $this->createViewBehaviors($users, $products, $categories);
        $this->createClickBehaviors($users, $products, $categories);
        $this->createAddToCartBehaviors($users, $products, $categories);
        $this->createPurchaseBehaviors($users, $products, $categories);
        $this->createSearchBehaviors($users, $categories);
        $this->createWishlistBehaviors($users, $products);
        $this->createFilterBehaviors($users, $categories);

        $this->command->info('User behaviors created successfully!');
    }

    private function createViewBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating view behaviors...');

        // Create view behaviors for the last 30 days
        for ($i = 0; $i < 500; $i++) {
            UserBehavior::factory()
                ->for($users->random())
                ->for($products->random())
                ->for($categories->random())
                ->view()
                ->create();
        }
    }

    private function createClickBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating click behaviors...');

        // Create click behaviors (usually follow views)
        UserBehavior::factory()
            ->count(200)
            ->click()
            ->create();
    }

    private function createAddToCartBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating add to cart behaviors...');

        // Create add to cart behaviors (conversion from views/clicks)
        UserBehavior::factory()
            ->count(150)
            ->state(function () use ($users, $products, $categories) {
                return [
                    'user_id' => $users->random()->id,
                    'product_id' => $products->random()->id,
                    'category_id' => $categories->random()->id,
                    'behavior_type' => 'add_to_cart',
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ];
            })
            ->create();
    }

    private function createPurchaseBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating purchase behaviors...');

        // Create purchase behaviors (final conversion)
        UserBehavior::factory()
            ->count(100)
            ->state(function () use ($users, $products, $categories) {
                return [
                    'user_id' => $users->random()->id,
                    'product_id' => $products->random()->id,
                    'category_id' => $categories->random()->id,
                    'behavior_type' => 'purchase',
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ];
            })
            ->create();
    }

    private function createSearchBehaviors(Collection $users, Collection $categories): void
    {
        $this->command->info('Creating search behaviors...');

        // Create search behaviors
        UserBehavior::factory()
            ->count(300)
            ->state(function () use ($users, $categories) {
                return [
                    'user_id' => $users->random()->id,
                    'category_id' => $categories->random()->id,
                    'behavior_type' => 'search',
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ];
            })
            ->create();
    }

    private function createWishlistBehaviors(Collection $users, Collection $products): void
    {
        $this->command->info('Creating wishlist behaviors...');

        // Create wishlist behaviors
        UserBehavior::factory()
            ->count(80)
            ->state(function () use ($users, $products) {
                return [
                    'user_id' => $users->random()->id,
                    'product_id' => $products->random()->id,
                    'behavior_type' => 'wishlist',
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ];
            })
            ->create();
    }

    private function createFilterBehaviors(Collection $users, Collection $categories): void
    {
        $this->command->info('Creating filter behaviors...');

        // Create filter behaviors
        UserBehavior::factory()
            ->count(120)
            ->state(function () use ($users, $categories) {
                return [
                    'user_id' => $users->random()->id,
                    'category_id' => $categories->random()->id,
                    'behavior_type' => 'filter',
                    'metadata' => [
                        'filters_applied' => fake()->randomElements(['price', 'brand', 'color', 'size'], fake()->numberBetween(1, 3)),
                        'page_url' => fake()->url(),
                        'page_title' => fake()->sentence(3),
                    ],
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ];
            })
            ->create();
    }
}
