<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Enhanced Settings System for Filament
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('display_name');
                $table->text('value')->nullable();
                $table->string('type')->default('string');  // string, boolean, integer, float, array, json
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

        // Enhanced Feature Flags System
        if (!Schema::hasTable('feature_flags')) {
            Schema::create('feature_flags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('key')->unique();
                $table->boolean('is_enabled')->default(false);
                $table->text('description')->nullable();
                $table->json('conditions')->nullable();  // User groups, roles, etc.
                $table->timestamp('enabled_at')->nullable();
                $table->timestamp('disabled_at')->nullable();
                $table->timestamps();

                $table->index('is_enabled');
                $table->index('key');
            });
        }

        // Enhanced Notification Templates
        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('key')->unique();
                $table->string('type');  // email, sms, push, database
                $table->string('event');  // order_created, user_registered, etc.
                $table->json('subject');  // Multilingual
                $table->json('content');  // Multilingual
                $table->json('variables')->nullable();  // Available variables
                $table->boolean('is_active')->default(true);
                $table->string('locale')->default('lt');
                $table->timestamps();

                $table->index(['type', 'event']);
                $table->index(['is_active', 'locale']);
            });
        }

        // Enhanced User Wishlists with better structure
        if (!Schema::hasTable('user_wishlists')) {
            Schema::create('user_wishlists', function (Blueprint $table) {
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
            Schema::create('wishlist_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wishlist_id')->constrained('user_wishlists')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
                $table->integer('quantity')->default(1);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['wishlist_id', 'product_id', 'variant_id']);
                $table->index('product_id');
            });
        }

        // Enhanced Cart System
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->string('session_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->json('product_snapshot')->nullable();  // Store product data at time of adding
                $table->timestamps();

                $table->index(['session_id', 'user_id']);
                $table->index('product_id');
                $table->index('created_at');
            });
        }

        // Enhanced Product Comparisons
        if (!Schema::hasTable('product_comparisons')) {
            Schema::create('product_comparisons', function (Blueprint $table) {
                $table->id();
                $table->string('session_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['session_id', 'product_id']);
                $table->unique(['user_id', 'product_id']);
                $table->index('product_id');
            });
        }

        // Enhanced Analytics System
        if (!Schema::hasTable('analytics_events')) {
            Schema::create('analytics_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_type');  // page_view, product_view, add_to_cart, etc.
                $table->string('session_id');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->json('properties');  // Event-specific data
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

        // Enhanced SEO System
        if (!Schema::hasTable('seo_data')) {
            Schema::create('seo_data', function (Blueprint $table) {
                $table->id();
                $table->morphs('seoable');  // Can be attached to any model
                $table->string('locale', 5);
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->text('keywords')->nullable();
                $table->string('canonical_url')->nullable();
                $table->json('meta_tags')->nullable();  // Additional meta tags
                $table->json('structured_data')->nullable();  // JSON-LD data
                $table->boolean('no_index')->default(false);
                $table->boolean('no_follow')->default(false);
                $table->timestamps();

                $table->unique(['seoable_type', 'seoable_id', 'locale']);
                $table->index(['locale', 'no_index']);
            });
        }

        // Enhanced Media Collections
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
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

        // Enhanced Reviews System
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
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

        // Enhanced Order System
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('currency');
                }
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status')->default('pending')->after('payment_method');
                }
                if (!Schema::hasColumn('orders', 'fulfillment_status')) {
                    $table->string('fulfillment_status')->default('pending')->after('payment_status');
                }
                if (!Schema::hasColumn('orders', 'tracking_number')) {
                    $table->string('tracking_number')->nullable()->after('fulfillment_status');
                }
                if (!Schema::hasColumn('orders', 'estimated_delivery')) {
                    $table->date('estimated_delivery')->nullable()->after('tracking_number');
                }
                if (!Schema::hasColumn('orders', 'customer_notes')) {
                    $table->text('customer_notes')->nullable()->after('estimated_delivery');
                }
                if (!Schema::hasColumn('orders', 'admin_notes')) {
                    $table->text('admin_notes')->nullable()->after('customer_notes');
                }
            });
        }

        // Enhanced Product System
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
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

        // Enhanced User System
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'avatar')) {
                    $table->string('avatar')->nullable()->after('email_verified_at');
                }
                if (!Schema::hasColumn('users', 'company')) {
                    $table->string('company')->nullable()->after('avatar');
                }
                if (!Schema::hasColumn('users', 'tax_number')) {
                    $table->string('tax_number')->nullable()->after('company');
                }
                if (!Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('tax_number');
                }
                if (!Schema::hasColumn('users', 'last_login_ip')) {
                    $table->ipAddress('last_login_ip')->nullable()->after('last_login_at');
                }
                if (!Schema::hasColumn('users', 'login_count')) {
                    $table->integer('login_count')->default(0)->after('last_login_ip');
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
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'login_count', 'last_login_ip', 'last_login_at',
                    'tax_number', 'company', 'avatar'
                ]);
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn([
                    'last_viewed_at', 'view_count', 'meta_description', 'meta_title'
                ]);
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn([
                    'admin_notes', 'customer_notes', 'estimated_delivery',
                    'tracking_number', 'fulfillment_status', 'payment_status', 'payment_method'
                ]);
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn([
                    'reported_count', 'helpful_count', 'is_verified_purchase', 'locale'
                ]);
            });
        }

        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                $table->dropColumn(['sort_order', 'is_featured', 'caption', 'alt_text']);
            });
        }
    }
};
