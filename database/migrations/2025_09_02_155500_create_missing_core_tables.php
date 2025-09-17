<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create addresses table (forward-only)
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table): void {
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

                $table->index(['user_id', 'type']);
                $table->index(['user_id', 'is_default']);
            });
        }

        // Create collection rules table (forward-only)
        if (!Schema::hasTable('collection_rules')) {
            Schema::create('collection_rules', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('collection_id');
                $table->string('field');
                $table->string('operator');
                $table->string('value');
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->foreign('collection_id')->references('id')->on('collections')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['collection_id', 'position']);
            });
        }

        // Canonical alters
        $this->alterCoreTables();
        $this->addVariantForeignKeys();
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_rules');
        Schema::dropIfExists('addresses');
    }

    private function alterCoreTables(): void
    {
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table): void {
                if (!Schema::hasColumn('zones', 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 4)->default(0)->after('currency_id');
                }
                if (!Schema::hasColumn('zones', 'shipping_rate')) {
                    $table->decimal('shipping_rate', 8, 2)->default(0)->after('tax_rate');
                }
                if (!Schema::hasColumn('zones', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_enabled');
                }
            });
        }

        if (Schema::hasTable('currencies')) {
            Schema::table('currencies', function (Blueprint $table): void {
                if (!Schema::hasColumn('currencies', 'symbol')) {
                    $table->string('symbol', 10)->nullable()->after('code');
                }
                if (!Schema::hasColumn('currencies', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 10, 6)->default(1)->after('symbol');
                }
                if (!Schema::hasColumn('currencies', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('exchange_rate');
                }
                if (!Schema::hasColumn('currencies', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('is_default');
                }
                if (!Schema::hasColumn('currencies', 'decimal_places')) {
                    $table->tinyInteger('decimal_places')->default(2)->after('is_enabled');
                }
            });
        }

        if (Schema::hasTable('channels')) {
            Schema::table('channels', function (Blueprint $table): void {
                if (!Schema::hasColumn('channels', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('channels', 'slug')) {
                    $table->string('slug')->nullable()->after('name');
                }
                if (!Schema::hasColumn('channels', 'url')) {
                    $table->string('url')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('channels', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('url');
                }
                if (!Schema::hasColumn('channels', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_enabled');
                }
                if (!Schema::hasColumn('channels', 'metadata')) {
                    $table->json('metadata')->nullable()->after('is_default');
                }
                try {
                    $table->index(['slug']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['is_enabled']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('collections')) {
            Schema::table('collections', function (Blueprint $table): void {
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

        if (Schema::hasTable('customer_groups')) {
            Schema::table('customer_groups', function (Blueprint $table): void {
                if (!Schema::hasColumn('customer_groups', 'description')) {
                    $table->text('description')->nullable()->after('code');
                }
                if (!Schema::hasColumn('customer_groups', 'discount_rate')) {
                    $table->decimal('discount_rate', 5, 4)->default(0)->after('description');
                }
                if (!Schema::hasColumn('customer_groups', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('discount_rate');
                }
            });
        }

        // Soft deletes coverage
        foreach (['currencies', 'channels', 'countries'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->softDeletes();
                });
            }
        }
    }

    private function addVariantForeignKeys(): void
    {
        // Orders foreign keys: only if referenced tables exist
        if (Schema::hasTable('orders')) {
            if (Schema::hasTable('channels') && Schema::hasColumn('orders', 'channel_id')) {
                try {
                    Schema::table('orders', function (Blueprint $table): void {
                        $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete()->cascadeOnUpdate();
                    });
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasTable('zones') && Schema::hasColumn('orders', 'zone_id')) {
                try {
                    Schema::table('orders', function (Blueprint $table): void {
                        $table->foreign('zone_id')->references('id')->on('zones')->nullOnDelete()->cascadeOnUpdate();
                    });
                } catch (\Throwable $e) {
                }
            }
        }

        if (Schema::hasTable('cart_items') && Schema::hasTable('product_variants') && Schema::hasColumn('cart_items', 'variant_id')) {
            try {
                Schema::table('cart_items', function (Blueprint $table): void {
                    $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete()->cascadeOnUpdate();
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('order_items') && Schema::hasTable('product_variants') && Schema::hasColumn('order_items', 'variant_id')) {
            try {
                Schema::table('order_items', function (Blueprint $table): void {
                    $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete()->cascadeOnUpdate();
                });
            } catch (\Throwable $e) {
            }
        }
    }
};
