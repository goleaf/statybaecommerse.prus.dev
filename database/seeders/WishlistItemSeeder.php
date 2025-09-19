<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
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
        // Get existing users and products
        $users = User::all();
        $products = Product::with('variants')->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        $this->command->info('Creating wishlist items...');

        // Create wishlists for users if they don't exist
        foreach ($users as $user) {
            if (!$user->wishlists()->exists()) {
                UserWishlist::factory()->create([
                    'user_id' => $user->id,
                    'name' => 'My Wishlist',
                    'description' => 'Default wishlist',
                    'is_public' => false,
                    'is_default' => true,
                ]);
            }
        }

        // Create wishlist items
        $wishlistItemsCount = 0;
        $maxItemsPerUser = 10;

        foreach ($users as $user) {
            $userWishlists = $user->wishlists;

            foreach ($userWishlists as $wishlist) {
                // Random number of items per wishlist (1-5)
                $itemsCount = rand(1, 5);

                for ($i = 0; $i < $itemsCount; $i++) {
                    $product = $products->random();

                    // Decide whether to use a variant or not
                    $variant = null;
                    if ($product->variants->isNotEmpty() && rand(0, 1)) {
                        $variant = $product->variants->random();
                    }

                    // Create wishlist item
                    WishlistItem::factory()->create([
                        'wishlist_id' => $wishlist->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant?->id,
                        'quantity' => rand(1, 3),
                        'notes' => $this->generateRandomNotes(),
                    ]);

                    $wishlistItemsCount++;
                }
            }
        }

        $this->command->info("Created {$wishlistItemsCount} wishlist items.");

        // Create some public wishlists
        $this->createPublicWishlists($users, $products);
    }

    /**
     * Create some public wishlists with items
     */
    private function createPublicWishlists($users, $products): void
    {
        $this->command->info('Creating public wishlists...');

        $publicWishlistCount = 0;
        $maxPublicWishlists = 5;

        foreach ($users->take($maxPublicWishlists) as $user) {
            $wishlist = UserWishlist::factory()->create([
                'user_id' => $user->id,
                'name' => $this->generateWishlistName(),
                'description' => $this->generateWishlistDescription(),
                'is_public' => true,
                'is_default' => false,
            ]);

            // Add 3-8 items to public wishlist
            $itemsCount = rand(3, 8);
            for ($i = 0; $i < $itemsCount; $i++) {
                $product = $products->random();
                $variant = null;

                if ($product->variants->isNotEmpty() && rand(0, 1)) {
                    $variant = $product->variants->random();
                }

                WishlistItem::factory()->create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => rand(1, 2),
                    'notes' => $this->generateRandomNotes(),
                ]);
            }

            $publicWishlistCount++;
        }

        $this->command->info("Created {$publicWishlistCount} public wishlists.");
    }

    /**
     * Generate random wishlist names
     */
    private function generateWishlistName(): string
    {
        $names = [
            'Christmas Wishlist',
            'Birthday Wishes',
            'Home Improvement',
            'Electronics',
            'Fashion & Style',
            'Sports & Fitness',
            'Books & Reading',
            'Garden & Outdoor',
            'Kitchen & Cooking',
            'Travel Essentials',
            'Gaming Setup',
            'Home Office',
            'Beauty & Skincare',
            'Kids & Toys',
            'Pet Supplies',
        ];

        return $names[array_rand($names)];
    }

    /**
     * Generate random wishlist descriptions
     */
    private function generateWishlistDescription(): string
    {
        $descriptions = [
            'A curated collection of items I would love to have.',
            'Things that caught my eye while browsing.',
            'My personal wishlist for special occasions.',
            'Items I am considering for future purchases.',
            'A mix of practical and fun items.',
            'Products that would make great gifts.',
            'Things I need for my hobbies and interests.',
            'A collection of quality products I admire.',
            'Items for home improvement and decoration.',
            'Products that would enhance my daily life.',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Generate random notes for wishlist items
     */
    private function generateRandomNotes(): ?string
    {
        $notes = [
            null,  // 50% chance of no notes
            'Need this for my project',
            'Great gift idea',
            'High priority',
            'Check reviews first',
            'Compare prices',
            'Perfect for the occasion',
            'Love this design',
            'Need to save up for this',
            'Research alternatives',
            'Check availability',
            'Consider for next purchase',
            'Great quality brand',
            'Recommended by friends',
            'Perfect size and color',
        ];

        return $notes[array_rand($notes)];
    }
}
