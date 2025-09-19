<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class ProductHistorySeeder extends Seeder
{
    private const CHUNK_SIZE = 200;

    public function run(): void
    {
        if (! Schema::hasTable('product_histories')) {
            $this->command?->warn('ProductHistorySeeder skipped: `product_histories` table not found. Run the product history migration first.');

            return;
        }

        $query = Product::query()
            ->with(['brand:id,name', 'categories:id,name'])
            ->orderBy('id');

        $userIds = User::query()->pluck('id')->all();

        if ($query->doesntExist()) {
            $this->command?->warn('ProductHistorySeeder skipped: no products found.');
            return;
        }

        if (empty($userIds)) {
            $this->command?->warn('ProductHistorySeeder skipped: no users found. Seed users before seeding histories.');
            return;
        }

        $this->command?->info('Seeding product history entries...');

        $query->chunkById(self::CHUNK_SIZE, function (Collection $products) use ($userIds): void {
            foreach ($products as $product) {
                if (ProductHistory::query()->where('product_id', $product->id)->exists()) {
                    // Skip products that already have history to keep seeding idempotent.
                    continue;
                }

                $this->createHistoryForProduct($product, $userIds);
            }
        });

        $this->command?->info('Product history seeding completed.');
    }

    /**
     * Create sample history timeline for a given product.
     *
     * @param array<int, int> $userIds
     */
    private function createHistoryForProduct(Product $product, array $userIds): void
    {
        $faker = fake();
        $createdAt = $product->created_at ? Carbon::parse($product->created_at) : Carbon::now()->subMonths(6);
        $baseMetadata = [
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'brand' => $product->brand?->name,
            'categories' => $product->categories->pluck('name')->toArray(),
        ];

        $pickUserId = fn (): int => Arr::random($userIds);

        $entries = [];
        $initialUserId = $pickUserId();

        $entries[] = [
            'action' => 'created',
            'field_name' => 'product',
            'old_value' => null,
            'new_value' => [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) ($product->price ?? 0),
                'status' => $product->status,
            ],
            'description' => "Product '{$product->name}' was created",
            'user_id' => $initialUserId,
            'metadata' => array_merge($baseMetadata, [
                'timestamp' => $createdAt->format('Y-m-d H:i:s'),
                'source' => 'initial_import',
            ]),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];

        $price = (float) ($product->price ?? $faker->randomFloat(2, 5, 100));
        $priceChangeCount = $faker->numberBetween(2, 4);

        for ($i = 0; $i < $priceChangeCount; $i++) {
            $changeDate = Carbon::instance($faker->dateTimeBetween($createdAt, 'now'));
            $oldPrice = $price;
            $multiplier = $faker->randomFloat(2, 0.85, 1.25);
            $price = round(max(0.5, $oldPrice * $multiplier), 2);

            $entries[] = [
                'action' => 'price_changed',
                'field_name' => 'price',
                'old_value' => $oldPrice,
                'new_value' => $price,
                'description' => "Price changed from {$oldPrice}€ to {$price}€",
                'user_id' => $pickUserId(),
                'metadata' => array_merge($baseMetadata, [
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                    'price_change_percentage' => $oldPrice > 0 ? round((($price - $oldPrice) / $oldPrice) * 100, 2) : null,
                    'reason' => $faker->randomElement(['market_adjustment', 'supplier_update', 'promotion', 'competitor_move']),
                ]),
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
        }

        $stock = $product->stock_quantity ?? $faker->numberBetween(0, 250);
        $stockChanges = $faker->numberBetween(3, 6);

        for ($i = 0; $i < $stockChanges; $i++) {
            $changeDate = Carbon::instance($faker->dateTimeBetween($createdAt, 'now'));
            $delta = $faker->numberBetween(-60, 120);
            $newStock = max(0, $stock + $delta);

            $entries[] = [
                'action' => 'stock_updated',
                'field_name' => 'stock_quantity',
                'old_value' => $stock,
                'new_value' => $newStock,
                'description' => "Stock updated from {$stock} to {$newStock} units",
                'user_id' => $pickUserId(),
                'metadata' => array_merge($baseMetadata, [
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                    'stock_change' => $delta,
                    'reason' => $faker->randomElement(['restock', 'inventory_adjustment', 'customer_return', 'bulk_order']),
                ]),
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];

            $stock = $newStock;
        }

        $statuses = ['draft', 'published', 'archived'];
        $status = $product->status ?? 'published';
        $statusChanges = $faker->numberBetween(1, 3);

        for ($i = 0; $i < $statusChanges; $i++) {
            $changeDate = Carbon::instance($faker->dateTimeBetween($createdAt, 'now'));
            $newStatus = $faker->randomElement(array_values(array_diff($statuses, [$status])));

            $entries[] = [
                'action' => 'status_changed',
                'field_name' => 'status',
                'old_value' => $status,
                'new_value' => $newStatus,
                'description' => "Status changed from {$status} to {$newStatus}",
                'user_id' => $pickUserId(),
                'metadata' => array_merge($baseMetadata, [
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                    'reason' => $faker->randomElement(['assortment_review', 'seasonal_adjustment', 'quality_check']),
                ]),
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];

            $status = $newStatus;
        }

        $visibility = (bool) ($product->is_visible ?? true);
        $visibilityChanges = $faker->numberBetween(1, 2);

        for ($i = 0; $i < $visibilityChanges; $i++) {
            $changeDate = Carbon::instance($faker->dateTimeBetween($createdAt, 'now'));
            $newVisibility = ! $visibility;

            $entries[] = [
                'action' => 'updated',
                'field_name' => 'is_visible',
                'old_value' => $visibility,
                'new_value' => $newVisibility,
                'description' => sprintf('Visibility changed from %s to %s', $visibility ? 'visible' : 'hidden', $newVisibility ? 'visible' : 'hidden'),
                'user_id' => $pickUserId(),
                'metadata' => array_merge($baseMetadata, [
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                    'reason' => $faker->randomElement(['maintenance', 'seasonal_visibility', 'content_review']),
                ]),
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];

            $visibility = $newVisibility;
        }

        $description = (string) ($product->description ?? '');
        $descriptionUpdates = $faker->numberBetween(1, 2);

        for ($i = 0; $i < $descriptionUpdates; $i++) {
            $changeDate = Carbon::instance($faker->dateTimeBetween($createdAt, 'now'));
            $oldDescription = $description;
            $description = trim($oldDescription . ' ' . $faker->sentence());

            $entries[] = [
                'action' => 'updated',
                'field_name' => 'description',
                'old_value' => $oldDescription,
                'new_value' => $description,
                'description' => 'Product description updated',
                'user_id' => $pickUserId(),
                'metadata' => array_merge($baseMetadata, [
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                    'reason' => $faker->randomElement(['seo_optimization', 'content_refresh', 'supplier_information']),
                ]),
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];
        }

        $categoryChanges = $faker->numberBetween(0, 2);
        $currentCategories = $product->categories->pluck('name')->toArray();

        for ($i = 0; $i < $categoryChanges; $i++) {
            $changeDate = Carbon::instance($faker->dateTimeBetween($createdAt, 'now'));
            $newCategory = $faker->sentence(2);
            $newCategories = array_values(array_unique(array_merge($currentCategories, [$newCategory])));

            $entries[] = [
                'action' => 'updated',
                'field_name' => 'categories',
                'old_value' => $currentCategories,
                'new_value' => $newCategories,
                'description' => 'Product categories updated',
                'user_id' => $pickUserId(),
                'metadata' => array_merge($baseMetadata, [
                    'timestamp' => $changeDate->format('Y-m-d H:i:s'),
                    'reason' => $faker->randomElement(['navigation_update', 'cross_sell', 'merchandising']),
                ]),
                'created_at' => $changeDate,
                'updated_at' => $changeDate,
            ];

            $currentCategories = $newCategories;
        }

        foreach ($entries as $entry) {
            $entry['product_id'] = $product->id;
            $entry['causer_type'] = User::class;
            $entry['causer_id'] = $entry['user_id'];
            $entry['ip_address'] = $entry['ip_address'] ?? $faker->ipv4();
            $entry['user_agent'] = $entry['user_agent'] ?? $faker->userAgent();

            ProductHistory::create($entry);
        }

        $this->command?->info(sprintf('Seeded %d history entries for product: %s', count($entries), $product->sku));
    }
}
