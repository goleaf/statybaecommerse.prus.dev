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
        $users = User::all();
        $products = Product::all();
        $categories = Category::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Creating sample users...');
            $users = User::factory(10)->create();
        }

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Creating sample products...');
            $products = Product::factory(20)->create();
        }

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Creating sample categories...');
            $categories = Category::factory(5)->create();
        }

        // Create realistic user behavior patterns
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
                ->view()
                ->forUser($users->random())
                ->forProduct($products->random())
                ->forCategory($categories->random())
                ->create([
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
        }
    }

    private function createClickBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating click behaviors...');

        // Create click behaviors (usually follow views)
        for ($i = 0; $i < 200; $i++) {
            UserBehavior::factory()
                ->click()
                ->forUser($users->random())
                ->forProduct($products->random())
                ->forCategory($categories->random())
                ->create([
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
        }
    }

    private function createAddToCartBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating add to cart behaviors...');

        // Create add to cart behaviors (conversion from views/clicks)
        for ($i = 0; $i < 150; $i++) {
            UserBehavior::factory()
                ->addToCart()
                ->forUser($users->random())
                ->forProduct($products->random())
                ->forCategory($categories->random())
                ->create([
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
        }
    }

    private function createPurchaseBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating purchase behaviors...');

        // Create purchase behaviors (final conversion)
        for ($i = 0; $i < 100; $i++) {
            UserBehavior::factory()
                ->purchase()
                ->forUser($users->random())
                ->forProduct($products->random())
                ->forCategory($categories->random())
                ->create([
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
        }
    }

    private function createSearchBehaviors(Collection $users, Collection $categories): void
    {
        $this->command->info('Creating search behaviors...');

        // Create search behaviors
        for ($i = 0; $i < 300; $i++) {
            UserBehavior::factory()
                ->search()
                ->forUser($users->random())
                ->forCategory($categories->random())
                ->create([
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
        }
    }

    private function createWishlistBehaviors(Collection $users, Collection $products): void
    {
        $this->command->info('Creating wishlist behaviors...');

        // Create wishlist behaviors
        for ($i = 0; $i < 80; $i++) {
            UserBehavior::factory()
                ->forUser($users->random())
                ->forProduct($products->random())
                ->create([
                    'behavior_type' => 'wishlist',
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
        }
    }

    private function createFilterBehaviors(Collection $users, Collection $categories): void
    {
        $this->command->info('Creating filter behaviors...');

        // Create filter behaviors
        for ($i = 0; $i < 120; $i++) {
            UserBehavior::factory()
                ->forUser($users->random())
                ->forCategory($categories->random())
                ->create([
                    'behavior_type' => 'filter',
                    'metadata' => [
                        'filters_applied' => fake()->randomElements(['price', 'brand', 'color', 'size'], fake()->numberBetween(1, 3)),
                        'page_url' => fake()->url(),
                        'page_title' => fake()->sentence(3),
                    ],
                    'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                ]);
        }
    }
}
