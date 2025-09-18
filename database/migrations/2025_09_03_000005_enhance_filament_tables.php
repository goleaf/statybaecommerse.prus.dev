<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns already exist before adding them
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'compare_price')) {
                    $table->decimal('compare_price', 10, 2)->nullable()->after('price');
                }
                if (! Schema::hasColumn('products', 'cost_price')) {
                    $table->decimal('cost_price', 10, 2)->nullable()->after('compare_price');
                }
                if (! Schema::hasColumn('products', 'barcode')) {
                    $table->string('barcode')->nullable()->after('sku');
                }
                if (! Schema::hasColumn('products', 'track_inventory')) {
                    $table->boolean('track_inventory')->default(true)->after('manage_stock');
                }
                if (! Schema::hasColumn('products', 'short_description')) {
                    $table->text('short_description')->nullable()->after('description');
                }
                if (! Schema::hasColumn('products', 'metadata')) {
                    $table->json('metadata')->nullable()->after('seo_description');
                }
                if (! Schema::hasColumn('products', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('is_featured');
                }
                if (! Schema::hasColumn('products', 'video_url')) {
                    $table->string('video_url')->nullable()->after('metadata');
                }
            });
        }

        // Add additional columns to users table for enhanced customer management
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (! Schema::hasColumn('users', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable()->after('phone');
                }
                if (! Schema::hasColumn('users', 'gender')) {
                    $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
                }
                if (! Schema::hasColumn('users', 'preferred_locale')) {
                    $table->string('preferred_locale', 5)->default('lt')->after('gender');
                }
                if (! Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('preferred_locale');
                }
                if (! Schema::hasColumn('users', 'accepts_marketing')) {
                    $table->boolean('accepts_marketing')->default(false)->after('is_active');
                }
                if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                    $table->boolean('two_factor_enabled')->default(false)->after('accepts_marketing');
                }
                if (! Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled');
                }
                if (! Schema::hasColumn('users', 'preferences')) {
                    $table->json('preferences')->nullable()->after('last_login_at');
                }
            });
        }

        // Add additional columns to orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'tracking_number')) {
                    $table->string('tracking_number')->nullable()->after('notes');
                }
                if (! Schema::hasColumn('orders', 'metadata')) {
                    $table->json('metadata')->nullable()->after('tracking_number');
                }
                if (! Schema::hasColumn('orders', 'locale')) {
                    $table->string('locale', 5)->default('lt')->after('metadata');
                }
                if (! Schema::hasColumn('orders', 'weight')) {
                    $table->decimal('weight', 8, 2)->nullable()->after('locale');
                }
                if (! Schema::hasColumn('orders', 'fulfillment_status')) {
                    $table->string('fulfillment_status')->default('unfulfilled')->after('weight');
                }
            });
        }

        // Create product_variants table for enhanced product management
        if (! Schema::hasTable('product_variants')) {
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
        }

        // Create attributes table for product attributes
        if (! Schema::hasTable('attributes')) {
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
        }

        // Create attribute_values table
        if (! Schema::hasTable('attribute_values')) {
            Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value');
            $table->string('slug');
            $table->string('color_code')->nullable(); // For color attributes
            $table->integer('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->unique(['attribute_id', 'slug']);
            $table->index(['attribute_id', 'is_enabled']);
            });
        }

        // Create product_attributes pivot table
        if (! Schema::hasTable('product_attributes')) {
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
        }

        // Create or enhance customer_groups table for customer segmentation
        if (Schema::hasTable('customer_groups')) {
            Schema::table('customer_groups', function (Blueprint $table) {
                if (! Schema::hasColumn('customer_groups', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('name');
                }

                if (! Schema::hasColumn('customer_groups', 'code')) {
                    $table->string('code')->nullable()->unique()->after('slug');
                }

                if (! Schema::hasColumn('customer_groups', 'description')) {
                    $table->text('description')->nullable()->after('code');
                }

                if (! Schema::hasColumn('customer_groups', 'discount_percentage')) {
                    $table->decimal('discount_percentage', 5, 2)->default(0)->after('description');
                }

                if (! Schema::hasColumn('customer_groups', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('discount_percentage');
                }

                if (! Schema::hasColumn('customer_groups', 'conditions')) {
                    $table->json('conditions')->nullable()->after('is_enabled');
                }

                if (! Schema::hasColumn('customer_groups', 'created_at')) {
                    $table->timestamps();
                }
            });
        } else {
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
        }

        // Create or normalize customer_group_user pivot table
        if (Schema::hasTable('customer_group_user')) {
            if (! Schema::hasColumn('customer_group_user', 'customer_group_id')) {
                $columns = Schema::getColumnListing('customer_group_user');
                $groupColumn = in_array('group_id', $columns, true) ? 'group_id' : 'customer_group_id';
                $assignedColumn = in_array('assigned_at', $columns, true) ? 'assigned_at' : 'CURRENT_TIMESTAMP';
                $createdColumn = in_array('created_at', $columns, true) ? 'created_at' : 'CURRENT_TIMESTAMP';
                $updatedColumn = in_array('updated_at', $columns, true) ? 'updated_at' : 'CURRENT_TIMESTAMP';

                DB::statement('CREATE TABLE tmp_customer_group_user (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    customer_group_id BIGINT UNSIGNED NOT NULL,
                    user_id BIGINT UNSIGNED NOT NULL,
                    assigned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY customer_group_user_unique (customer_group_id, user_id),
                    KEY customer_group_user_user_id_index (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

                DB::statement("INSERT INTO tmp_customer_group_user (customer_group_id, user_id, assigned_at, created_at, updated_at)
                        SELECT {$groupColumn}, user_id, {$assignedColumn}, {$createdColumn}, {$updatedColumn} FROM customer_group_user");

                DB::statement('DROP TABLE customer_group_user');
                DB::statement('ALTER TABLE tmp_customer_group_user RENAME TO customer_group_user');

                DB::statement('ALTER TABLE customer_group_user ADD CONSTRAINT customer_group_user_customer_group_id_foreign FOREIGN KEY (customer_group_id) REFERENCES customer_groups(id) ON DELETE CASCADE');
                DB::statement('ALTER TABLE customer_group_user ADD CONSTRAINT customer_group_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
            }
        } elseif (! Schema::hasTable('customer_group_user')) {
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
        }

        // Create addresses table for user addresses
        if (! Schema::hasTable('addresses')) {
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
            $table->string('country_code', 2);
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'type']);
            });
        }

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
        if (! Schema::hasTable('settings')) {
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
