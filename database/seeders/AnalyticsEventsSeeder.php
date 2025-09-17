<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AnalyticsEventsSeeder extends Seeder
{
    public function run(): void
    {
        // Get some published products
        $products = Product::where('status', 'published')
            ->where('is_visible', true)
            ->limit(20)
            ->get();

        if ($products->isEmpty()) {
            $this->command->warn('No published products found. Skipping analytics events seeding.');

            return;
        }

        // Get some users for realistic data
        $users = User::limit(10)->get();

        $this->command->info('Creating analytics events for top products widget...');

        // Create product view events (last 30 days)
        foreach ($products as $product) {
            $viewCount = rand(5, 50);  // Random views per product

            for ($i = 0; $i < $viewCount; $i++) {
                AnalyticsEvent::create([
                    'event_type' => 'product_view',
                    'session_id' => 'session_'.uniqid(),
                    'user_id' => $users->random()->id ?? null,
                    'properties' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'category' => $product->categories->first()?->name ?? 'Uncategorized',
                    ],
                    'url' => "/products/{$product->slug}",
                    'referrer' => rand(0, 1) ? 'https://google.com' : 'https://facebook.com',
                    'user_agent' => 'Mozilla/5.0 (compatible; TestBot/1.0)',
                    'ip_address' => '192.168.'.rand(1, 255).'.'.rand(1, 255),
                    'country_code' => collect(['LT', 'US', 'GB', 'DE', 'FR'])->random(),
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }

        // Create add to cart events (fewer than views)
        foreach ($products as $product) {
            $cartCount = rand(1, 15);  // Random cart adds per product

            for ($i = 0; $i < $cartCount; $i++) {
                AnalyticsEvent::create([
                    'event_type' => 'add_to_cart',
                    'session_id' => 'session_'.uniqid(),
                    'user_id' => $users->random()->id ?? null,
                    'properties' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity' => rand(1, 3),
                        'price' => $product->price,
                    ],
                    'url' => "/products/{$product->slug}",
                    'referrer' => "/products/{$product->slug}",
                    'user_agent' => 'Mozilla/5.0 (compatible; TestBot/1.0)',
                    'ip_address' => '192.168.'.rand(1, 255).'.'.rand(1, 255),
                    'country_code' => collect(['LT', 'US', 'GB', 'DE', 'FR'])->random(),
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }

        // Create some recent events (last 7 days) for better widget display
        $recentProducts = $products->take(10);

        foreach ($recentProducts as $product) {
            // Recent views
            for ($i = 0; $i < rand(3, 15); $i++) {
                AnalyticsEvent::create([
                    'event_type' => 'product_view',
                    'session_id' => 'session_'.uniqid(),
                    'user_id' => $users->random()->id ?? null,
                    'properties' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                    ],
                    'url' => "/products/{$product->slug}",
                    'referrer' => 'https://google.com/search?q='.urlencode($product->name),
                    'user_agent' => 'Mozilla/5.0 (compatible; TestBot/1.0)',
                    'ip_address' => '192.168.'.rand(1, 255).'.'.rand(1, 255),
                    'country_code' => 'LT',
                    'created_at' => now()->subDays(rand(0, 7)),
                ]);
            }

            // Recent cart adds
            for ($i = 0; $i < rand(1, 8); $i++) {
                AnalyticsEvent::create([
                    'event_type' => 'add_to_cart',
                    'session_id' => 'session_'.uniqid(),
                    'user_id' => $users->random()->id ?? null,
                    'properties' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => rand(1, 2),
                        'price' => $product->price,
                    ],
                    'url' => "/products/{$product->slug}",
                    'referrer' => "/products/{$product->slug}",
                    'user_agent' => 'Mozilla/5.0 (compatible; TestBot/1.0)',
                    'ip_address' => '192.168.'.rand(1, 255).'.'.rand(1, 255),
                    'country_code' => 'LT',
                    'created_at' => now()->subDays(rand(0, 7)),
                ]);
            }
        }

        $totalEvents = AnalyticsEvent::count();
        $this->command->info("Created analytics events. Total events in database: {$totalEvents}");
    }
}
