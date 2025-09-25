<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;

class WishlistItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->has(UserWishlist::factory()->count(2))
            ->create()
            ->each(function (User $user): void {
                $user->wishlists->each(function (UserWishlist $wishlist): void {
                    WishlistItem::factory()
                        ->count(fake()->numberBetween(3, 8))
                        ->for($wishlist)
                        ->create();
                });
            });

        $this->createSpecificScenarios();
    }

    /**
     * Create specific test scenarios for wishlist items
     */
    private function createSpecificScenarios(): void
    {
        // Scenario 1: User with many items in wishlist
        $powerUser = User::factory()->create([
            'name' => 'Power User',
            'email' => 'poweruser@example.com',
        ]);

        $powerUserWishlist = UserWishlist::factory()->create([
            'user_id' => $powerUser->id,
            'name' => 'Everything I Want',
            'is_public' => true,
        ]);

        $products = Product::factory()->count(15)->create();
        foreach ($products as $product) {
            WishlistItem::factory()
                ->for($powerUserWishlist)
                ->for($product)
                ->create([
                    'quantity' => rand(1, 5),
                    'notes' => 'Priority: '.rand(1, 5),
                ]);
        }

        // Scenario 2: User with empty wishlist
        $minimalUser = User::factory()->create([
            'name' => 'Minimal User',
            'email' => 'minimal@example.com',
        ]);

        UserWishlist::factory()->create([
            'user_id' => $minimalUser->id,
            'name' => 'Empty Wishlist',
            'is_default' => true,
        ]);

        // Scenario 3: User with multiple wishlists
        $organizedUser = User::factory()->create([
            'name' => 'Organized User',
            'email' => 'organized@example.com',
        ]);

        $wishlists = [
            'Electronics',
            'Books',
            'Clothing',
            'Home & Garden',
        ];

        foreach ($wishlists as $wishlistName) {
            $wishlist = UserWishlist::factory()->create([
                'user_id' => $organizedUser->id,
                'name' => $wishlistName,
                'description' => "My {$wishlistName} wishlist",
            ]);

            WishlistItem::factory()
                ->count(rand(2, 4))
                ->for($wishlist)
                ->create([
                    'notes' => "For {$wishlistName} collection",
                ]);
        }

        // Scenario 4: Items with high quantities
        $bulkBuyer = User::factory()->create([
            'name' => 'Bulk Buyer',
            'email' => 'bulk@example.com',
        ]);

        $bulkWishlist = UserWishlist::factory()->create([
            'user_id' => $bulkBuyer->id,
            'name' => 'Bulk Orders',
        ]);

        WishlistItem::factory()
            ->count(3)
            ->for($bulkWishlist)
            ->create([
                'quantity' => rand(10, 50),
                'notes' => 'Bulk order for business',
            ]);

        $this->command->info('Wishlist items seeded successfully!');
        $this->command->info('Created wishlist items for various user scenarios:');
        $this->command->info('- Power user with 15+ items');
        $this->command->info('- Minimal user with empty wishlist');
        $this->command->info('- Organized user with 4 categorized wishlists');
        $this->command->info('- Bulk buyer with high quantity items');
    }
}
