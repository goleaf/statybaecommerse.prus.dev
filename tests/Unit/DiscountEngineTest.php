<?php

use App\Services\Discounts\DiscountEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses()->group('engine');

beforeEach(function () {
    // Run the package/app migrations to ensure tables exist
    $this->artisan('migrate', ['--force' => true]);
    
    // Clean up any existing data
    if (Schema::hasTable('sh_discount_codes')) {
        DB::table('sh_discount_codes')->truncate();
    }
    if (Schema::hasTable('sh_discounts')) {
        DB::table('sh_discounts')->truncate();
    }
    if (Schema::hasTable('sh_orders')) {
        DB::table('sh_orders')->truncate();
    }
    if (!Schema::hasTable('sh_discounts')) {
        Schema::create('sh_discounts', function ($table) {
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
        Schema::table('sh_discounts', function ($table) {
            if (!Schema::hasColumn('sh_discounts', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('sh_discounts', 'stacking_policy')) {
                $table->string('stacking_policy')->default('stack');
            }
            if (!Schema::hasColumn('sh_discounts', 'first_order_only')) {
                $table->boolean('first_order_only')->default(false);
            }
            if (!Schema::hasColumn('sh_discounts', 'code')) {
                $table->string('code')->nullable();
            }
            if (!Schema::hasColumn('sh_discounts', 'apply_to')) {
                $table->string('apply_to')->nullable();
            }
            if (!Schema::hasColumn('sh_discounts', 'min_required')) {
                $table->decimal('min_required', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('sh_discounts', 'eligibility')) {
                $table->string('eligibility')->nullable();
            }
        });
    }
    if (!Schema::hasTable('sh_discount_codes')) {
        Schema::create('sh_discount_codes', function ($table) {
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
            Schema::table('sh_discount_codes', function ($table) {
                $table->unique('code', 'sh_discount_codes_code_unique');
            });
        } catch (Exception $e) {
            // Index already exists, ignore the error
        }
    }
    if (!Schema::hasTable('sh_orders')) {
        Schema::create('sh_orders', function ($table) {
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
    if (!Schema::hasTable('sh_products')) {
        Schema::create('sh_products', function ($table) {
            $table->id();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();
        });
    }
    if (!Schema::hasTable('sh_category_product')) {
        Schema::create('sh_category_product', function ($table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('product_id');
        });
    }
    if (!Schema::hasTable('sh_collection_product')) {
        Schema::create('sh_collection_product', function ($table) {
            $table->unsignedBigInteger('collection_id');
            $table->unsignedBigInteger('product_id');
        });
    }
    if (!Schema::hasTable('users')) {
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
        'type' => 'percentage',
        'value' => 10,
        'status' => 'active',
        'stacking_policy' => 'stack',
        'code' => 'ANY',
        'apply_to' => 'cart',
        'min_required' => 0,
        'eligibility' => 'all',
        'created_at' => now(),
        'updated_at' => now(),
    ];
    if (Schema::hasColumn('sh_discounts', 'start_at')) {
        $data['start_at'] = now()->subDay();
    }
    if (Schema::hasColumn('sh_discounts', 'end_at')) {
        $data['end_at'] = now()->addDay();
    }
    if (Schema::hasColumn('sh_discounts', 'starts_at')) {
        $data['starts_at'] = now()->subDay();
    }
    if (Schema::hasColumn('sh_discounts', 'ends_at')) {
        $data['ends_at'] = now()->addDay();
    }
    $discountId = DB::table('sh_discounts')->insertGetId($data);
    DB::table('sh_discount_codes')->insert([
        'discount_id' => $discountId,
        'code' => 'TEST10',
        'max_uses' => 10,
        'usage_count' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $engine = app(DiscountEngine::class);
    $result = $engine->evaluate([
        'currency_code' => 'EUR',
        'zone_id' => 1,
        'code' => 'TEST10',
        'now' => now(),
        'cart' => [
            'subtotal' => 100.0,
            'items' => [],
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
    $data2 = [
        'type' => 'fixed',
        'value' => 5,
        'status' => 'active',
        'first_order_only' => true,
        'stacking_policy' => 'stack',
        'code' => 'FIRST',
        'apply_to' => 'cart',
        'created_at' => now(),
        'updated_at' => now(),
        'min_required' => 0,
        'eligibility' => 'all',
    ];
    if (Schema::hasColumn('sh_discounts', 'start_at')) {
        $data2['start_at'] = now()->subDay();
    }
    if (Schema::hasColumn('sh_discounts', 'end_at')) {
        $data2['end_at'] = now()->addDay();
    }
    if (Schema::hasColumn('sh_discounts', 'starts_at')) {
        $data2['starts_at'] = now()->subDay();
    }
    if (Schema::hasColumn('sh_discounts', 'ends_at')) {
        $data2['ends_at'] = now()->addDay();
    }
    $did = DB::table('sh_discounts')->insertGetId($data2);
    $engine = app(DiscountEngine::class);
    // First time
    $r1 = $engine->evaluate(['user_id' => $uid, 'currency_code' => 'EUR', 'zone_id' => 1, 'now' => now(), 'cart' => ['subtotal' => 20, 'items' => []]]);
    expect($r1['discount_total_amount'])->toBe(5.0);
    // Simulate order
    DB::table('sh_orders')->insert(['customer_id' => $uid, 'status' => 'completed', 'currency_code' => 'EUR', 'subtotal_amount' => 0, 'discount_total_amount' => 0, 'tax_total_amount' => 0, 'shipping_total_amount' => 0, 'grand_total_amount' => 0, 'number' => 'X', 'created_at' => now(), 'updated_at' => now()]);
    $r2 = $engine->evaluate(['user_id' => $uid, 'currency_code' => 'EUR', 'zone_id' => 1, 'now' => now(), 'cart' => ['subtotal' => 20, 'items' => []]]);
    expect($r2['discount_total_amount'])->toBe(0.0);
});
