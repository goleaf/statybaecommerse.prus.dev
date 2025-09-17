<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create brands table
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('website')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_enabled', 'name']);
                $table->index(['sort_order']);
            });
        }

        // Create categories table
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_visible')->default(true);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['is_visible', 'sort_order']);
                $table->index(['parent_id', 'sort_order']);
            });
        }

        // Create products table
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('short_description')->nullable();
                $table->string('sku')->unique();
                $table->text('summary')->nullable();
                $table->decimal('price', 15, 2)->nullable();
                $table->decimal('sale_price', 15, 2)->nullable();
                $table->decimal('compare_price', 15, 2)->nullable();
                $table->decimal('cost_price', 15, 2)->nullable();
                $table->boolean('manage_stock')->default(false);
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(0);
                $table->decimal('weight', 8, 2)->nullable();
                $table->decimal('length', 8, 2)->nullable();
                $table->decimal('width', 8, 2)->nullable();
                $table->decimal('height', 8, 2)->nullable();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->enum('type', ['simple', 'variable'])->default('simple');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['is_visible', 'published_at']);
                $table->index(['status', 'is_visible']);
                $table->index(['brand_id', 'is_visible']);
                $table->index(['is_featured', 'is_visible']);
            });
        }

        // Create product_categories pivot table
        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('category_id');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['product_id', 'category_id']);
            });
        }

        // Create collections table
        if (!Schema::hasTable('collections')) {
            Schema::create('collections', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_visible', 'sort_order']);
                $table->index(['is_enabled']);
            });
        }

        // Create product_collections pivot table
        if (!Schema::hasTable('product_collections')) {
            Schema::create('product_collections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('collection_id');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('collection_id')->references('id')->on('collections')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unique(['product_id', 'collection_id']);
            });
        }

        // Create orders table
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('number')->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('shipping_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('currency', 3)->default('EUR');
                $table->json('billing_address')->nullable();
                $table->json('shipping_address')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['status', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        // Create order_items table
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id');
                $table->string('product_name');
                $table->string('product_sku');
                $table->integer('quantity');
                $table->decimal('price', 15, 2);
                $table->decimal('total', 15, 2);
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        // Create reviews table
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('reviewer_name');
                $table->string('reviewer_email');
                $table->integer('rating');
                $table->string('title')->nullable();
                $table->text('content');
                $table->boolean('is_approved')->default(false);
                $table->string('locale', 8)->default('lt');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
                $table->index(['product_id', 'is_approved']);
                $table->index(['is_approved', 'created_at']);
                $table->index(['locale']);
            });
        }

        // Create coupons table
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['percentage', 'fixed']);
                $table->decimal('value', 15, 2);
                $table->decimal('minimum_amount', 15, 2)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('used_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['code', 'is_active']);
                $table->index(['is_active', 'starts_at', 'expires_at']);
            });
        }

        // Create cart_items table for session-based cart
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->string('session_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->integer('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_price', 15, 2);
                $table->json('product_snapshot')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['session_id']);
                $table->index(['user_id']);
            });
        }

        // Settings
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->string('display_name');
                $table->text('value')->nullable();
                $table->string('type')->default('string');
                $table->string('group')->default('general');
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false);
                $table->boolean('is_required')->default(false);
                $table->boolean('is_encrypted')->default(false);
                $table->timestamps();

                $table->index(['group', 'key']);
                $table->index('is_public');
            });
        }

        // Feature flags
        if (!Schema::hasTable('feature_flags')) {
            Schema::create('feature_flags', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('key')->unique();
                $table->boolean('is_enabled')->default(false);
                $table->text('description')->nullable();
                $table->json('conditions')->nullable();
                $table->timestamp('enabled_at')->nullable();
                $table->timestamp('disabled_at')->nullable();
                $table->timestamps();

                $table->index('is_enabled');
                $table->index('key');
            });
        }

        // Notification templates
        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->string('type');
                $table->string('event');
                $table->json('subject');
                $table->json('content');
                $table->json('variables')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('locale')->default('lt');
                $table->timestamps();

                $table->index(['type', 'event']);
                $table->index(['is_active', 'locale']);
            });
        }

        // Wishlists
        if (!Schema::hasTable('user_wishlists')) {
            Schema::create('user_wishlists', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name')->default('My Wishlist');
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
                $table->index('is_public');
            });
        }

        if (!Schema::hasTable('wishlist_items')) {
            Schema::create('wishlist_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('wishlist_id')->constrained('user_wishlists')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['wishlist_id', 'product_id', 'variant_id']);
                $table->index('product_id');
            });
        }

        // Cart items (extended)
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table): void {
                if (!Schema::hasColumn('cart_items', 'variant_id')) {
                    $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('cart_items', 'product_snapshot')) {
                    $table->json('product_snapshot')->nullable()->after('price');
                }
            });
        }

        // Product comparisons
        if (!Schema::hasTable('product_comparisons')) {
            Schema::create('product_comparisons', function (Blueprint $table): void {
                $table->id();
                $table->string('session_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->timestamps();

                $table->unique(['session_id', 'product_id']);
                $table->unique(['user_id', 'product_id']);
                $table->index('product_id');
            });
        }

        // Analytics events
        if (!Schema::hasTable('analytics_events')) {
            Schema::create('analytics_events', function (Blueprint $table): void {
                $table->id();
                $table->string('event_type');
                $table->string('session_id');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->json('properties');
                $table->string('url')->nullable();
                $table->string('referrer')->nullable();
                $table->string('user_agent')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->string('country_code', 2)->nullable();
                $table->timestamp('created_at');

                $table->index(['event_type', 'created_at']);
                $table->index(['session_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        // SEO data
        if (!Schema::hasTable('seo_data')) {
            Schema::create('seo_data', function (Blueprint $table): void {
                $table->id();
                $table->morphs('seoable');
                $table->string('locale', 5);
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->text('keywords')->nullable();
                $table->string('canonical_url')->nullable();
                $table->json('meta_tags')->nullable();
                $table->json('structured_data')->nullable();
                $table->boolean('no_index')->default(false);
                $table->boolean('no_follow')->default(false);
                $table->timestamps();

                $table->unique(['seoable_type', 'seoable_id', 'locale']);
                $table->index(['locale', 'no_index']);
            });
        }

        // Media enhancements
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table): void {
                if (!Schema::hasColumn('media', 'alt_text')) {
                    $table->string('alt_text')->nullable()->after('name');
                }
                if (!Schema::hasColumn('media', 'caption')) {
                    $table->text('caption')->nullable()->after('alt_text');
                }
                if (!Schema::hasColumn('media', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('caption');
                }
                if (!Schema::hasColumn('media', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_featured');
                }
            });
        }

        // Reviews enhancements
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                if (!Schema::hasColumn('reviews', 'locale')) {
                    $table->string('locale', 5)->default('lt')->after('rating');
                }
                if (!Schema::hasColumn('reviews', 'is_verified_purchase')) {
                    $table->boolean('is_verified_purchase')->default(false)->after('locale');
                }
                if (!Schema::hasColumn('reviews', 'helpful_count')) {
                    $table->integer('helpful_count')->default(0)->after('is_verified_purchase');
                }
                if (!Schema::hasColumn('reviews', 'reported_count')) {
                    $table->integer('reported_count')->default(0)->after('helpful_count');
                }
            });
        }

        // Orders enhancements
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                foreach ([
                    'payment_method' => 'string',
                    'payment_status' => 'string',
                    'fulfillment_status' => 'string',
                    'tracking_number' => 'string',
                    'estimated_delivery' => 'date',
                    'customer_notes' => 'text',
                    'admin_notes' => 'text',
                ] as $name => $type) {
                    if (!Schema::hasColumn('orders', $name)) {
                        match ($type) {
                            'string' => $table->string($name)->nullable()->after('currency'),
                            'date' => $table->date($name)->nullable()->after('currency'),
                            'text' => $table->text($name)->nullable()->after('currency'),
                            default => null,
                        };
                    }
                }
            });
        }

        // Products enhancements
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (!Schema::hasColumn('products', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('description');
                }
                if (!Schema::hasColumn('products', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('products', 'view_count')) {
                    $table->bigInteger('view_count')->default(0)->after('meta_description');
                }
                if (!Schema::hasColumn('products', 'last_viewed_at')) {
                    $table->timestamp('last_viewed_at')->nullable()->after('view_count');
                }
            });
        }

        // Users enhancements
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                foreach ([
                    'avatar' => 'string',
                    'company' => 'string',
                    'tax_number' => 'string',
                    'last_login_at' => 'timestamp',
                    'last_login_ip' => 'ipAddress',
                    'login_count' => 'integer',
                ] as $name => $type) {
                    if (!Schema::hasColumn('users', $name)) {
                        match ($type) {
                            'string' => $table->string($name)->nullable()->after('email_verified_at'),
                            'timestamp' => $table->timestamp($name)->nullable()->after('email_verified_at'),
                            'ipAddress' => $table->ipAddress($name)->nullable()->after('email_verified_at'),
                            'integer' => $table->integer($name)->default(0)->after('email_verified_at'),
                            default => null,
                        };
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('product_comparisons');
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('seo_data');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('settings');

        // Remove added columns (in reverse order)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                foreach (['login_count', 'last_login_ip', 'last_login_at', 'tax_number', 'company', 'avatar'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                foreach (['last_viewed_at', 'view_count', 'meta_description', 'meta_title'] as $col) {
                    if (Schema::hasColumn('products', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                foreach ([
                    'admin_notes',
                    'customer_notes',
                    'estimated_delivery',
                    'tracking_number',
                    'fulfillment_status',
                    'payment_status',
                    'payment_method',
                ] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                foreach (['reported_count', 'helpful_count', 'is_verified_purchase', 'locale'] as $col) {
                    if (Schema::hasColumn('reviews', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table): void {
                foreach (['sort_order', 'is_featured', 'caption', 'alt_text'] as $col) {
                    if (Schema::hasColumn('media', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
