<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Collection;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\VariantInventory;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class ExtendedDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create brands
        $brands = Brand::factory()->count(12)->create();

        // Create categories with hierarchical structure
        $rootCategories = Category::factory()->count(10)->create();
        $childCategories = collect();
        foreach ($rootCategories as $root) {
            $children = Category::factory()
                ->count(random_int(1, 3))
                ->for($root, 'parent')
                ->create();
            $childCategories = $childCategories->merge($children);
        }
        $allCategories = $rootCategories->merge($childCategories);

        // Create collections
        $collections = Collection::factory()->count(8)->create();

        // Create attributes with values using relationships
        $attributes = Attribute::factory()
            ->count(5)
            ->has(AttributeValue::factory()->count(random_int(5, 12)), 'values')
            ->create();

        // Get default currency and location
        $defaultCurrency = Currency::factory()->create(['is_default' => true]);
        $defaultLocation = Location::factory()->create();
        $defaultChannel = Channel::factory()->create();
        $defaultZone = Zone::factory()->create();

        // Create products with full relationships
        $products = Product::factory()
            ->count(150)
            ->has(
                ProductVariant::factory()
                    ->has(
                        Price::factory()->for($defaultCurrency, 'currency'),
                        'prices'
                    )
                    ->has(
                        VariantInventory::factory()
                            ->for($defaultLocation, 'location')
                            ->state(['stock' => random_int(5, 50)]),
                        'inventories'
                    ),
                'variants'
            )
            ->has(
                Price::factory()->for($defaultCurrency, 'currency'),
                'prices'
            )
            ->create()
            ->each(function (Product $product) use ($brands, $allCategories, $collections) {
                // Attach random brand
                $product->brand()->associate($brands->random());
                $product->save();

                // Attach random categories
                $product->categories()->attach(
                    $allCategories->random(random_int(1, 3))->pluck('id')
                );

                // Attach manual collections
                $manualCollections = $collections->where('type', 'manual');
                if ($manualCollections->isNotEmpty()) {
                    $product->collections()->attach(
                        $manualCollections->random(min(2, $manualCollections->count()))->pluck('id')
                    );
                }

                // Add media if available
                $path = 'demo/tshirt.jpg';
                if (Storage::disk('public')->exists($path)) {
                    $product->addMedia(Storage::disk('public')->path($path))
                        ->toMediaCollection(config('media.storage.collection_name'));
                }
            });

        // Create reviews using factory relationships
        $user = User::factory()->create();
        Review::factory()
            ->count(300)
            ->for($user)
            ->create()
            ->each(function (Review $review) use ($products) {
                $review->product()->associate($products->random());
                $review->save();
            });

        // Create demo user with address and order
        $demoUser = User::factory()
            ->has(
                Address::factory()->state([
                    'type' => 'shipping',
                    'last_name' => 'Doe',
                    'first_name' => 'John',
                    'address_line_1' => '123 Main St',
                    'postal_code' => '00000',
                    'city' => 'Springfield',
                    'phone' => '1234567890',
                    'is_default' => true,
                    'country' => 'LT',
                ]),
                'addresses'
            )
            ->create();

        // Create demo order with relationships
        $orderProducts = $products->where('is_visible', true)
            ->whereNotNull('published_at')
            ->take(3);

        if ($orderProducts->isNotEmpty()) {
            $order = Order::factory()
                ->for($demoUser, 'user')
                ->for($defaultChannel, 'channel')
                ->for($defaultZone, 'zone')
                ->state([
                    'currency' => $defaultCurrency->code,
                    'payment_method' => 'cash_on_delivery',
                    'payment_status' => 'pending',
                ])
                ->has(
                    OrderShipping::factory()->state([
                        'carrier' => 'standard',
                        'service' => 'ground',
                        'price' => 9.99,
                        'weight' => 1.0,
                        'estimated_delivery_date' => now()->addDays(5),
                    ]),
                    'shipping'
                )
                ->create();

            // Create order items using factory relationships
            foreach ($orderProducts as $product) {
                $price = $product->prices()->first();
                $amount = $price ? $price->amount : (random_int(1000, 5000) / 100);

                OrderItem::factory()
                    ->for($order)
                    ->for($product)
                    ->state([
                        'name' => $product->name,
                        'sku' => $product->sku ?? 'SKU-' . strtoupper(fake()->bothify('??????')),
                        'unit_price' => $amount,
                        'quantity' => 1,
                        'total' => $amount,
                    ])
                    ->create();
            }

            // Update order totals
            $order->update([
                'subtotal' => $order->items()->sum('total'),
                'shipping_amount' => 9.99,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total' => $order->items()->sum('total') + 9.99,
            ]);
        }
    }
}
