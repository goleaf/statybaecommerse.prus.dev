<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
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
        $variants = ProductVariant::all();

        if ($orders->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No orders or products found. Please seed orders and products first.');

            return;
        }

        $orderItems = [];

        foreach ($orders as $order) {
            // Create 1-5 order items per order
            $itemCount = fake()->numberBetween(1, 5);

            for ($i = 0; $i < $itemCount; $i++) {
                $product = $products->random();
                $variant = $variants->where('product_id', $product->id)->random();

                $quantity = fake()->numberBetween(1, 10);
                $unitPrice = fake()->randomFloat(2, 5, 500);
                $discountAmount = fake()->boolean(30) ? fake()->randomFloat(2, 0, $unitPrice * 0.3) : 0;
                $total = ($unitPrice * $quantity) - $discountAmount;

                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'name' => $product->name.($variant ? ' - '.$variant->name : ''),
                    'sku' => $variant?->sku ?? $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'price' => $unitPrice,  // Keep for compatibility
                    'total' => $total,
                    'created_at' => fake()->dateTimeBetween($order->created_at, 'now'),
                    'updated_at' => fake()->dateTimeBetween($order->created_at, 'now'),
                ];
            }
        }

        // Insert order items in chunks
        collect($orderItems)->chunk(100)->each(function ($chunk) {
            OrderItem::insert($chunk->toArray());
        });

        $this->command->info('OrderItem seeding completed successfully.');
    }
}
