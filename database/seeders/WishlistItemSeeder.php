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
        // Create some test users
        $users = User::factory(5)->create();

        foreach ($users as $user) {
            // Create a default wishlist for each user
            $wishlist = UserWishlist::factory()->create([
                'user_id' => $user->id,
                'name' => 'My Wishlist',
                'is_default' => true,
            ]);

            // Create additional wishlists for some users
            if (rand(0, 1)) {
                UserWishlist::factory()->create([
                    'user_id' => $user->id,
                    'name' => 'Birthday Wishlist',
                    'description' => 'Items I want for my birthday',
                ]);
            }

            // Add some products to the wishlist
            $products = Product::inRandomOrder()->limit(rand(3, 8))->get();

            foreach ($products as $product) {
                $quantity = rand(1, 3);
                $notes = null;

                // Add notes for some items
                if (rand(0, 1)) {
                    $notes = collect([
                        'Really want this!',
                        'For next month',
                        'Gift for someone',
                        'Need to check reviews first',
                        'Waiting for sale',
                        'High priority',
                    ])->random();
                }

                // Check if product has variants
                if ($product->variants()->exists()) {
                    $variant = $product->variants()->inRandomOrder()->first();

                    WishlistItem::create([
                        'wishlist_id' => $wishlist->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => $quantity,
                        'notes' => $notes,
                    ]);
                } else {
                    WishlistItem::create([
                        'wishlist_id' => $wishlist->id,
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'quantity' => $quantity,
                        'notes' => $notes,
                    ]);
                }
            }
        }

        // Create some wishlist items with specific scenarios
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

        $products = Product::inRandomOrder()->limit(15)->get();
        foreach ($products as $product) {
            WishlistItem::create([
                'wishlist_id' => $powerUserWishlist->id,
                'product_id' => $product->id,
                'variant_id' => $product->variants()->exists() ? $product->variants()->inRandomOrder()->first()->id : null,
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

            // Add 2-4 items to each wishlist
            $products = Product::inRandomOrder()->limit(rand(2, 4))->get();
            foreach ($products as $product) {
                WishlistItem::create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => 1,
                    'notes' => "For {$wishlistName} collection",
                ]);
            }
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

        $products = Product::inRandomOrder()->limit(3)->get();
        foreach ($products as $product) {
            WishlistItem::create([
                'wishlist_id' => $bulkWishlist->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'quantity' => rand(10, 50),
                'notes' => 'Bulk order for business',
            ]);
        }

        $this->command->info('Wishlist items seeded successfully!');
        $this->command->info('Created wishlist items for various user scenarios:');
        $this->command->info('- Power user with 15+ items');
        $this->command->info('- Minimal user with empty wishlist');
        $this->command->info('- Organized user with 4 categorized wishlists');
        $this->command->info('- Bulk buyer with high quantity items');
    }
}
