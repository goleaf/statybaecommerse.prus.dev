<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $country = $this->seedCountry();
            $channel = $this->seedChannel();
            $zone = $this->seedZone();

            $brands = [
                'makita' => $this->seedBrand('makita'),
                'bosch' => $this->seedBrand('bosch'),
                'dewalt' => $this->seedBrand('dewalt'),
            ];

            $categories = $this->seedCategories();
            $products = $this->seedProducts($brands, $categories);
            $customers = $this->seedCustomers();

            $this->seedOrders($customers, $products, $channel, $zone, $country);
        });
    }

    private function seedCountry(): Country
    {
        $attributes = Country::factory()->lithuania()->raw();

        return Country::query()->updateOrCreate(
            ['cca2' => $attributes['cca2']],
            $attributes,
        );
    }

    private function seedChannel(): Channel
    {
        $attributes = Channel::factory()->web()->raw();

        return Channel::query()->updateOrCreate(
            ['slug' => $attributes['slug']],
            $attributes,
        );
    }

    private function seedZone(): Zone
    {
        $attributes = Zone::factory()->lithuania()->raw();

        return Zone::query()->updateOrCreate(
            ['code' => $attributes['code']],
            $attributes,
        );
    }

    private function seedBrand(string $state): Brand
    {
        $factory = Brand::factory();
        $factory = method_exists($factory, $state) ? $factory->{$state}() : $factory;
        $attributes = $factory->raw();

        return Brand::query()->updateOrCreate(
            ['slug' => $attributes['slug']],
            $attributes,
        );
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $tools = $this->upsertCategory(Category::factory()->tools());
        $fasteners = $this->upsertCategory(Category::factory()->fasteners());
        $safety = $this->upsertCategory(Category::factory()->safety());

        $powerTools = $this->upsertCategory(Category::factory()->powerTools()->withParent($tools));
        $handTools = $this->upsertCategory(Category::factory()->handTools()->withParent($tools));
        $protectiveGear = $this->upsertCategory(Category::factory()->protectiveGear()->withParent($safety));

        return [
            'tools' => $tools,
            'fasteners' => $fasteners,
            'safety' => $safety,
            'powerTools' => $powerTools,
            'handTools' => $handTools,
            'protectiveGear' => $protectiveGear,
        ];
    }

    private function upsertCategory(Factory $factory): Category
    {
        $attributes = $factory->raw();

        return Category::query()->updateOrCreate(
            ['slug' => $attributes['slug']],
            $attributes,
        );
    }

    /**
     * @param  array<string, Brand>  $brands
     * @param  array<string, Category>  $categories
     * @return array<string, Product>
     */
    private function seedProducts(array $brands, array $categories): array
    {
        $products = [];

        $products['hammerDrill'] = $this->upsertProduct(
            Product::factory()->hammerDrill()->published()->state([
                'brand_id' => $brands['makita']->id,
            ]),
            [$categories['powerTools']->id],
        );

        $products['circularSaw'] = $this->upsertProduct(
            Product::factory()->circularSaw()->published()->state([
                'brand_id' => $brands['dewalt']->id,
            ]),
            [$categories['powerTools']->id],
        );

        $products['safetyGlasses'] = $this->upsertProduct(
            Product::factory()->safetyGlasses()->published()->state([
                'brand_id' => $brands['bosch']->id,
            ]),
            [$categories['protectiveGear']->id],
        );

        return $products;
    }

    private function upsertProduct(Factory $factory, array $categoryIds): Product
    {
        $attributes = $factory->raw();
        $product = Product::query()->updateOrCreate(
            ['slug' => $attributes['slug']],
            $attributes,
        );

        $product->categories()->sync($categoryIds);

        return $product;
    }

    /**
     * @return array<string, User>
     */
    private function seedCustomers(): array
    {
        $customers = [
            'greta' => $this->upsertUser('greta@demo.test', 'Greta Mikalajūnaitė'),
            'jonas' => $this->upsertUser('jonas@demo.test', 'Jonas Kazlauskas'),
            'ruta' => $this->upsertUser('ruta@demo.test', 'Rūta Petrauskienė'),
        ];

        Collection::make($customers)->each(fn (User $user) => $user->syncRoles(['user']));

        return $customers;
    }

    private function upsertUser(string $email, string $name): User
    {
        $attributes = User::factory()->state([
            'email' => $email,
            'name' => $name,
            'preferred_locale' => 'lt',
            'is_admin' => false,
        ])->raw();

        return User::query()->updateOrCreate(
            ['email' => $email],
            $attributes,
        );
    }

    /**
     * @param  array<string, User>  $customers
     * @param  array<string, Product>  $products
     */
    private function seedOrders(array $customers, array $products, Channel $channel, Zone $zone, Country $country): void
    {
        $orders = [
            [
                'number' => 'ORD-100001',
                'user' => $customers['greta'],
                'status' => 'completed',
                'payment_status' => 'paid',
                'amounts' => [
                    'subtotal' => 387.00,
                    'shipping' => 12.00,
                    'discount' => 0.00,
                ],
                'items' => [
                    ['product' => $products['hammerDrill'], 'quantity' => 1],
                    ['product' => $products['safetyGlasses'], 'quantity' => 2],
                ],
                'shipping_days' => 2,
            ],
            [
                'number' => 'ORD-100002',
                'user' => $customers['jonas'],
                'status' => 'processing',
                'payment_status' => 'paid',
                'amounts' => [
                    'subtotal' => 289.00,
                    'shipping' => 9.50,
                    'discount' => 15.00,
                ],
                'items' => [
                    ['product' => $products['circularSaw'], 'quantity' => 1],
                ],
                'shipping_days' => null,
            ],
        ];

        foreach ($orders as $orderData) {
            $subtotal = $orderData['amounts']['subtotal'];
            $shipping = $orderData['amounts']['shipping'];
            $discount = $orderData['amounts']['discount'];
            $tax = round($subtotal * 0.21, 2);
            $total = round($subtotal + $tax + $shipping - $discount, 2);

            $timestamps = $this->orderTimestamps($orderData['status'], $orderData['shipping_days']);

            $attributes = Order::factory()->state([
                'number' => $orderData['number'],
                'user_id' => $orderData['user']->id,
                'channel_id' => $channel->id,
                'zone_id' => $zone->id,
                'status' => $orderData['status'],
                'payment_status' => $orderData['payment_status'],
                'payment_method' => 'bank_transfer',
                'partner_id' => null,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'shipping_amount' => $shipping,
                'discount_amount' => $discount,
                'total' => $total,
                'currency' => 'EUR',
                'billing_address' => $this->addressFor($orderData['user'], $country),
                'shipping_address' => $this->addressFor($orderData['user'], $country),
                'notes' => null,
            ] + $timestamps)->raw();

            $order = Order::query()->updateOrCreate(
                ['number' => $attributes['number']],
                Arr::except($attributes, ['number'])
            );

            $order->items()->delete();

            foreach ($orderData['items'] as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];

                OrderItem::factory()->state([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'price' => $product->price,
                    'total' => round($product->price * $quantity, 2),
                ])->create();
            }
        }
    }

    private function orderTimestamps(string $status, ?int $shippingDays): array
    {
        $now = now();

        return match ($status) {
            'completed' => [
                'shipped_at' => $now->copy()->subDays($shippingDays ?? 3),
                'delivered_at' => $now->copy()->subDay(),
            ],
            'processing' => [
                'shipped_at' => null,
                'delivered_at' => null,
            ],
            default => [
                'shipped_at' => null,
                'delivered_at' => null,
            ],
        };
    }

    private function addressFor(User $user, Country $country): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '+37060000000',
            'address' => 'Konstitucijos pr. 7',
            'city' => 'Vilnius',
            'postal_code' => '09308',
            'country' => $country->name,
        ];
    }
}
