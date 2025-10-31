<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Grab the necessary relations up front so the seeder does not trigger
        // N+1 lookups when iterating through each order.
        $orders = Order::query()->get();
        $products = Product::query()->with('variants')->get();

        if ($orders->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No orders or products found. Please seed orders and products first.');

            return;
        }

        foreach ($orders as $order) {
            // Respect the available product pool so we never request more items than exist.
            $maxItems = min(5, $products->count());
            $itemCount = fake()->numberBetween(1, $maxItems);

            // Guarantee we are working with a collection even when only one product is sampled.
            $selectedProducts = $this->pickProductsForOrder($products, $itemCount);

            foreach ($selectedProducts as $index => $product) {
                // Decide whether to attach a variant while avoiding unnecessary relation loads.
                $variant = $this->pickVariantForProduct($product);

                $quantity = fake()->numberBetween(1, 4);
                $unitPrice = $this->resolveUnitPrice($product, $variant);
                $timestamps = $this->resolveTimestamps($order);

                OrderItem::factory()
                    ->for($order)
                    ->state([
                        'product_id'         => $product->id,
                        'product_variant_id' => $variant?->id,
                        'name'               => $variant?->name ?? $product->name,
                        'sku'                => $this->resolveSku($product, $variant, (int) $order->id, $index),
                        'quantity'           => $quantity,
                        'unit_price'         => $unitPrice,
                        'price'              => $unitPrice,
                        'total'              => $unitPrice * $quantity,
                        'notes'              => fake()->optional(0.3)->sentence(),
                        'status'             => $this->resolveStatus(),
                        'created_at'         => $timestamps['created_at'],
                        'updated_at'         => $timestamps['updated_at'],
                    ])
                    ->create();
            }
        }

        $this->command?->info('OrderItem seeding completed successfully.');
    }

    /**
     * Pick a deterministic collection of products for the provided order.
     */
    private function pickProductsForOrder(Collection $products, int $itemCount): Collection
    {
        $selection = $products->random($itemCount);

        // Ensure that a single sampled product is wrapped in a collection so the
        // calling code can treat the result consistently.
        if ($selection instanceof Product) {
            return collect([$selection]);
        }

        return $selection->values();
    }

    /**
     * Randomly choose a variant for the supplied product to better represent catalog diversity.
     */
    private function pickVariantForProduct(Product $product): ?ProductVariant
    {
        if (! $product->relationLoaded('variants')) {
            $product->load('variants');
        }

        if ($product->variants->isEmpty()) {
            return null;
        }

        // Only some items should include variants so the demo data represents both cases.
        if (! fake()->boolean(60)) {
            return null;
        }

        $variant = $product->variants->random();

        return $variant instanceof ProductVariant ? $variant : null;
    }

    /**
     * Resolve a sensible unit price from the product hierarchy.
     */
    private function resolveUnitPrice(Product $product, ?ProductVariant $variant): float
    {
        $candidate = $variant?->price
            ?? $product->price
            ?? $product->sale_price
            ?? $product->compare_price;

        if ($candidate === null) {
            // Fallback to a generated amount when the catalog lacks any pricing.
            return fake()->randomFloat(2, 5, 150);
        }

        return (float) $candidate;
    }

    /**
     * Derive a stable SKU so rows without catalog data still look realistic.
     */
    private function resolveSku(Product $product, ?ProductVariant $variant, int $orderId, int $sequence): string
    {
        $sku = $variant?->sku ?: $product->sku;

        if (is_string($sku) && $sku !== '') {
            return $sku;
        }

        // Build a readable fallback using the order identifier and an item counter.
        return sprintf(
            'SKU-%04d-%s',
            $orderId,
            Str::upper(Str::random(4 + $sequence % 3))
        );
    }

    /**
     * Provide varied but valid order item statuses for the generated data.
     */
    private function resolveStatus(): string
    {
        return fake()->randomElement(['pending', 'processing', 'completed']);
    }

    /**
     * Produce timestamps that always respect the order lifecycle.
     *
     * @return array{created_at: Carbon, updated_at: Carbon}
     */
    private function resolveTimestamps(Order $order): array
    {
        $orderCreatedAt = $order->created_at instanceof Carbon
            ? $order->created_at->copy()
            : Carbon::parse((string) $order->created_at);

        $startDate = $orderCreatedAt->lessThanOrEqualTo(now()) ? $orderCreatedAt : now();
        $endDate = now();

        $createdAt = Carbon::instance(fake()->dateTimeBetween($startDate, $endDate));
        $updatedAt = Carbon::instance(fake()->dateTimeBetween($createdAt, $endDate));

        return [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
