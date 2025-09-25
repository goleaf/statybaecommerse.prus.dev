<?php declare(strict_types=1);

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
        // Use existing users and create wishlists for them
        $users = User::query()->inRandomOrder()->limit(5)->get();

        if ($users->isEmpty()) {
            // Create users if none exist
            $users = User::factory()->count(5)->create();
        }

        $users->each(function (User $user): void {
            // Create wishlists for each user
            $wishlists = UserWishlist::factory()
                ->count(2)
                ->for($user)
                ->create();

            $wishlists->each(function (UserWishlist $wishlist): void {
                WishlistItem::factory()
                    ->count(fake()->numberBetween(3, 8))
                    ->create([
                        'wishlist_id' => $wishlist->id,
                    ]);
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
        $powerUser = User::firstOrCreate(
            ['email' => 'poweruser@example.com'],
            [
                'name' => 'Power User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'preferred_locale' => 'en',
                'is_admin' => false,
            ]
        );

        $powerUserWishlist = UserWishlist::factory()->create([
            'user_id' => $powerUser->id,
            'name' => 'Everything I Want',
            'is_public' => true,
        ]);

        $products = Product::factory()->count(15)->create();
        foreach ($products as $product) {
            WishlistItem::factory()
                ->create([
                    'wishlist_id' => $powerUserWishlist->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 5),
                    'notes' => 'Priority: ' . rand(1, 5),
                ]);
        }

        // Scenario 2: User with empty wishlist
        $minimalUser = User::firstOrCreate(
            ['email' => 'minimal@example.com'],
            [
                'name' => 'Minimal User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'preferred_locale' => 'en',
                'is_admin' => false,
            ]
        );

        UserWishlist::factory()->create([
            'user_id' => $minimalUser->id,
            'name' => 'Empty Wishlist',
            'is_default' => true,
        ]);

        // Scenario 3: User with multiple wishlists
        $organizedUser = User::firstOrCreate(
            ['email' => 'organized@example.com'],
            [
                'name' => 'Organized User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'preferred_locale' => 'en',
                'is_admin' => false,
            ]
        );

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
                ->create([
                    'wishlist_id' => $wishlist->id,
                    'notes' => "For {$wishlistName} collection",
                ]);
        }

        // Scenario 4: Items with high quantities
        $bulkBuyer = User::firstOrCreate(
            ['email' => 'bulk@example.com'],
            [
                'name' => 'Bulk Buyer',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'preferred_locale' => 'en',
                'is_admin' => false,
            ]
        );

        $bulkWishlist = UserWishlist::factory()->create([
            'user_id' => $bulkBuyer->id,
            'name' => 'Bulk Orders',
        ]);

        WishlistItem::factory()
            ->count(3)
            ->create([
                'wishlist_id' => $bulkWishlist->id,
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
