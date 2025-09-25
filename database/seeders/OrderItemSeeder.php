<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

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

            foreach ($selectedProducts as $product) {
                OrderItem::factory()
                    ->for($order)
                    ->for($product)
                    ->state([
                        'name' => $product->name,
                        'sku' => $product->sku ?? fake()->bothify('SKU-####'),
                        'created_at' => fake()->dateTimeBetween($order->created_at, 'now'),
                        'updated_at' => fake()->dateTimeBetween($order->created_at, 'now'),
                    ])
                    ->create();
            }
        }

        $this->command->info('OrderItem seeding completed successfully.');
    }
}
