<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class AnalyticsEventSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating analytics events...');

        // Clear existing analytics events
        AnalyticsEvent::truncate();

        // Get some users and products for realistic data
        $users = User::limit(50)->get();
        $products = Product::where('is_visible', true)->limit(100)->get();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No users found. Creating some users first...');
            $users = User::factory(20)->create();
        }

        if ($products->isEmpty()) {
            $this->command->warn('⚠️  No products found. Analytics will have limited product data...');
        }

        $eventTypes = ['page_view', 'product_view', 'add_to_cart', 'purchase'];
        $sessionIds = [];

        // Generate some session IDs for realistic user sessions
        for ($i = 0; $i < 100; $i++) {
            $sessionIds[] = 'session_' . fake()->uuid();
        }

        $events = [];
        $batchSize = 500;

        // Create events for the last 30 days with realistic patterns
        for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo);

            // More activity during business hours and recent days
            $baseEventsPerDay = $daysAgo < 7 ? rand(200, 400) : rand(50, 150);

            // Distribute events throughout the day with realistic patterns
            for ($hour = 0; $hour < 24; $hour++) {
                // Peak hours: 9-11, 14-16, 19-21
                $hourMultiplier = match (true) {
                    $hour >= 9 && $hour <= 11 => 1.5,
                    $hour >= 14 && $hour <= 16 => 1.3,
                    $hour >= 19 && $hour <= 21 => 1.8,
                    $hour >= 22 || $hour <= 6 => 0.3,
                    default => 1.0
                };

                $eventsThisHour = (int) ($baseEventsPerDay / 24 * $hourMultiplier);

                for ($i = 0; $i < $eventsThisHour; $i++) {
                    $eventTime = $date->copy()->addHours($hour)->addMinutes(rand(0, 59))->addSeconds(rand(0, 59));
                    $sessionId = fake()->randomElement($sessionIds);
                    $user = fake()->optional(0.6)->randomElement($users);

                    // Create realistic event flow: page_view -> product_view -> add_to_cart -> purchase
                    $eventType = $this->getRealisticEventType($eventTypes);

                    $properties = $this->generateEventProperties($eventType, $products, $user);

                    $events[] = [
                        'event_type' => $eventType,
                        'session_id' => $sessionId,
                        'user_id' => $user?->id,
                        'properties' => json_encode($properties),
                        'url' => $this->generateRealisticUrl($eventType, $properties),
                        'referrer' => fake()->optional(0.4)->url(),
                        'user_agent' => fake()->userAgent(),
                        'ip_address' => fake()->ipv4(),
                        'country_code' => fake()->optional(0.8)->countryCode(),
                        'created_at' => $eventTime,
                    ];

                    // Insert in batches to avoid memory issues
                    if (count($events) >= $batchSize) {
                        DB::table('analytics_events')->insert($events);
                        $events = [];
                        $this->command->info("📊 Inserted batch of {$batchSize} analytics events...");
                    }
                }
            }
        }

        // Insert remaining events
        if (!empty($events)) {
            DB::table('analytics_events')->insert($events);
            $this->command->info('📊 Inserted final batch of ' . count($events) . ' analytics events...');
        }

        $totalEvents = AnalyticsEvent::count();
        $this->command->info("✅ Created {$totalEvents} analytics events successfully!");

        // Show some statistics
        $this->showAnalyticsStatistics();
    }

    private function getRealisticEventType(array $eventTypes): string
    {
        // Weighted distribution: more page views, fewer purchases
        $weights = [
            'page_view' => 50,
            'product_view' => 30,
            'add_to_cart' => 15,
            'purchase' => 5,
        ];

        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($weights as $eventType => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $eventType;
            }
        }

        return 'page_view';
    }

    private function generateEventProperties(string $eventType, $products, $user): array
    {
        $properties = [];

        switch ($eventType) {
            case 'page_view':
                $pages = ['home', 'catalog', 'about', 'contact', 'blog', 'cart', 'checkout'];
                $properties = [
                    'page' => fake()->randomElement($pages),
                    'section' => fake()->optional()->randomElement(['hero', 'products', 'footer', 'header']),
                    'device_type' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
                ];
                break;

            case 'product_view':
                if ($products->isNotEmpty()) {
                    $product = fake()->randomElement($products);
                    $properties = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_price' => $product->price,
                        'category' => $product->category?->name,
                        'view_duration' => rand(10, 300),  // seconds
                    ];
                }
                break;

            case 'add_to_cart':
                if ($products->isNotEmpty()) {
                    $product = fake()->randomElement($products);
                    $properties = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_price' => $product->price,
                        'quantity' => rand(1, 3),
                        'cart_value' => $product->price * rand(1, 3),
                    ];
                }
                break;

            case 'purchase':
                $properties = [
                    'order_value' => fake()->randomFloat(2, 20, 500),
                    'items_count' => rand(1, 5),
                    'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer']),
                    'shipping_method' => fake()->randomElement(['standard', 'express', 'pickup']),
                ];
                if ($products->isNotEmpty()) {
                    $purchasedProducts = fake()->randomElements($products->toArray(), rand(1, 3));
                    $properties['products'] = array_map(fn($p) => [
                        'id' => $p['id'],
                        'name' => $p['name'],
                        'price' => $p['price']
                    ], $purchasedProducts);
                }
                break;
        }

        return $properties;
    }

    private function generateRealisticUrl(string $eventType, array $properties): string
    {
        $baseUrl = config('app.url', 'http://localhost');

        return match ($eventType) {
            'page_view' => $baseUrl . '/' . ($properties['page'] ?? 'home'),
            'product_view' => $baseUrl . '/products/' . ($properties['product_id'] ?? '1'),
            'add_to_cart' => $baseUrl . '/products/' . ($properties['product_id'] ?? '1'),
            'purchase' => $baseUrl . '/checkout/success',
            default => $baseUrl,
        };
    }

    private function showAnalyticsStatistics(): void
    {
        $this->command->info("\n📈 Analytics Statistics:");

        foreach (['page_view', 'product_view', 'add_to_cart', 'purchase'] as $eventType) {
            $count = AnalyticsEvent::where('event_type', $eventType)->count();
            $this->command->info("   {$eventType}: {$count} events");
        }

        $todayEvents = AnalyticsEvent::where('created_at', '>=', now()->startOfDay())->count();
        $this->command->info("   Today's events: {$todayEvents}");

        $thisWeekEvents = AnalyticsEvent::where('created_at', '>=', now()->startOfWeek())->count();
        $this->command->info("   This week's events: {$thisWeekEvents}");
    }
}
