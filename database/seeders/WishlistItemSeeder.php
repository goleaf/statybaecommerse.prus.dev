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
        $this->command->info('💝 Starting Wishlist Items seeding...');

        // Get or create users
        $users = $this->getOrCreateUsers();

        if ($users->isEmpty()) {
            $this->command->error('❌ No users available.');

            return;
        }

        // Create wishlists and items for random users
        $this->createUserWishlists($users);

        // Create specific test scenarios
        $this->createSpecificScenarios();

        $this->command->info('✅ Wishlist items seeded successfully!');
    }

    private function getOrCreateUsers()
    {
        $existingCount = User::where('is_admin', false)->count();

        if ($existingCount >= 5) {
            $this->command->info('✓ Using existing users');

            return User::where('is_admin', false)->inRandomOrder()->limit(5)->get();
        }

        $needed = 5 - $existingCount;
        $this->command->info("Creating {$needed} users...");

        User::factory()->count($needed)->create();

        return User::where('is_admin', false)->limit(5)->get();
    }

    private function createUserWishlists($users): void
    {
        $this->command->info('Creating wishlists for users...');

        $wishlistsCreated = 0;
        $itemsCreated = 0;

        $users->each(function (User $user) use (&$wishlistsCreated, &$itemsCreated): void {
            // Check if user already has wishlists
            $existingWishlists = UserWishlist::where('user_id', $user->id)->count();

            if ($existingWishlists >= 2) {
                $this->command->info("✓ User #{$user->id} already has {$existingWishlists} wishlists");

                return;
            }

            $needed = 2 - $existingWishlists;

            // Create wishlists for this user
            $wishlists = UserWishlist::factory()
                ->count($needed)
                ->for($user)
                ->create();

            $wishlistsCreated += $wishlists->count();

            $wishlists->each(function (UserWishlist $wishlist) use (&$itemsCreated): void {
                // Check if wishlist already has items
                $existingItems = WishlistItem::where('wishlist_id', $wishlist->id)->count();

                if ($existingItems >= 3) {
                    return;
                }

                $targetItems = fake()->numberBetween(3, 8);
                $needed = max(0, $targetItems - $existingItems);

                $created = WishlistItem::factory()
                    ->count($needed)
                    ->create([
                        'wishlist_id' => $wishlist->id,
                    ]);

                $itemsCreated += $created->count();
            });
        });

        if ($wishlistsCreated > 0) {
            $this->command->info("✓ Created {$wishlistsCreated} wishlists");
        }

        if ($itemsCreated > 0) {
            $this->command->info("✓ Created {$itemsCreated} wishlist items");
        }
    }

    /**
     * Create specific test scenarios for wishlist items
     */
    private function createSpecificScenarios(): void
    {
        $this->command->info('Creating specific test scenarios...');

        $scenariosCreated = [];

        // Scenario 1: Power user with many items
        if (! $this->scenarioExists('poweruser@example.com')) {
            $this->createPowerUserScenario();
            $scenariosCreated[] = 'Power user with 15+ items';
        } else {
            $this->command->info('✓ Power user scenario already exists');
        }

        // Scenario 2: Minimal user with empty wishlist
        if (! $this->scenarioExists('minimal@example.com')) {
            $this->createMinimalUserScenario();
            $scenariosCreated[] = 'Minimal user with empty wishlist';
        } else {
            $this->command->info('✓ Minimal user scenario already exists');
        }

        // Scenario 3: Organized user with multiple wishlists
        if (! $this->scenarioExists('organized@example.com')) {
            $this->createOrganizedUserScenario();
            $scenariosCreated[] = 'Organized user with 4 categorized wishlists';
        } else {
            $this->command->info('✓ Organized user scenario already exists');
        }

        // Scenario 4: Bulk buyer
        if (! $this->scenarioExists('bulk@example.com')) {
            $this->createBulkBuyerScenario();
            $scenariosCreated[] = 'Bulk buyer with high quantity items';
        } else {
            $this->command->info('✓ Bulk buyer scenario already exists');
        }

        if (! empty($scenariosCreated)) {
            $this->command->info('✅ Created wishlist items for various user scenarios:');
            foreach ($scenariosCreated as $scenario) {
                $this->command->info("  - {$scenario}");
            }
        }
    }

    private function scenarioExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    private function createPowerUserScenario(): void
    {
        $powerUser = User::create([
            'name'              => 'Power User',
            'email'             => 'poweruser@example.com',
            'email_verified_at' => now(),
            // Use a strong password so SecurePasswordHandling validates before hashing.
            'password'         => 'Admin123!',
            'preferred_locale' => 'en',
            'is_admin'         => false,
            'first_name'       => 'Power',
            'last_name'        => 'User',
            'is_active'        => true,
        ]);

        $powerUserWishlist = UserWishlist::factory()->create([
            'user_id'   => $powerUser->id,
            'name'      => 'Everything I Want',
            'is_public' => true,
        ]);

        // Use existing products instead of creating new ones
        $products = Product::inRandomOrder()->limit(15)->get();

        // If not enough products exist, get what we can
        if ($products->count() < 15) {
            $this->command->warn("Only {$products->count()} products available for power user wishlist");
        }

        foreach ($products as $product) {
            WishlistItem::factory()->create([
                'wishlist_id' => $powerUserWishlist->id,
                'product_id'  => $product->id,
                'quantity'    => rand(1, 5),
                'notes'       => 'Priority: ' . rand(1, 5),
            ]);
        }
    }

    private function createMinimalUserScenario(): void
    {
        $minimalUser = User::create([
            'name'              => 'Minimal User',
            'email'             => 'minimal@example.com',
            'email_verified_at' => now(),
            // Use a strong password so SecurePasswordHandling validates before hashing.
            'password'         => 'Admin123!',
            'preferred_locale' => 'en',
            'is_admin'         => false,
            'first_name'       => 'Minimal',
            'last_name'        => 'User',
            'is_active'        => true,
        ]);

        UserWishlist::factory()->create([
            'user_id'    => $minimalUser->id,
            'name'       => 'Empty Wishlist',
            'is_default' => true,
        ]);
    }

    private function createOrganizedUserScenario(): void
    {
        $organizedUser = User::create([
            'name'              => 'Organized User',
            'email'             => 'organized@example.com',
            'email_verified_at' => now(),
            // Use a strong password so SecurePasswordHandling validates before hashing.
            'password'         => 'Admin123!',
            'preferred_locale' => 'en',
            'is_admin'         => false,
            'first_name'       => 'Organized',
            'last_name'        => 'User',
            'is_active'        => true,
        ]);

        $wishlists = [
            'Electronics',
            'Books',
            'Clothing',
            'Home & Garden',
        ];

        foreach ($wishlists as $wishlistName) {
            $wishlist = UserWishlist::factory()->create([
                'user_id'     => $organizedUser->id,
                'name'        => $wishlistName,
                'description' => "My {$wishlistName} wishlist",
            ]);

            WishlistItem::factory()
                ->count(rand(2, 4))
                ->create([
                    'wishlist_id' => $wishlist->id,
                    'notes'       => "For {$wishlistName} collection",
                ]);
        }
    }

    private function createBulkBuyerScenario(): void
    {
        $bulkBuyer = User::create([
            'name'              => 'Bulk Buyer',
            'email'             => 'bulk@example.com',
            'email_verified_at' => now(),
            // Use a strong password so SecurePasswordHandling validates before hashing.
            'password'         => 'Admin123!',
            'preferred_locale' => 'en',
            'is_admin'         => false,
            'first_name'       => 'Bulk',
            'last_name'        => 'Buyer',
            'is_active'        => true,
        ]);

        $bulkWishlist = UserWishlist::factory()->create([
            'user_id' => $bulkBuyer->id,
            'name'    => 'Bulk Orders',
        ]);

        WishlistItem::factory()
            ->count(3)
            ->create([
                'wishlist_id' => $bulkWishlist->id,
                'quantity'    => rand(10, 50),
                'notes'       => 'Bulk order for business',
            ]);
    }
}
