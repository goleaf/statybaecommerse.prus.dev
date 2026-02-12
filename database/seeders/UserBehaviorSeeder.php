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
final class UserBehaviorSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->command->info('Creating user behaviors...');

        // Use existing data instead of creating new ones to avoid conflicts
        $categories = $this->getOrCreateCategories();
        $products = $this->getOrCreateProducts($categories);
        $users = $this->getOrCreateUsers();

        // Only create behaviors if we have the necessary data
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');

            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('No products found. Please seed products first.');

            return;
        }

        if ($categories->isEmpty()) {
            $this->command->error('No categories found. Please seed categories first.');

            return;
        }

        // Each method checks its own completion status and skips if already done
        $this->createViewBehaviors($users, $products, $categories);
        $this->createClickBehaviors($users, $products, $categories);
        $this->createAddToCartBehaviors($users, $products, $categories);
        $this->createPurchaseBehaviors($users, $products, $categories);
        $this->createSearchBehaviors($users, $categories);
        $this->createWishlistBehaviors($users, $products);
        $this->createFilterBehaviors($users, $categories);

        $this->command->info('✅ User behaviors created successfully!');
    }

    private function getOrCreateCategories(): Collection
    {
        $existingCount = Category::count();

        if ($existingCount >= 8) {
            $this->command->info("✓ Using {$existingCount} existing categories");

            return Category::limit(8)->get();
        }

        $needed = 8 - $existingCount;
        $this->command->info("Creating {$needed} categories...");

        Category::factory()->count($needed)->create();

        return Category::limit(8)->get();
    }

    private function getOrCreateProducts(Collection $categories): Collection
    {
        $existingCount = Product::count();

        if ($existingCount >= 30) {
            $this->command->info("✓ Using {$existingCount} existing products");
            $products = Product::limit(30)->get();

            // Ensure products have category relationships
            $products->each(function (Product $product) use ($categories) {
                if ($product->categories()->count() === 0) {
                    $product->categories()->attach($categories->random(min(3, $categories->count())));
                }
            });

            return $products;
        }

        $needed = 30 - $existingCount;
        $this->command->info("Creating {$needed} products...");

        $newProducts = Product::factory()->count($needed)->create();
        $newProducts->each(fn (Product $product) => $product->categories()->attach($categories->random(min(3, $categories->count()))));

        return Product::limit(30)->get();
    }

    private function getOrCreateUsers(): Collection
    {
        $existingCount = User::where('is_admin', false)->count();

        if ($existingCount >= 10) {
            $this->command->info("✓ Using {$existingCount} existing users");

            return User::where('is_admin', false)->limit(10)->get();
        }

        $needed = 10 - $existingCount;
        $this->command->info("Creating {$needed} users...");

        User::factory()->count($needed)->create();

        return User::where('is_admin', false)->limit(10)->get();
    }

    private function createViewBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $existingCount = UserBehavior::where('action', 'view')->count();

        if ($existingCount >= 500) {
            $this->command->info("✓ View behaviors already complete ({$existingCount}/500)");

            return;
        }

        $needed = 500 - $existingCount;
        $this->command->info("Creating {$needed} view behaviors...");

        UserBehavior::factory()
            ->count($needed)
            ->view()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();

        $this->command->info('✓ View behaviors created');
    }

    private function createClickBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $existingCount = UserBehavior::where('action', 'click')->count();

        if ($existingCount >= 200) {
            $this->command->info("✓ Click behaviors already complete ({$existingCount}/200)");

            return;
        }

        $needed = 200 - $existingCount;
        $this->command->info("Creating {$needed} click behaviors...");

        UserBehavior::factory()
            ->count($needed)
            ->click()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();

        $this->command->info('✓ Click behaviors created');
    }

    private function createAddToCartBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $existingCount = UserBehavior::where('action', 'add_to_cart')->count();

        if ($existingCount >= 150) {
            $this->command->info("✓ Add to cart behaviors already complete ({$existingCount}/150)");

            return;
        }

        $needed = 150 - $existingCount;
        $this->command->info("Creating {$needed} add to cart behaviors...");

        UserBehavior::factory()
            ->count($needed)
            ->addToCart()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();

        $this->command->info('✓ Add to cart behaviors created');
    }

    private function createPurchaseBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $existingCount = UserBehavior::where('action', 'purchase')->count();

        if ($existingCount >= 100) {
            $this->command->info("✓ Purchase behaviors already complete ({$existingCount}/100)");

            return;
        }

        $needed = 100 - $existingCount;
        $this->command->info("Creating {$needed} purchase behaviors...");

        UserBehavior::factory()
            ->count($needed)
            ->purchase()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();

        $this->command->info('✓ Purchase behaviors created');
    }

    private function createSearchBehaviors(Collection $users, Collection $categories): void
    {
        $existingCount = UserBehavior::where('action', 'search')->count();

        if ($existingCount >= 300) {
            $this->command->info("✓ Search behaviors already complete ({$existingCount}/300)");

            return;
        }

        $needed = 300 - $existingCount;
        $this->command->info("Creating {$needed} search behaviors...");

        UserBehavior::factory()
            ->count($needed)
            ->search()
            ->recycle($users)
            ->recycle($categories)
            ->create();

        $this->command->info('✓ Search behaviors created');
    }

    private function createWishlistBehaviors(Collection $users, Collection $products): void
    {
        $existingCount = UserBehavior::where('action', 'wishlist')->count();

        if ($existingCount >= 80) {
            $this->command->info("✓ Wishlist behaviors already complete ({$existingCount}/80)");

            return;
        }

        $needed = 80 - $existingCount;
        $this->command->info("Creating {$needed} wishlist behaviors...");

        UserBehavior::factory()
            ->count($needed)
            ->wishlist()
            ->recycle($users)
            ->recycle($products)
            ->create();

        $this->command->info('✓ Wishlist behaviors created');
    }

    private function createFilterBehaviors(Collection $users, Collection $categories): void
    {
        $existingCount = UserBehavior::where('action', 'filter')->count();

        if ($existingCount >= 120) {
            $this->command->info("✓ Filter behaviors already complete ({$existingCount}/120)");

            return;
        }

        $needed = 120 - $existingCount;
        $this->command->info("Creating {$needed} filter behaviors...");

        UserBehavior::factory()
            ->count($needed)
            ->filter()
            ->recycle($users)
            ->recycle($categories)
            ->create();

        $this->command->info('✓ Filter behaviors created');
    }
}
