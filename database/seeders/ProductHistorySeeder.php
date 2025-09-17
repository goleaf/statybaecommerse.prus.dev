<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class ProductHistorySeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with(['brand', 'categories'])->get();
        $users = User::all();
        
        if ($products->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No products or users found. Please run ProductSeeder and UserSeeder first.');
            return;
        }

        $this->command->info('Creating product history entries...');

        foreach ($products as $product) {
            $this->createProductHistory($product, $users);
        }

        $this->command->info('Product history seeding completed!');
    }

    private function createProductHistory(Product $product, $users): void
    {
        $historyEntries = [];
        $createdAt = $product->created_at ?? now()->subMonths(6);
        
        // Helper method to add causer fields
        $addCauserFields = function($entry, $users) {
            $entry['causer_type'] = User::class;
            $entry['causer_id'] = $users->random()->id;
            return $entry;
        };
        
        // Product creation history
        $historyEntries[] = [
            'product_id' => $product->id,
            'user_id' => $users->random()->id,
            'action' => 'created',
            'field_name' => 'product',
            'old_value' => null,
            'new_value' => [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'description' => $product->description,
            ],
            'description' => "Product '{$product->name}' was created",
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadata' => [
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'brand' => $product->brand?->name,
                'categories' => $product->categories->pluck('name')->toArray(),
                'timestamp' => $createdAt->format('Y-m-d H:i:s'),
            ],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];

        // Price changes (2-5 entries per product)
        $priceChanges = fake()->numberBetween(2, 5);
        for ($i = 0; $i < $priceChanges; $i++) {
            $oldPrice = $product->price;
            $newPrice = fake()->randomFloat(2, $oldPrice * 0.8, $oldPrice * 1.3);
            $changeDate = fake()->dateTimeBetween($createdAt, 'now');
            
            $historyEntries[] = [
                'product_id' => $product->id,
                'user_id' => $users->random()->id,
                'action' => 'price_changed',
                'field_name' => 'price',
                'old_value' => $oldPrice,
                'new_value' => $newPrice,
                'description' => "Price changed from {$oldPrice}€ to {$newPrice}€",
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'metadata' => [
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'price_change_percentage' => round((($newPrice - $oldPrice) / $oldPrice) * 100, 2),
                    'reason' => fake()->randomElement(['market_adjustment', 'cost_increase', 'promotion', 'competitor_analysis']),
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                ],
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
        }

        // Stock updates (3-8 entries per product)
        $stockUpdates = fake()->numberBetween(3, 8);
        $currentStock = $product->stock_quantity ?? 0;
        
        for ($i = 0; $i < $stockUpdates; $i++) {
            $oldStock = $currentStock;
            $stockChange = fake()->numberBetween(-50, 100);
            $newStock = max(0, $oldStock + $stockChange);
            $changeDate = fake()->dateTimeBetween($createdAt, 'now');
            
            $historyEntries[] = [
                'product_id' => $product->id,
                'user_id' => $users->random()->id,
                'action' => 'stock_updated',
                'field_name' => 'stock_quantity',
                'old_value' => $oldStock,
                'new_value' => $newStock,
                'description' => "Stock updated from {$oldStock} to {$newStock} units",
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'metadata' => [
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'stock_change' => $stockChange,
                    'reason' => fake()->randomElement(['restock', 'sale', 'return', 'damage', 'inventory_adjustment']),
                    'supplier' => fake()->company(),
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                ],
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
            
            $currentStock = $newStock;
        }

        // Status changes (1-3 entries per product)
        $statusChanges = fake()->numberBetween(1, 3);
        $statuses = ['draft', 'published', 'archived'];
        $currentStatus = 'published';
        
        for ($i = 0; $i < $statusChanges; $i++) {
            $oldStatus = $currentStatus;
            $newStatus = fake()->randomElement(array_diff($statuses, [$oldStatus]));
            $changeDate = fake()->dateTimeBetween($createdAt, 'now');
            
            $historyEntries[] = [
                'product_id' => $product->id,
                'user_id' => $users->random()->id,
                'action' => 'status_changed',
                'field_name' => 'status',
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
                'description' => "Status changed from {$oldStatus} to {$newStatus}",
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'metadata' => [
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'reason' => fake()->randomElement(['quality_control', 'seasonal', 'discontinuation', 'promotion']),
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                ],
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
            
            $currentStatus = $newStatus;
        }

        // Visibility changes (1-2 entries per product)
        $visibilityChanges = fake()->numberBetween(1, 2);
        $isVisible = true;
        
        for ($i = 0; $i < $visibilityChanges; $i++) {
            $oldVisibility = $isVisible;
            $newVisibility = !$oldVisibility;
            $changeDate = fake()->dateTimeBetween($createdAt, 'now');
            
            $historyEntries[] = [
                'product_id' => $product->id,
                'user_id' => $users->random()->id,
                'action' => 'updated',
                'field_name' => 'is_visible',
                'old_value' => $oldVisibility,
                'new_value' => $newVisibility,
                'description' => "Visibility changed from " . ($oldVisibility ? 'visible' : 'hidden') . " to " . ($newVisibility ? 'visible' : 'hidden'),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'metadata' => [
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'reason' => fake()->randomElement(['maintenance', 'seasonal', 'quality_issue', 'promotion']),
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                ],
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
            
            $isVisible = $newVisibility;
        }

        // Description updates (1-2 entries per product)
        $descriptionUpdates = fake()->numberBetween(1, 2);
        
        for ($i = 0; $i < $descriptionUpdates; $i++) {
            $oldDescription = $product->description;
            $newDescription = $oldDescription . ' ' . fake()->sentence();
            $changeDate = fake()->dateTimeBetween($createdAt, 'now');
            
            $historyEntries[] = [
                'product_id' => $product->id,
                'user_id' => $users->random()->id,
                'action' => 'updated',
                'field_name' => 'description',
                'old_value' => $oldDescription,
                'new_value' => $newDescription,
                'description' => "Product description updated",
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'metadata' => [
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'reason' => fake()->randomElement(['seo_optimization', 'customer_feedback', 'compliance', 'marketing']),
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                ],
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
        }

        // Category changes (0-2 entries per product)
        $categoryChanges = fake()->numberBetween(0, 2);
        
        for ($i = 0; $i < $categoryChanges; $i++) {
            $oldCategories = $product->categories->pluck('name')->toArray();
            $newCategories = array_merge($oldCategories, [fake()->word()]);
            $changeDate = fake()->dateTimeBetween($createdAt, 'now');
            
            $historyEntries[] = [
                'product_id' => $product->id,
                'user_id' => $users->random()->id,
                'action' => 'updated',
                'field_name' => 'categories',
                'old_value' => $oldCategories,
                'new_value' => $newCategories,
                'description' => "Product categories updated",
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'metadata' => [
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'reason' => fake()->randomElement(['reorganization', 'new_category', 'customer_request']),
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                ],
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
        }

        // Insert all history entries for this product one by one to handle JSON properly
        foreach ($historyEntries as $entry) {
            $entry = $addCauserFields($entry, $users);
            ProductHistory::create($entry);
        }
        
        $this->command->info("Created " . count($historyEntries) . " history entries for product: {$product->name}");
    }
}
