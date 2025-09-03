<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add additional fields to existing tables for enhanced Filament functionality
        
        // Enhance products table
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'meta_title')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->string('meta_title')->nullable()->after('description');
                $table->text('meta_description')->nullable()->after('meta_title');
                $table->json('meta_keywords')->nullable()->after('meta_description');
                $table->boolean('is_featured')->default(false)->after('is_visible');
                $table->integer('sort_order')->default(0)->after('is_featured');
                $table->json('gallery')->nullable()->after('sort_order');
                $table->decimal('compare_price', 10, 2)->nullable()->after('price');
                $table->string('barcode')->nullable()->after('sku');
                $table->boolean('manage_stock')->default(true)->after('stock_quantity');
                $table->integer('low_stock_threshold')->default(5)->after('manage_stock');
                $table->timestamp('published_at')->nullable()->after('low_stock_threshold');
            });
        }

        // Enhance categories table
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'meta_title')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->string('meta_title')->nullable()->after('description');
                $table->text('meta_description')->nullable()->after('meta_title');
                $table->json('meta_keywords')->nullable()->after('meta_description');
                $table->boolean('is_featured')->default(false)->after('is_visible');
                $table->integer('sort_order')->default(0)->after('is_featured');
                $table->string('icon')->nullable()->after('sort_order');
                $table->string('color')->nullable()->after('icon');
            });
        }

        // Enhance brands table
        if (Schema::hasTable('brands') && !Schema::hasColumn('brands', 'meta_title')) {
            Schema::table('brands', function (Blueprint $table): void {
                $table->string('meta_title')->nullable()->after('description');
                $table->text('meta_description')->nullable()->after('meta_title');
                $table->json('meta_keywords')->nullable()->after('meta_description');
                $table->boolean('is_featured')->default(false)->after('is_visible');
                $table->integer('sort_order')->default(0)->after('is_featured');
                $table->string('website')->nullable()->after('sort_order');
                $table->string('contact_email')->nullable()->after('website');
                $table->string('contact_phone')->nullable()->after('contact_email');
            });
        }

        // Enhance users table for admin features
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'avatar_url')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('avatar_url')->nullable()->after('email_verified_at');
                $table->timestamp('last_login_at')->nullable()->after('avatar_url');
                $table->string('last_login_ip')->nullable()->after('last_login_at');
                $table->boolean('is_admin')->default(false)->after('last_login_ip');
                $table->boolean('is_active')->default(true)->after('is_admin');
                $table->json('preferences')->nullable()->after('is_active');
                $table->string('timezone')->default('UTC')->after('preferences');
            });
        }

        // Add indexes for better performance
        if (Schema::hasTable('products')) {
            try {
                Schema::table('products', function (Blueprint $table): void {
                    $table->index(['is_visible', 'is_featured'], 'products_visibility_featured_index');
                    $table->index('published_at', 'products_published_at_index');
                    $table->index('sort_order', 'products_sort_order_index');
                });
            } catch (\Exception $e) {
                // Indexes may already exist
            }
        }

        if (Schema::hasTable('categories')) {
            try {
                Schema::table('categories', function (Blueprint $table): void {
                    $table->index(['is_visible', 'is_featured'], 'categories_visibility_featured_index');
                    $table->index('sort_order', 'categories_sort_order_index');
                });
            } catch (\Exception $e) {
                // Indexes may already exist
            }
        }

        if (Schema::hasTable('brands')) {
            try {
                Schema::table('brands', function (Blueprint $table): void {
                    $table->index(['is_visible', 'is_featured'], 'brands_visibility_featured_index');
                    $table->index('sort_order', 'brands_sort_order_index');
                });
            } catch (\Exception $e) {
                // Indexes may already exist
            }
        }
    }

    public function down(): void
    {
        // Remove added columns from products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn([
                    'meta_title', 'meta_description', 'meta_keywords',
                    'is_featured', 'sort_order', 'gallery', 'compare_price',
                    'barcode', 'manage_stock', 'low_stock_threshold',
                    'published_at'
                ]);
            });
        }

        // Remove added columns from categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropColumn([
                    'meta_title', 'meta_description', 'meta_keywords',
                    'is_featured', 'sort_order', 'icon', 'color'
                ]);
            });
        }

        // Remove added columns from brands table
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table): void {
                $table->dropColumn([
                    'meta_title', 'meta_description', 'meta_keywords',
                    'is_featured', 'sort_order', 'website', 'contact_email', 'contact_phone'
                ]);
            });
        }

        // Remove added columns from users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn([
                    'avatar_url', 'last_login_at', 'last_login_ip',
                    'is_admin', 'is_active', 'preferences', 'timezone'
                ]);
            });
        }
    }
};
