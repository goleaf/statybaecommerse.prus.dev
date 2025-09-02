<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExtendedDemoSeeder extends Seeder
{
	public function run(): void
	{
		DB::transaction(function () {
			// Brands
			\App\Models\Brand::factory()->count(12)->create();

			// Categories (roots + children)
			$roots = \App\Models\Category::factory()->count(10)->create();
			foreach ($roots as $root) {
				\App\Models\Category::factory()->count(random_int(1, 3))->create(['parent_id' => $root->id]);
			}

			// Collections (mix manual/auto)
			$collections = \App\Models\Collection::factory()->count(8)->create();

			// Attributes + values
			$attributes = \App\Models\Attribute::factory()->count(5)->create();
			foreach ($attributes as $attr) {
				\App\Models\AttributeValue::factory()->count(random_int(5, 12))->create(['attribute_id' => $attr->id]);
			}

			// Products
			$products = \App\Models\Product::factory()->count(150)->create()->each(function ($product) use ($attributes, $collections, $roots) {
				// brand
				$brandId = \App\Models\Brand::query()->inRandomOrder()->value('id');
				if ($brandId) {
					$product->brand_id = $brandId;
					$product->save();
				}

				// categories
				$catIds = \App\Models\Category::query()->inRandomOrder()->limit(random_int(1, 3))->pluck('id')->all();
				if (!empty($catIds)) {
					$product->categories()->syncWithoutDetaching($catIds);
				}

				// manual collections
				$manualCollections = $collections->where('type', 'manual')->random($collections->where('type', 'manual')->count() > 0 ? min(2, $collections->where('type', 'manual')->count()) : 0);
				foreach ($manualCollections as $col) {
					$product->collections()->syncWithoutDetaching([$col->id]);
				}

				// media placeholder if available
				$path = 'demo/tshirt.jpg';
				if (Storage::disk('public')->exists($path)) {
					$product->addMedia(Storage::disk('public')->path($path))->toMediaCollection(config('shopper.media.storage.collection_name'));
				}

				// price base currency
				\Shop\Core\Models\Price::query()->updateOrCreate([
					'priceable_type' => $product->getMorphClass(),
					'priceable_id' => $product->id,
					'currency_id' => (int) (string) shopper_setting('default_currency_id'),
				], [
					'amount' => random_int(1000, 15000) / 100,
					'compare_amount' => random_int(0, 1) ? random_int(1100, 18000) / 100 : null,
					'cost_amount' => random_int(700, 12000) / 100,
				]);
			});

			// Ensure a default variant and inventory for every product
			$defaultInventoryId = \Shop\Core\Models\Inventory::query()->where('is_default', true)->value('id')
				?: \Shop\Core\Models\Inventory::query()->value('id');
			$defaultCurrencyId = (int) (string) shopper_setting('default_currency_id');
			foreach ($products as $product) {
				$variant = \App\Models\ProductVariant::query()->firstOrCreate(
					[
						'product_id' => $product->id,
						'name' => 'Default',
					],
					[
						'sku' => strtoupper(Str::random(12)),
						'allow_backorder' => false,
						'status' => 'active',
					]
				);

				// Variant price mirrors product price
				$productPrice = \Shop\Core\Models\Price::query()
					->where('priceable_type', $product->getMorphClass())
					->where('priceable_id', $product->id)
					->where('currency_id', $defaultCurrencyId)
					->first();

				if ($productPrice) {
					\Shop\Core\Models\Price::query()->updateOrCreate([
						'priceable_type' => $variant->getMorphClass(),
						'priceable_id' => $variant->id,
						'currency_id' => $defaultCurrencyId,
					], [
						'amount' => $productPrice->amount,
						'compare_amount' => $productPrice->compare_amount,
						'cost_amount' => $productPrice->cost_amount,
					]);
				}

				if ($defaultInventoryId) {
					DB::table('sh_variant_inventories')->upsert([
						[
							'variant_id' => $variant->id,
							'inventory_id' => (int) $defaultInventoryId,
							'stock' => random_int(5, 50),
							'reserved' => 0,
							'created_at' => now(),
							'updated_at' => now(),
						],
					], ['variant_id', 'inventory_id'], ['stock', 'reserved', 'updated_at']);
				}
			}

			// Reviews (only if table and expected columns exist)
			if (\Illuminate\Support\Facades\Schema::hasTable('sh_reviews') && \Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'rating')) {
				$productIds = \App\Models\Product::query()->pluck('id')->all();
				if (!empty($productIds)) {
					$userId = \App\Models\User::query()->inRandomOrder()->value('id');
					if (!$userId) {
						$userId = \App\Models\User::factory()->create()->id;
					}
					$now = now();
					$rows = [];
					for ($i = 0; $i < 300; $i++) {
						$randomProductId = $productIds[array_rand($productIds)];
						$row = [
							'title' => 'Review #' . ($i + 1),
							'content' => 'Demo review content #' . ($i + 1),
							'rating' => random_int(1, 5),
							'approved' => (bool) random_int(0, 1),
							'created_at' => $now,
							'updated_at' => $now,
						];
						if (\Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'user_id')) {
							$row['user_id'] = $userId;
						}
						if (\Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'is_recommended')) {
							$row['is_recommended'] = (bool) random_int(0, 1);
						}
						if (\Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'product_id')) {
							$row['product_id'] = $randomProductId;
						}
						if (
							\Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'reviewrateable_type') &&
							\Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'reviewrateable_id')
						) {
							$row['reviewrateable_type'] = \App\Models\Product::class;
							$row['reviewrateable_id'] = $randomProductId;
						}
						if (
							\Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'author_type') &&
							\Illuminate\Support\Facades\Schema::hasColumn('sh_reviews', 'author_id')
						) {
							$row['author_type'] = \App\Models\User::class;
							$row['author_id'] = $userId;
						}
						$rows[] = $row;
					}
					DB::table('sh_reviews')->insert($rows);
				}
			}

			// Demo addresses and orders
			$user = \App\Models\User::query()->first();
			if ($user) {
				$address = $user->addresses()->create([
					'last_name' => 'Doe',
					'first_name' => 'John',
					'company_name' => null,
					'street_address' => '123 Main St',
					'street_address_plus' => null,
					'postal_code' => '00000',
					'city' => 'Springfield',
					'phone_number' => '1234567890',
					'shipping_default' => true,
					'billing_default' => true,
					'country_id' => \Shop\Core\Models\Country::query()->where('cca2', 'LT')->value('id'),
				]);

				$currency = \Shop\Core\Models\Currency::query()->where('code', current_currency())->first()
					?: \Shop\Core\Models\Currency::query()->where('id', (int) (string) shopper_setting('default_currency_id'))->first();
				$zone = \Shop\Core\Models\Zone::query()->first();
				$carrier = \Shop\Core\Models\Carrier::query()->first();
				$payment = \Shop\Core\Models\PaymentMethod::query()->first();

				$productsForOrder = \App\Models\Product::query()->where('is_visible', true)->whereNotNull('published_at')->limit(3)->get();
				if ($productsForOrder->isNotEmpty() && $currency && $zone && $carrier && $payment) {
					$orderData = [
						'number' => 'WEB-' . Str::upper(Str::random(8)),
						'currency_code' => $currency->code,
						'channel_id' => \Shop\Core\Models\Channel::query()->value('id'),
						'zone_id' => $zone->id,
						'payment_method_id' => $payment->id,
					];
					if (\Illuminate\Support\Facades\Schema::hasColumn('sh_orders', 'user_id')) {
						$orderData['user_id'] = $user->id;
					}
					$order = \Shop\Core\Models\Order::query()->create($orderData);

					foreach ($productsForOrder as $p) {
						$amountCents = (int) round((optional($p->prices()->where('currency_id', $currency->id)->first())->amount ?? random_int(1000, 5000) / 100) * 100);
						$order->items()->create([
							'product_id' => $p->id,
							'product_type' => \App\Models\Product::class,
							'unit_price_amount' => $amountCents,
							'quantity' => 1,
							'name' => $p->name,
							'sku' => $p->sku ?? 'SKU-' . Str::upper(Str::random(6)),
						]);
					}

					$shippingData = [
						'last_name' => $address->last_name,
						'first_name' => $address->first_name,
						'company' => $address->company_name,
						'street_address' => $address->street_address,
						'street_address_plus' => $address->street_address_plus,
						'postal_code' => $address->postal_code,
						'city' => $address->city,
						'phone' => $address->phone_number,
						'country_name' => 'Lithuania',
					];
					if (\Illuminate\Support\Facades\Schema::hasColumn('sh_order_addresses', 'customer_id')) {
						$shippingData['customer_id'] = $user->id;
					}
					$order->shippingAddress()->create($shippingData);

					$billingData = [
						'last_name' => $address->last_name,
						'first_name' => $address->first_name,
						'company' => $address->company_name,
						'street_address' => $address->street_address,
						'street_address_plus' => $address->street_address_plus,
						'postal_code' => $address->postal_code,
						'city' => $address->city,
						'phone' => $address->phone_number,
						'country_name' => 'Lithuania',
					];
					if (\Illuminate\Support\Facades\Schema::hasColumn('sh_order_addresses', 'customer_id')) {
						$billingData['customer_id'] = $user->id;
					}
					$order->billingAddress()->create($billingData);

					$option = \Shop\Core\Models\CarrierOption::query()->first();
					if ($option) {
						// attach through pivot or relation if available; fallback: no-op for simplified schema
						if (method_exists($order, 'shippingOption')) {
							try {
								$order->shippingOption()->associate($option);
								$order->save();
							} catch (\Throwable $e) {
								// ignore if relation differs in current schema
							}
						}
					}

					$subtotalCents = (int) $order->items()->get()->sum(fn($i) => (int) $i->unit_price_amount * (int) $i->quantity);
					$updates = [];
					if (\Illuminate\Support\Facades\Schema::hasColumn('sh_orders', 'price_amount')) {
						$updates['price_amount'] = $subtotalCents;
					}
					if (\Illuminate\Support\Facades\Schema::hasColumn('sh_orders', 'subtotal')) {
						$updates['subtotal'] = $subtotalCents / 100;
					}
					if (\Illuminate\Support\Facades\Schema::hasColumn('sh_orders', 'grand_total')) {
						$updates['grand_total'] = ($subtotalCents / 100) + 9.99;
					}
					if (!empty($updates)) {
						$order->update($updates);
					}
				}
			}
		});
	}
}
