<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add additional columns to products table for enhanced Filament functionality
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('compare_price', 10, 2)->nullable()->after('price');
            $table->decimal('cost_price', 10, 2)->nullable()->after('compare_price');
            $table->string('barcode')->nullable()->after('sku');
            $table->boolean('track_inventory')->default(true)->after('manage_stock');
            $table->text('short_description')->nullable()->after('description');
            $table->json('metadata')->nullable()->after('seo_description');
            $table->timestamp('published_at')->nullable()->after('is_featured');
            $table->string('video_url')->nullable()->after('metadata');
        });

        // Add additional columns to users table for enhanced customer management
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('preferred_locale', 5)->default('lt')->after('gender');
            $table->boolean('is_active')->default(true)->after('preferred_locale');
            $table->boolean('accepts_marketing')->default(false)->after('is_active');
            $table->boolean('two_factor_enabled')->default(false)->after('accepts_marketing');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled');
            $table->json('preferences')->nullable()->after('last_login_at');
        });

        // Add additional columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('notes');
            $table->json('metadata')->nullable()->after('tracking_number');
            $table->string('locale', 5)->default('lt')->after('metadata');
            $table->decimal('weight', 8, 2)->nullable()->after('locale');
            $table->string('fulfillment_status')->default('unfulfilled')->after('weight');
        });

        // Create product_variants table for enhanced product management
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->json('attributes')->nullable(); // Store variant attributes as JSON
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['product_id', 'is_enabled']);
            $table->index(['sku']);
        });

        // Create attributes table for product attributes
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('text'); // text, number, select, boolean, color
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->json('options')->nullable(); // For select type attributes
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });

        // Create attribute_values table
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value');
            $table->string('slug');
            $table->string('color_code')->nullable(); // For color attributes
            $table->integer('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->unique(['attribute_id', 'slug']);
            $table->index(['attribute_id', 'is_enabled']);
        });

        // Create product_attributes pivot table
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->foreign('attribute_value_id')->references('id')->on('attribute_values')->onDelete('cascade');
            $table->unique(['product_id', 'attribute_id'], 'product_attribute_unique');
        });

        // Create customer_groups table for customer segmentation
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->json('conditions')->nullable(); // Conditions for automatic assignment
            $table->timestamps();

            $table->index(['is_enabled']);
        });

        // Create customer_group_user pivot table
        Schema::create('customer_group_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_group_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['customer_group_id', 'user_id']);
        });

        // Create addresses table for user addresses
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type')->default('shipping'); // shipping, billing
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->string('country', 2);
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'type']);
        });

        // Create wishlists table
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['user_id', 'product_id']);
        });

        // Create settings table for application settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['key']);
            $table->index(['is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('customer_group_user');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_variants');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number',
                'metadata',
                'locale',
                'weight',
                'fulfillment_status',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'date_of_birth',
                'gender',
                'preferred_locale',
                'is_active',
                'accepts_marketing',
                'two_factor_enabled',
                'last_login_at',
                'preferences',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'compare_price',
                'cost_price',
                'barcode',
                'track_inventory',
                'short_description',
                'metadata',
                'published_at',
                'video_url',
            ]);
        });
    }
};
