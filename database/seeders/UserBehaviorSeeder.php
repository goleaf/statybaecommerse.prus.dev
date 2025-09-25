<?php declare(strict_types=1);

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
        $users = User::factory()->count(10)->create();

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

        UserBehavior::factory()
            ->count(500)
            ->view()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();
    }

    private function createClickBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating click behaviors...');

        UserBehavior::factory()
            ->count(200)
            ->click()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();
    }

    private function createAddToCartBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating add to cart behaviors...');

        UserBehavior::factory()
            ->count(150)
            ->addToCart()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();
    }

    private function createPurchaseBehaviors(Collection $users, Collection $products, Collection $categories): void
    {
        $this->command->info('Creating purchase behaviors...');

        UserBehavior::factory()
            ->count(100)
            ->purchase()
            ->recycle($users)
            ->recycle($products)
            ->recycle($categories)
            ->create();
    }

    private function createSearchBehaviors(Collection $users, Collection $categories): void
    {
        $this->command->info('Creating search behaviors...');

        UserBehavior::factory()
            ->count(300)
            ->search()
            ->recycle($users)
            ->recycle($categories)
            ->create();
    }

    private function createWishlistBehaviors(Collection $users, Collection $products): void
    {
        $this->command->info('Creating wishlist behaviors...');

        UserBehavior::factory()
            ->count(80)
            ->state(fn () => [
                'behavior_type' => 'wishlist',
            ])
            ->recycle($users)
            ->recycle($products)
            ->create();
    }

    private function createFilterBehaviors(Collection $users, Collection $categories): void
    {
        $this->command->info('Creating filter behaviors...');

        UserBehavior::factory()
            ->count(120)
            ->state(fn () => [
                'behavior_type' => 'filter',
                'metadata' => [
                    'filters_applied' => fake()->randomElements(['price', 'brand', 'color', 'size'], fake()->numberBetween(1, 3)),
                    'page_url' => fake()->url(),
                    'page_title' => fake()->sentence(3),
                ],
            ])
            ->recycle($users)
            ->recycle($categories)
            ->create();
    }
}
