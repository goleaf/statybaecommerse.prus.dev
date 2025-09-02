<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create addresses table
        if (!Schema::hasTable('sh_addresses')) {
            Schema::create('sh_addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('type', ['billing', 'shipping', 'both'])->default('both');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('company')->nullable();
                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('city');
                $table->string('state');
                $table->string('postal_code');
                $table->string('country_code', 3);
                $table->string('phone')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('country_code')->references('code')->on('sh_countries')->onDelete('restrict');
                $table->index(['user_id', 'type']);
                $table->index(['user_id', 'is_default']);
            });
        }

        // Create collection rules table
        if (!Schema::hasTable('sh_collection_rules')) {
            Schema::create('sh_collection_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('collection_id');
                $table->string('field');  // 'brand_id', 'category_id', 'price', etc.
                $table->string('operator');  // 'equals', 'not_equals', 'greater_than', 'less_than', 'contains'
                $table->string('value');
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
                $table->index(['collection_id', 'position']);
            });
        }

        // Add missing columns to existing tables
        $this->addMissingColumns();

        // Add foreign key constraints
        $this->addForeignKeys();
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_collection_rules');
        Schema::dropIfExists('sh_addresses');
    }

    private function addMissingColumns(): void
    {
        // Add missing columns to zones table
        if (Schema::hasTable('sh_zones')) {
            Schema::table('sh_zones', function (Blueprint $table) {
                if (!Schema::hasColumn('sh_zones', 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 4)->default(0)->after('currency_id');
                }
                if (!Schema::hasColumn('sh_zones', 'shipping_rate')) {
                    $table->decimal('shipping_rate', 8, 2)->default(0)->after('tax_rate');
                }
                if (!Schema::hasColumn('sh_zones', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_enabled');
                }
            });
        }

        // Add missing columns to currencies table
        if (Schema::hasTable('sh_currencies')) {
            Schema::table('sh_currencies', function (Blueprint $table) {
                if (!Schema::hasColumn('sh_currencies', 'symbol')) {
                    $table->string('symbol', 10)->nullable()->after('code');
                }
                if (!Schema::hasColumn('sh_currencies', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 10, 6)->default(1)->after('symbol');
                }
                if (!Schema::hasColumn('sh_currencies', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('exchange_rate');
                }
                if (!Schema::hasColumn('sh_currencies', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('is_default');
                }
                if (!Schema::hasColumn('sh_currencies', 'decimal_places')) {
                    $table->tinyInteger('decimal_places')->default(2)->after('is_enabled');
                }
            });
        }

        // Add missing columns to channels table
        if (Schema::hasTable('sh_channels')) {
            Schema::table('sh_channels', function (Blueprint $table) {
                if (!Schema::hasColumn('sh_channels', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('sh_channels', 'slug')) {
                    $table->string('slug')->nullable()->after('name');
                }
                if (!Schema::hasColumn('sh_channels', 'url')) {
                    $table->string('url')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('sh_channels', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('url');
                }
                if (!Schema::hasColumn('sh_channels', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_enabled');
                }
                if (!Schema::hasColumn('sh_channels', 'metadata')) {
                    $table->json('metadata')->nullable()->after('is_default');
                }
            });
        }

        // Add missing columns to collections table
        if (Schema::hasTable('collections')) {
            Schema::table('collections', function (Blueprint $table) {
                if (!Schema::hasColumn('collections', 'is_automatic')) {
                    $table->boolean('is_automatic')->default(false)->after('is_visible');
                }
                if (!Schema::hasColumn('collections', 'rules')) {
                    $table->json('rules')->nullable()->after('is_automatic');
                }
                if (!Schema::hasColumn('collections', 'max_products')) {
                    $table->integer('max_products')->nullable()->after('rules');
                }
            });
        }

        // Add missing columns to customer groups table
        if (Schema::hasTable('sh_customer_groups')) {
            Schema::table('sh_customer_groups', function (Blueprint $table) {
                if (!Schema::hasColumn('sh_customer_groups', 'description')) {
                    $table->text('description')->nullable()->after('code');
                }
                if (!Schema::hasColumn('sh_customer_groups', 'discount_rate')) {
                    $table->decimal('discount_rate', 5, 4)->default(0)->after('description');
                }
                if (!Schema::hasColumn('sh_customer_groups', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('discount_rate');
                }
            });
        }

        // Add soft deletes to tables that need it
        $this->addSoftDeletes();
    }

    private function addSoftDeletes(): void
    {
        $tables = [
            'sh_currencies' => 'currencies',
            'sh_channels' => 'channels',
            'sh_countries' => 'countries',
        ];

        foreach ($tables as $tableName => $displayName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    private function addForeignKeys(): void
    {
        // Add foreign keys that might be missing
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'channel_id') && Schema::hasTable('sh_channels')) {
                    try {
                        $table->foreign('channel_id')->references('id')->on('sh_channels')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key might already exist
                    }
                }
                if (Schema::hasColumn('orders', 'zone_id') && Schema::hasTable('sh_zones')) {
                    try {
                        $table->foreign('zone_id')->references('id')->on('sh_zones')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key might already exist
                    }
                }
            });
        }

        // Add foreign keys to cart_items and order_items for variants
        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'variant_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                try {
                    $table->foreign('variant_id')->references('id')->on('sh_product_variants')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                try {
                    $table->foreign('variant_id')->references('id')->on('sh_product_variants')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            });
        }
    }
};
