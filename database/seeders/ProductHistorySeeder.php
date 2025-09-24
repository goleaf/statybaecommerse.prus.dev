<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ProductHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::limit(20)->get();
        $users = User::limit(10)->get();

        if ($products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No products or users found. Please run ProductSeeder and UserSeeder first.');

            return;
        }

        // Create different types of product history entries
        $this->createProductLifecycleHistories($products, $users);
        $this->createPriceChangeHistories($products, $users);
        $this->createStockUpdateHistories($products, $users);
        $this->createStatusChangeHistories($products, $users);
        $this->createCategoryChangeHistories($products, $users);
        $this->createImageChangeHistories($products, $users);
        $this->createCustomHistories($products, $users);
    }

    /**
     * Create product lifecycle histories (created, updated, deleted, restored)
     */
    private function createProductLifecycleHistories($products, $users): void
    {
        foreach ($products->take(10) as $product) {
            $user = $users->random();

            // Product creation
            ProductHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'causer_type' => 'App\\Models\\User',
                'causer_id' => $user->id,
                'action' => 'created',
                'field_name' => 'name',
                'old_value' => null,
                'new_value' => $product->name,
                'description' => 'Product was created in the system',
                'ip_address' => $this->generateIpAddress(),
                'user_agent' => $this->generateUserAgent(),
                'metadata' => [
                    'source' => 'admin_panel',
                    'version' => '1.0',
                ],
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            // Product updates
            $this->createUpdateHistories($product, $user);
        }
    }

    /**
     * Create update histories for products
     */
    private function createUpdateHistories($product, $user): void
    {
        $updateFields = [
            'name' => ['Updated Product Name', 'New Product Name', 'Revised Name'],
            'description' => ['Updated description', 'Enhanced description', 'Revised description'],
            'short_description' => ['Updated short description', 'New short description'],
            'meta_title' => ['Updated meta title', 'New meta title'],
            'meta_description' => ['Updated meta description', 'New meta description'],
        ];

        $updateCount = rand(2, 5);

        for ($i = 0; $i < $updateCount; $i++) {
            $field = array_rand($updateFields);
            $values = $updateFields[$field];
            $newValue = $values[array_rand($values)];

            ProductHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'causer_type' => 'App\\Models\\User',
                'causer_id' => $user->id,
                'action' => 'updated',
                'field_name' => $field,
                'old_value' => $product->{$field} ?? 'Original value',
                'new_value' => $newValue,
                'description' => ucfirst($field).' was updated',
                'ip_address' => $this->generateIpAddress(),
                'user_agent' => $this->generateUserAgent(),
                'metadata' => [
                    'source' => 'admin_panel',
                    'change_reason' => 'Content improvement',
                ],
                'created_at' => now()->subDays(rand(1, 20)),
            ]);
        }
    }

    /**
     * Create price change histories
     */
    private function createPriceChangeHistories($products, $users): void
    {
        foreach ($products->take(8) as $product) {
            $user = $users->random();
            $originalPrice = rand(1000, 5000) / 100; // $10.00 - $50.00
            $newPrice = $originalPrice + rand(-500, 1000) / 100; // Price change

            ProductHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'causer_type' => 'App\\Models\\User',
                'causer_id' => $user->id,
                'action' => 'price_changed',
                'field_name' => 'price',
                'old_value' => $originalPrice,
                'new_value' => $newPrice,
                'description' => 'Product price was updated',
                'ip_address' => $this->generateIpAddress(),
                'user_agent' => $this->generateUserAgent(),
                'metadata' => [
                    'price_change_percentage' => round((($newPrice - $originalPrice) / $originalPrice) * 100, 2),
                    'reason' => 'Market adjustment',
                ],
                'created_at' => now()->subDays(rand(1, 15)),
            ]);
        }
    }

    /**
     * Create stock update histories
     */
    private function createStockUpdateHistories($products, $users): void
    {
        foreach ($products->take(12) as $product) {
            $user = $users->random();
            $oldStock = rand(0, 50);
            $newStock = $oldStock + rand(-20, 100);

            ProductHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'causer_type' => 'App\\Models\\User',
                'causer_id' => $user->id,
                'action' => 'stock_updated',
                'field_name' => 'stock_quantity',
                'old_value' => $oldStock,
                'new_value' => $newStock,
                'description' => 'Stock quantity was updated',
                'ip_address' => $this->generateIpAddress(),
                'user_agent' => $this->generateUserAgent(),
                'metadata' => [
                    'stock_change' => $newStock - $oldStock,
                    'reason' => $newStock > $oldStock ? 'Restock' : 'Sale',
                ],
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }

    /**
     * Create status change histories
     */
    private function createStatusChangeHistories($products, $users): void
    {
        $statuses = ['draft', 'published', 'archived', 'pending'];

        foreach ($products->take(6) as $product) {
            $user = $users->random();
            $oldStatus = $statuses[array_rand($statuses)];
            $newStatus = $statuses[array_rand($statuses)];

            if ($oldStatus !== $newStatus) {
                ProductHistory::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'causer_type' => 'App\\Models\\User',
                    'causer_id' => $user->id,
                    'action' => 'status_changed',
                    'field_name' => 'status',
                    'old_value' => $oldStatus,
                    'new_value' => $newStatus,
                    'description' => 'Product status was changed',
                    'ip_address' => $this->generateIpAddress(),
                    'user_agent' => $this->generateUserAgent(),
                    'metadata' => [
                        'status_change_reason' => 'Administrative action',
                    ],
                    'created_at' => now()->subDays(rand(1, 25)),
                ]);
            }
        }
    }

    /**
     * Create category change histories
     */
    private function createCategoryChangeHistories($products, $users): void
    {
        $categories = ['Electronics', 'Clothing', 'Home & Garden', 'Sports', 'Books'];

        foreach ($products->take(5) as $product) {
            $user = $users->random();
            $oldCategory = $categories[array_rand($categories)];
            $newCategory = $categories[array_rand($categories)];

            if ($oldCategory !== $newCategory) {
                ProductHistory::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'causer_type' => 'App\\Models\\User',
                    'causer_id' => $user->id,
                    'action' => 'category_changed',
                    'field_name' => 'category_id',
                    'old_value' => $oldCategory,
                    'new_value' => $newCategory,
                    'description' => 'Product category was changed',
                    'ip_address' => $this->generateIpAddress(),
                    'user_agent' => $this->generateUserAgent(),
                    'metadata' => [
                        'category_change_reason' => 'Better categorization',
                    ],
                    'created_at' => now()->subDays(rand(1, 20)),
                ]);
            }
        }
    }

    /**
     * Create image change histories
     */
    private function createImageChangeHistories($products, $users): void
    {
        foreach ($products->take(7) as $product) {
            $user = $users->random();
            $oldImage = 'old-image-'.rand(1, 100).'.jpg';
            $newImage = 'new-image-'.rand(1, 100).'.jpg';

            ProductHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'causer_type' => 'App\\Models\\User',
                'causer_id' => $user->id,
                'action' => 'image_changed',
                'field_name' => 'image',
                'old_value' => $oldImage,
                'new_value' => $newImage,
                'description' => 'Product image was updated',
                'ip_address' => $this->generateIpAddress(),
                'user_agent' => $this->generateUserAgent(),
                'metadata' => [
                    'image_change_reason' => 'Better quality image',
                ],
                'created_at' => now()->subDays(rand(1, 15)),
            ]);
        }
    }

    /**
     * Create custom histories
     */
    private function createCustomHistories($products, $users): void
    {
        $customActions = [
            'bulk_import' => 'Product was imported via bulk import',
            'api_update' => 'Product was updated via API',
            'migration' => 'Product data was migrated',
            'sync' => 'Product was synchronized with external system',
        ];

        foreach ($products->take(4) as $product) {
            $user = $users->random();
            $action = array_rand($customActions);
            $description = $customActions[$action];

            ProductHistory::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'causer_type' => 'App\\Models\\User',
                'causer_id' => $user->id,
                'action' => 'custom',
                'field_name' => 'system',
                'old_value' => null,
                'new_value' => $action,
                'description' => $description,
                'ip_address' => $this->generateIpAddress(),
                'user_agent' => $this->generateUserAgent(),
                'metadata' => [
                    'custom_action' => $action,
                    'system_source' => 'automated',
                ],
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }

    /**
     * Generate random IP address
     */
    private function generateIpAddress(): string
    {
        return rand(1, 255).'.'.rand(1, 255).'.'.rand(1, 255).'.'.rand(1, 255);
    }

    /**
     * Generate random user agent
     */
    private function generateUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
            'Mozilla/5.0 (Android 10; Mobile; rv:68.0) Gecko/68.0 Firefox/68.0',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
