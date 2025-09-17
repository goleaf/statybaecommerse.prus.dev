<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update variant_inventories table if it exists
        if (Schema::hasTable('variant_inventories')) {
            Schema::table('variant_inventories', function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (! Schema::hasColumn('variant_inventories', 'supplier_id')) {
                    $table->unsignedBigInteger('supplier_id')->nullable()->after('max_stock_level');
                    $table->foreign('supplier_id')->references('id')->on('partners')->onDelete('set null');
                }

                if (! Schema::hasColumn('variant_inventories', 'batch_number')) {
                    $table->string('batch_number')->nullable()->after('supplier_id');
                }

                if (! Schema::hasColumn('variant_inventories', 'expiry_date')) {
                    $table->date('expiry_date')->nullable()->after('batch_number');
                }

                if (! Schema::hasColumn('variant_inventories', 'status')) {
                    $table->enum('status', ['active', 'inactive', 'discontinued', 'quarantine'])->default('active')->after('expiry_date');
                }

                if (! Schema::hasColumn('variant_inventories', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }

                if (! Schema::hasColumn('variant_inventories', 'last_restocked_at')) {
                    $table->timestamp('last_restocked_at')->nullable()->after('notes');
                }

                if (! Schema::hasColumn('variant_inventories', 'last_sold_at')) {
                    $table->timestamp('last_sold_at')->nullable()->after('last_restocked_at');
                }

                if (! Schema::hasColumn('variant_inventories', 'cost_per_unit')) {
                    $table->decimal('cost_per_unit', 10, 2)->nullable()->after('last_sold_at');
                }

                if (! Schema::hasColumn('variant_inventories', 'reorder_point')) {
                    $table->integer('reorder_point')->default(0)->after('cost_per_unit');
                }

                if (! Schema::hasColumn('variant_inventories', 'max_stock_level')) {
                    $table->integer('max_stock_level')->nullable()->after('reorder_point');
                }
            });
        }

        // Create stock_movements table if it doesn't exist
        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_inventory_id');
                $table->integer('quantity');
                $table->enum('type', ['in', 'out']);
                $table->enum('reason', [
                    'sale', 'return', 'adjustment', 'manual_adjustment',
                    'restock', 'damage', 'theft', 'transfer',
                ]);
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamp('moved_at');
                $table->timestamps();

                $table->foreign('variant_inventory_id')->references('id')->on('variant_inventories')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

                $table->index(['variant_inventory_id', 'moved_at']);
                $table->index(['type', 'reason']);
                $table->index('moved_at');
            });
        }

        // Create inventories table if it doesn't exist (for simple products)
        if (! Schema::hasTable('inventories')) {
            Schema::create('inventories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('location_id');
                $table->integer('quantity')->default(0);
                $table->integer('reserved')->default(0);
                $table->integer('incoming')->default(0);
                $table->integer('threshold')->default(0);
                $table->boolean('is_tracked')->default(true);
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');

                $table->unique(['product_id', 'location_id']);
                $table->index(['location_id', 'is_tracked']);
            });
        }

        // Create locations table if it doesn't exist
        if (! Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->json('name'); // Translatable
                $table->json('slug'); // Translatable
                $table->json('description')->nullable(); // Translatable
                $table->string('code')->unique();
                $table->string('address_line_1')->nullable();
                $table->string('address_line_2')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country_code', 2)->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->enum('type', ['warehouse', 'store', 'office', 'other'])->default('warehouse');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('country_code')->references('cca2')->on('countries')->onDelete('set null');
                $table->index(['is_enabled', 'is_default']);
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        // Drop stock_movements table
        if (Schema::hasTable('stock_movements')) {
            Schema::dropIfExists('stock_movements');
        }

        // Drop inventories table
        if (Schema::hasTable('inventories')) {
            Schema::dropIfExists('inventories');
        }

        // Drop locations table
        if (Schema::hasTable('locations')) {
            Schema::dropIfExists('locations');
        }

        // Remove added columns from variant_inventories
        if (Schema::hasTable('variant_inventories')) {
            Schema::table('variant_inventories', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn([
                    'supplier_id', 'batch_number', 'expiry_date', 'status',
                    'notes', 'last_restocked_at', 'last_sold_at',
                    'cost_per_unit', 'reorder_point', 'max_stock_level',
                ]);
            });
        }
    }
};
