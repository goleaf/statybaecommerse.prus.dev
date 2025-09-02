<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add enhanced user fields
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'accepts_marketing')) {
                $table->boolean('accepts_marketing')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('accepts_marketing');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('phone');
            }
        });

        // Add enhanced product fields
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('seo_description');
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('products', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable()->after('meta_description');
            }
        });

        // Add enhanced order fields
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('payment_reference');
            }
            if (!Schema::hasColumn('orders', 'estimated_delivery')) {
                $table->date('estimated_delivery')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('orders', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('estimated_delivery');
            }
        });

        // Create admin activity log table
        if (!Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('action');
                $table->string('resource_type');
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['resource_type', 'resource_id']);
                $table->index('action');
            });
        }

        // Create system notifications table
        if (!Schema::hasTable('system_notifications')) {
            Schema::create('system_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->json('data')->nullable();
                $table->enum('level', ['info', 'success', 'warning', 'error'])->default('info');
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'is_read']);
                $table->index(['type', 'level']);
                $table->index('created_at');
            });
        }

        // Create product analytics table
        if (!Schema::hasTable('product_analytics')) {
            Schema::create('product_analytics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->unsignedInteger('views')->default(0);
                $table->unsignedInteger('cart_additions')->default(0);
                $table->unsignedInteger('purchases')->default(0);
                $table->decimal('revenue', 10, 2)->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'date']);
                $table->index(['date', 'views']);
                $table->index(['date', 'revenue']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_analytics');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('admin_activity_logs');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'estimated_delivery', 'priority']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'accepts_marketing', 'two_factor_enabled', 'phone', 'date_of_birth']);
        });
    }
};
