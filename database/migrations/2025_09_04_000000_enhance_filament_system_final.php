<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove legacy sh_ tables that are no longer needed
        $legacyTables = [
            'sh_addresses',
            'sh_attribute_values',
            'sh_attributes',
            'sh_customer_group_user',
            'sh_customer_groups',
            'sh_product_attributes',
        ];

        foreach ($legacyTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        // Enhance products table with additional e-commerce features
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_featured');
                }
                if (! Schema::hasColumn('products', 'gallery')) {
                    $table->json('gallery')->nullable()->after('sort_order');
                }
                if (! Schema::hasColumn('products', 'barcode')) {
                    $table->string('barcode')->nullable()->after('sku');
                }
                if (! Schema::hasColumn('products', 'track_inventory')) {
                    $table->boolean('track_inventory')->default(true)->after('manage_stock');
                }
                if (! Schema::hasColumn('products', 'video_url')) {
                    $table->string('video_url')->nullable()->after('metadata');
                }
                if (! Schema::hasColumn('products', 'available_from')) {
                    $table->timestamp('available_from')->nullable()->after('published_at');
                }
                if (! Schema::hasColumn('products', 'available_until')) {
                    $table->timestamp('available_until')->nullable()->after('available_from');
                }
            });
        }

        // Enhance categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'show_in_menu')) {
                    $table->boolean('show_in_menu')->default(true)->after('is_visible');
                }
                if (! Schema::hasColumn('categories', 'product_limit')) {
                    $table->integer('product_limit')->nullable()->after('show_in_menu');
                }
            });
        }

        // Enhance brands table
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (! Schema::hasColumn('brands', 'social_links')) {
                    $table->json('social_links')->nullable()->after('contact_phone');
                }
                if (! Schema::hasColumn('brands', 'is_premium')) {
                    $table->boolean('is_premium')->default(false)->after('is_featured');
                }
            });
        }

        // Create enhanced product analytics table
        if (! Schema::hasTable('product_analytics')) {
            Schema::create('product_analytics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->integer('views')->default(0);
                $table->integer('cart_additions')->default(0);
                $table->integer('purchases')->default(0);
                $table->integer('wishlist_additions')->default(0);
                $table->decimal('conversion_rate', 5, 4)->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'date']);
                $table->index(['date', 'views']);
                $table->index(['date', 'purchases']);
            });
        }

        // Create enhanced system notifications table
        if (! Schema::hasTable('system_notifications')) {
            Schema::create('system_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('type'); // info, warning, error, success
                $table->string('title');
                $table->text('message');
                $table->json('data')->nullable();
                $table->boolean('is_read')->default(false);
                $table->boolean('is_dismissible')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'is_read']);
                $table->index(['type', 'created_at']);
                $table->index(['expires_at']);
            });
        }

        // Create enhanced system logs table
        if (! Schema::hasTable('system_logs')) {
            Schema::create('system_logs', function (Blueprint $table) {
                $table->id();
                $table->string('level'); // debug, info, warning, error, critical
                $table->string('message');
                $table->json('context')->nullable();
                $table->string('channel')->nullable();
                $table->timestamp('logged_at');
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->index(['level', 'logged_at']);
                $table->index(['channel', 'logged_at']);
                $table->index(['user_id', 'logged_at']);
            });
        }

        // Create performance metrics table
        if (! Schema::hasTable('performance_metrics')) {
            Schema::create('performance_metrics', function (Blueprint $table) {
                $table->id();
                $table->string('metric_name');
                $table->string('metric_type'); // counter, gauge, histogram, summary
                $table->decimal('value', 15, 6);
                $table->json('labels')->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->index(['metric_name', 'recorded_at']);
                $table->index(['metric_type', 'recorded_at']);
                $table->index(['recorded_at']);
            });
        }

        // Create user preferences table
        if (! Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('key');
                $table->json('value');
                $table->timestamps();

                $table->unique(['user_id', 'key']);
                $table->index(['key']);
            });
        }

        // Create advanced search logs for analytics
        if (! Schema::hasTable('search_logs')) {
            Schema::create('search_logs', function (Blueprint $table) {
                $table->id();
                $table->string('query');
                $table->integer('results_count')->default(0);
                $table->string('ip_address')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->json('filters')->nullable();
                $table->timestamp('searched_at');
                $table->timestamps();

                $table->index(['query']);
                $table->index(['searched_at']);
                $table->index(['results_count']);
            });
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('product_analytics');

        // Remove added columns from existing tables
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn([
                    'sort_order', 'gallery', 'compare_price', 'cost_price',
                    'barcode', 'track_inventory', 'metadata', 'video_url',
                    'available_from', 'available_until',
                ]);
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn([
                    'icon', 'color', 'metadata', 'show_in_menu', 'product_limit',
                ]);
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn([
                    'contact_email', 'contact_phone', 'social_links',
                    'metadata', 'is_premium',
                ]);
            });
        }
    }
};
