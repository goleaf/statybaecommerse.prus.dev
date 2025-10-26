<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Discounts\DiscountEngine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class)->group('engine');

beforeEach(function () {
    // Run the package/app migrations to ensure tables exist
    Artisan::call('migrate', ['--force' => true]);

    // Clean up any existing data - disable foreign key checks for SQLite
    DB::statement('PRAGMA foreign_keys=OFF');

    if (Schema::hasTable('discount_codes')) {
        DB::table('discount_codes')->delete();
    }
    if (Schema::hasTable('discounts')) {
        DB::table('discounts')->delete();
    }
    if (Schema::hasTable('orders')) {
        DB::table('orders')->delete();
    }
    if (Schema::hasTable('discount_redemptions')) {
        DB::table('discount_redemptions')->delete();
    }

    // Re-enable foreign key checks
    DB::statement('PRAGMA foreign_keys=ON');
    if (! Schema::hasTable('discounts')) {
        Schema::create('discounts', function ($table) {
            $table->id();
            $table->string('type')->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('stacking_policy')->default('stack');
            $table->boolean('first_order_only')->default(false);
            $table->boolean('free_shipping')->default(false);
            $table->boolean('applies_to_shipping')->default(false);
            $table->unsignedInteger('priority')->default(100);
            // columns present in app schema that may be NOT NULL in vendor
            $table->string('code')->nullable();
            $table->string('apply_to')->nullable();
            $table->decimal('min_required', 12, 2)->default(0);
            $table->string('eligibility')->nullable();
            $table->string('weekday_mask')->nullable();
            $table->text('time_window')->nullable();
            $table->text('channel_restrictions')->nullable();
            $table->text('currency_restrictions')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    } else {
        Schema::table('discounts', function ($table) {
            if (! Schema::hasColumn('discounts', 'status')) {
                $table->string('status')->nullable();
            }
            if (! Schema::hasColumn('discounts', 'stacking_policy')) {
                $table->string('stacking_policy')->default('stack');
            }
            if (! Schema::hasColumn('discounts', 'first_order_only')) {
                $table->boolean('first_order_only')->default(false);
            }
            if (! Schema::hasColumn('discounts', 'code')) {
                $table->string('code')->nullable();
            }
            if (! Schema::hasColumn('discounts', 'apply_to')) {
                $table->string('apply_to')->nullable();
            }
            if (! Schema::hasColumn('discounts', 'min_required')) {
                $table->decimal('min_required', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('discounts', 'eligibility')) {
                $table->string('eligibility')->nullable();
            }
        });
    }
    if (! Schema::hasTable('discount_codes')) {
        Schema::create('discount_codes', function ($table) {
            $table->id();
            $table->unsignedBigInteger('discount_id');
            $table->string('code');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
        });

        // Add unique index if it doesn't exist
        try {
            Schema::table('discount_codes', function ($table) {
                $table->unique('code', 'discount_codes_code_unique');
            });
        } catch (Exception $e) {
            // Index already exists, ignore the error
        }
    }
    if (! Schema::hasTable('orders')) {
        Schema::create('orders', function ($table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('status')->default('placed');
            $table->char('currency_code', 3)->default('EUR');
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_total_amount', 12, 2)->default(0);
            $table->decimal('tax_total_amount', 12, 2)->default(0);
            $table->decimal('shipping_total_amount', 12, 2)->default(0);
            $table->decimal('grand_total_amount', 12, 2)->default(0);
            $table->string('number')->nullable();
            $table->timestamps();
        });
    }
    // Minimal product table and pivots used by engine scoping
    if (! Schema::hasTable('products')) {
        Schema::create('products', function ($table) {
            $table->id();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();
        });
    }
    if (! Schema::hasTable('product_categories')) {
        Schema::create('product_categories', function ($table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');
        });
    }
    if (! Schema::hasTable('product_collections')) {
        Schema::create('product_collections', function ($table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('collection_id');
        });
    }
    if (! Schema::hasTable('users')) {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }
});

it('applies percentage cart discount with code', function () {
    // seed minimal discount
    $data = [
        'name'            => 'Test Discount',
        'type'            => 'percentage',
        'value'           => 10,
        'status'          => 'active',
        'stacking_policy' => 'stack',
        'code'            => 'ANY',
        'apply_to'        => 'cart',
        'min_required'    => 0,
        'eligibility'     => 'all',
        'created_at'      => now(),
        'updated_at'      => now(),
    ];
    if (Schema::hasColumn('discounts', 'start_at')) {
        $data['start_at'] = now()->subDay();
    }
    if (Schema::hasColumn('discounts', 'end_at')) {
        $data['end_at'] = now()->addDay();
    }
    if (Schema::hasColumn('discounts', 'starts_at')) {
        $data['starts_at'] = now()->subDay();
    }
    if (Schema::hasColumn('discounts', 'ends_at')) {
        $data['ends_at'] = now()->addDay();
    }
    $discountId = DB::table('discounts')->insertGetId($data);
    $codeColumns = Schema::getColumnListing('discount_codes');
    $codePayload = [
        'discount_id' => $discountId,
        'code'        => 'TEST10',
        'created_at'  => now(),
        'updated_at'  => now(),
    ];
    if (in_array('usage_limit', $codeColumns, true)) {
        $codePayload['usage_limit'] = 10;
    }
    if (in_array('usage_count', $codeColumns, true)) {
        $codePayload['usage_count'] = 0;
    }
    if (in_array('status', $codeColumns, true)) {
        $codePayload['status'] = 'active';
    }
    if (in_array('is_active', $codeColumns, true)) {
        $codePayload['is_active'] = true;
    }
    DB::table('discount_codes')->insert($codePayload);

    $engine = app(DiscountEngine::class);
    $result = $engine->evaluate([
        'currency_code' => 'EUR',
        'zone_id'       => 1,
        'code'          => 'TEST10',
        'now'           => now(),
        'cart'          => [
            'subtotal' => 100.0,
            'items'    => [],
        ],
    ]);

    expect($result['discount_total_amount'])->toBe(10.0);
});

it('respects first order only flag', function () {
    $userInsert = ['email' => 'a' . rand() . '@x.tld', 'password' => bcrypt('x'), 'created_at' => now(), 'updated_at' => now()];
    if (Schema::hasColumn('users', 'name')) {
        $userInsert['name'] = 'A B';
    } else {
        $userInsert['first_name'] = 'A';
        $userInsert['last_name'] = 'B';
    }
    $uid = DB::table('users')->insertGetId($userInsert);
    $customerId = $uid;
    if (class_exists(Customer::class) && Schema::hasTable('customers') && Schema::hasColumn('customers', 'user_id')) {
        $customer = Customer::factory()->create(['user_id' => $uid]);
        $customerId = $customer->id;
    }
    $data2 = [
        'name'             => 'First Order Discount',
        'type'             => 'fixed',
        'value'            => 5,
        'status'           => 'active',
        'first_order_only' => true,
        'stacking_policy'  => 'stack',
        'code'             => 'FIRST',
        'apply_to'         => 'cart',
        'created_at'       => now(),
        'updated_at'       => now(),
        'min_required'     => 0,
        'eligibility'      => 'all',
    ];
    if (Schema::hasColumn('discounts', 'start_at')) {
        $data2['start_at'] = now()->subDay();
    }
    if (Schema::hasColumn('discounts', 'end_at')) {
        $data2['end_at'] = now()->addDay();
    }
    if (Schema::hasColumn('discounts', 'starts_at')) {
        $data2['starts_at'] = now()->subDay();
    }
    if (Schema::hasColumn('discounts', 'ends_at')) {
        $data2['ends_at'] = now()->addDay();
    }
    $did = DB::table('discounts')->insertGetId($data2);
    $engine = app(DiscountEngine::class);
    // First time
    $r1 = $engine->evaluate(['user_id' => $uid, 'currency_code' => 'EUR', 'zone_id' => 1, 'now' => now(), 'cart' => ['subtotal' => 20, 'items' => []]]);
    expect($r1['discount_total_amount'])->toBe(5.0);
    // Simulate order
    $orderColumns = Schema::getColumnListing('orders');
    $orderInsert = [
        'status'     => 'completed',
        'number'     => 'X',
        'created_at' => now(),
        'updated_at' => now(),
    ];
    if (in_array('customer_id', $orderColumns, true)) {
        $orderInsert['customer_id'] = $customerId;
    }
    if (in_array('user_id', $orderColumns, true)) {
        $orderInsert['user_id'] = $uid;
    }
    if (in_array('currency_code', $orderColumns, true)) {
        $orderInsert['currency_code'] = 'EUR';
    } elseif (in_array('currency', $orderColumns, true)) {
        $orderInsert['currency'] = 'EUR';
    }
    if (in_array('subtotal_amount', $orderColumns, true)) {
        $orderInsert['subtotal_amount'] = 0;
    } elseif (in_array('subtotal', $orderColumns, true)) {
        $orderInsert['subtotal'] = 0;
    }
    if (in_array('discount_total_amount', $orderColumns, true)) {
        $orderInsert['discount_total_amount'] = 0;
    } elseif (in_array('discount_amount', $orderColumns, true)) {
        $orderInsert['discount_amount'] = 0;
    }
    if (in_array('tax_total_amount', $orderColumns, true)) {
        $orderInsert['tax_total_amount'] = 0;
    } elseif (in_array('tax_amount', $orderColumns, true)) {
        $orderInsert['tax_amount'] = 0;
    }
    if (in_array('shipping_total_amount', $orderColumns, true)) {
        $orderInsert['shipping_total_amount'] = 0;
    } elseif (in_array('shipping_amount', $orderColumns, true)) {
        $orderInsert['shipping_amount'] = 0;
    }
    if (in_array('grand_total_amount', $orderColumns, true)) {
        $orderInsert['grand_total_amount'] = 0;
    } elseif (in_array('total', $orderColumns, true)) {
        $orderInsert['total'] = 0;
    }
    DB::statement('PRAGMA foreign_keys=OFF');
    DB::table('orders')->insert($orderInsert);
    DB::statement('PRAGMA foreign_keys=ON');
    $r2 = $engine->evaluate(['user_id' => $uid, 'currency_code' => 'EUR', 'zone_id' => 1, 'now' => now(), 'cart' => ['subtotal' => 20, 'items' => []]]);
    expect($r2['discount_total_amount'])->toBe(0.0);
});

it('applies category scoped minimum amount discount', function () {
    // Prepare a product that belongs to a qualifying category for the condition.
    $category = Category::factory()->create();
    $product = Product::factory()->create(['brand_id' => null]);
    DB::table('product_categories')->insert(['product_id' => $product->id, 'category_id' => $category->id]);

    // Provision the discount with a minimum amount threshold and category scope condition.
    $discountData = [
        'name'            => 'Category Boost 20%',
        'type'            => 'percentage',
        'value'           => 20,
        'status'          => 'active',
        'stacking_policy' => 'stack',
        'priority'        => 10,
        'created_at'      => now(),
        'updated_at'      => now(),
    ];
    if (Schema::hasColumn('discounts', 'minimum_amount')) {
        $discountData['minimum_amount'] = 50;
    }
    if (Schema::hasColumn('discounts', 'min_required')) {
        $discountData['min_required'] = 50;
    }
    $discountId = DB::table('discounts')->insertGetId($discountData);

    DB::table('discount_conditions')->insert([
        'discount_id' => $discountId,
        'type'        => 'category',
        'operator'    => 'in_array',
        'value'       => json_encode([$category->id]),
        'position'    => 0,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    $engine = app(DiscountEngine::class);
    $result = $engine->evaluate([
        'currency_code' => 'EUR',
        'now'           => now(),
        'cart'          => [
            'subtotal' => 80.0,
            'items'    => [
                ['product_id' => $product->id, 'variant_id' => null, 'quantity' => 2, 'unit_price' => 40.0],
            ],
        ],
    ]);

    expect($result['discount_total_amount'])->toBe(16.0);
});

it('applies free shipping discount type to shipping total', function () {
    // Seed a standalone free shipping discount that becomes eligible immediately.
    $discountData = [
        'name'            => 'Logistics Holiday',
        'type'            => 'free_shipping',
        'value'           => 0,
        'status'          => 'active',
        'stacking_policy' => 'stack',
        'free_shipping'   => true,
        'created_at'      => now(),
        'updated_at'      => now(),
    ];
    if (Schema::hasColumn('discounts', 'minimum_amount')) {
        $discountData['minimum_amount'] = 0;
    }
    if (Schema::hasColumn('discounts', 'min_required')) {
        $discountData['min_required'] = 0;
    }
    $discountId = DB::table('discounts')->insertGetId($discountData);

    $engine = app(DiscountEngine::class);
    $result = $engine->evaluate([
        'currency_code' => 'EUR',
        'now'           => now(),
        'cart'          => [
            'subtotal' => 40.0,
            'items'    => [],
        ],
        'shipping' => [
            'base_amount' => 9.99,
        ],
    ]);

    expect($result['discount_total_amount'])->toBe(0.0)
        ->and($result['shipping']['discount_amount'])->toBe(9.99);
});

it('blocks discount codes when global or per-user usage limits are exceeded', function () {
    // Create a customer to exercise per-user limit logic.
    $userInsert = ['email' => 'usage-' . uniqid() . '@test.dev', 'password' => bcrypt('secret'), 'created_at' => now(), 'updated_at' => now()];
    if (Schema::hasColumn('users', 'name')) {
        $userInsert['name'] = 'Limit Tester';
    } else {
        $userInsert['first_name'] = 'Limit';
        $userInsert['last_name'] = 'Tester';
    }
    $userId = DB::table('users')->insertGetId($userInsert);

    // Register a simple fixed discount paired with a code.
    $discountId = DB::table('discounts')->insertGetId([
        'name'            => 'Limit Sensitive',
        'type'            => 'fixed',
        'value'           => 15,
        'status'          => 'active',
        'stacking_policy' => 'single_best',
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    $codeColumns = Schema::getColumnListing('discount_codes');
    $codeData = [
        'discount_id' => $discountId,
        'code'        => 'LIMITED',
        'created_at'  => now(),
        'updated_at'  => now(),
    ];
    if (in_array('usage_limit', $codeColumns, true)) {
        $codeData['usage_limit'] = 1;
    }
    if (in_array('usage_limit_per_user', $codeColumns, true)) {
        $codeData['usage_limit_per_user'] = 1;
    }
    if (in_array('usage_count', $codeColumns, true)) {
        $codeData['usage_count'] = 1;
    }
    if (in_array('status', $codeColumns, true)) {
        $codeData['status'] = 'active';
    }
    if (in_array('is_active', $codeColumns, true)) {
        $codeData['is_active'] = true;
    }
    $codeId = DB::table('discount_codes')->insertGetId($codeData);

    $engine = app(DiscountEngine::class);
    $baseContext = [
        'currency_code' => 'EUR',
        'code'          => 'LIMITED',
        'now'           => now(),
        'cart'          => [
            'subtotal' => 100.0,
            'items'    => [],
        ],
    ];

    // Usage limit already consumed => expect no discount amount.
    $result1 = $engine->evaluate($baseContext);
    expect($result1['discount_total_amount'])->toBe(0.0);

    // Reset global counter but log a redemption for the same user to trigger per-user limit.
    $updateColumns = [];
    if (in_array('usage_limit', $codeColumns, true)) {
        $updateColumns['usage_limit'] = 5;
    }
    if (in_array('usage_count', $codeColumns, true)) {
        $updateColumns['usage_count'] = 0;
    }
    DB::table('discount_codes')->where('id', $codeId)->update($updateColumns);
    DB::table('discount_redemptions')->insert([
        'discount_id'   => $discountId,
        'code_id'       => $codeId,
        'user_id'       => $userId,
        'amount_saved'  => 15,
        'currency_code' => 'EUR',
        'status'        => 'redeemed',
        'redeemed_at'   => now()->subDay(),
        'created_at'    => now()->subDay(),
        'updated_at'    => now()->subDay(),
    ]);

    $result2 = $engine->evaluate($baseContext + ['user_id' => $userId]);
    expect($result2['discount_total_amount'])->toBe(0.0);

    // Alternate user should be able to redeem once limits reset.
    $otherUserInsert = ['email' => 'usage-' . uniqid() . '@other.dev', 'password' => bcrypt('secret'), 'created_at' => now(), 'updated_at' => now()];
    if (Schema::hasColumn('users', 'name')) {
        $otherUserInsert['name'] = 'Alt Tester';
    } else {
        $otherUserInsert['first_name'] = 'Alt';
        $otherUserInsert['last_name'] = 'Tester';
    }
    $otherUserId = DB::table('users')->insertGetId($otherUserInsert);

    $result3 = $engine->evaluate($baseContext + ['user_id' => $otherUserId]);
    expect($result3['discount_total_amount'])->toBe(15.0);
});
