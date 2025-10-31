<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing orders and products
        $orders = Order::all();
        $products = Product::all();

        if ($orders->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No orders or products found. Please seed orders and products first.');

            return;
        }

        foreach ($orders as $order) {
            // Create 1-5 order items per order using factory relationships
            $itemCount = fake()->numberBetween(1, 5);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            // Normalise the order timestamps to Carbon instances so we can safely derive ranges for Faker.
            $orderCreatedAt = $order->created_at instanceof Carbon
                ? $order->created_at->copy()
                : Carbon::parse((string) $order->created_at);

            // Ensure the faker range never inverts by clamping the end date to now and the start date to the earlier value.
            $startDate = $orderCreatedAt->lessThanOrEqualTo(now()) ? $orderCreatedAt : now();
            $endDate = now();

            foreach ($selectedProducts as $product) {
                // Generate timestamps in chronological order so updated_at never predates created_at even for future-dated orders.
                $createdAt = fake()->dateTimeBetween($startDate, $endDate);
                $updatedAt = fake()->dateTimeBetween($createdAt, $endDate);

                OrderItem::factory()
                    ->for($order)
                    ->for($product)
                    ->state([
                        'name'       => $product->name,
                        'sku'        => $product->sku ?? fake()->bothify('SKU-####'),
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ])
                    ->create();
            }
        }

        $this->command->info('OrderItem seeding completed successfully.');
    }
}
