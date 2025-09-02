<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
                $table->boolean('track_inventory')->default(true)->after('stock_quantity');
                $table->integer('low_stock_threshold')->default(5)->after('track_inventory');
                $table->timestamp('available_from')->nullable()->after('low_stock_threshold');
                $table->timestamp('available_until')->nullable()->after('available_from');
            });
        }

        // Enhance categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                if (!Schema::hasColumn('categories', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('description');
                }
                if (!Schema::hasColumn('categories', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('categories', 'meta_keywords')) {
                    $table->json('meta_keywords')->nullable()->after('meta_description');
                }
                if (!Schema::hasColumn('categories', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_visible');
                }
                if (!Schema::hasColumn('categories', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_featured');
                }
                if (!Schema::hasColumn('categories', 'icon')) {
                    $table->string('icon')->nullable()->after('sort_order');
                }
                if (!Schema::hasColumn('categories', 'color')) {
                    $table->string('color')->nullable()->after('icon');
                }
            });
        }

        // Enhance brands table
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table): void {
                if (!Schema::hasColumn('brands', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('description');
                }
                if (!Schema::hasColumn('brands', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('brands', 'meta_keywords')) {
                    $table->json('meta_keywords')->nullable()->after('meta_description');
                }
                if (!Schema::hasColumn('brands', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_visible');
                }
                if (!Schema::hasColumn('brands', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('is_featured');
                }
                if (!Schema::hasColumn('brands', 'website')) {
                    $table->string('website')->nullable()->after('sort_order');
                }
                if (!Schema::hasColumn('brands', 'contact_email')) {
                    $table->string('contact_email')->nullable()->after('website');
                }
                if (!Schema::hasColumn('brands', 'contact_phone')) {
                    $table->string('contact_phone')->nullable()->after('contact_email');
                }
            });
        }

        // Activity log table is created by the Spatie Activity Log plugin migration

        // Create notifications table for admin notifications
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // Create failed jobs table
        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table): void {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        // Create personal access tokens table for API access
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table): void {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // Create filament notifications table
        if (!Schema::hasTable('filament_notifications')) {
            Schema::create('filament_notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable'); // This already creates the index
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // Create password reset tokens table
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table): void {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Create sessions table
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
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
            Schema::table('products', function (Blueprint $table): void {
                $table->index(['is_visible', 'is_featured']);
                $table->index(['available_from', 'available_until']);
                $table->index('sort_order');
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->index(['is_visible', 'is_featured']);
                $table->index('sort_order');
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table): void {
                $table->index(['is_visible', 'is_featured']);
                $table->index('sort_order');
            });
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
                    'barcode', 'track_inventory', 'low_stock_threshold',
                    'available_from', 'available_until'
                ]);
                $table->dropIndex(['is_visible', 'is_featured']);
                $table->dropIndex(['available_from', 'available_until']);
                $table->dropIndex(['sort_order']);
            });
        }

        // Remove added columns from categories table
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->dropColumn([
                    'meta_title', 'meta_description', 'meta_keywords',
                    'is_featured', 'sort_order', 'icon', 'color'
                ]);
                $table->dropIndex(['is_visible', 'is_featured']);
                $table->dropIndex(['sort_order']);
            });
        }

        // Remove added columns from brands table
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table): void {
                $table->dropColumn([
                    'meta_title', 'meta_description', 'meta_keywords',
                    'is_featured', 'sort_order', 'website', 'contact_email', 'contact_phone'
                ]);
                $table->dropIndex(['is_visible', 'is_featured']);
                $table->dropIndex(['sort_order']);
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

        // Drop created tables
        Schema::dropIfExists('filament_notifications');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
