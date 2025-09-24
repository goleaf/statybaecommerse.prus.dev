<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('variant_inventories', function (Blueprint $table) {
            // Ensure location support exists for per-location stock management
            if (!Schema::hasColumn('variant_inventories', 'location_id')) {
                $table->unsignedBigInteger('location_id')->default(1)->after('variant_id');
                try {
                    $table->index(['location_id']);
                } catch (\Throwable $e) {
                    // ignore index creation race
                }
            }
            if (!Schema::hasColumn('variant_inventories', 'threshold')) {
                $afterColumn = null;
                if (Schema::hasColumn('variant_inventories', 'incoming')) {
                    $afterColumn = 'incoming';
                } elseif (Schema::hasColumn('variant_inventories', 'reserved')) {
                    $afterColumn = 'reserved';
                } elseif (Schema::hasColumn('variant_inventories', 'stock')) {
                    $afterColumn = 'stock';
                }

                $thresholdColumn = $table->integer('threshold')->default(0);
                if ($afterColumn !== null) {
                    $thresholdColumn->after($afterColumn);
                }
            }
            // Add new columns for enhanced inventory management
            if (!Schema::hasColumn('variant_inventories', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'last_restocked_at')) {
                $table->timestamp('last_restocked_at')->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'last_sold_at')) {
                $table->timestamp('last_sold_at')->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'cost_per_unit')) {
                $table->decimal('cost_per_unit', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'reorder_point')) {
                $table->integer('reorder_point')->default(0);
            }
            if (!Schema::hasColumn('variant_inventories', 'max_stock_level')) {
                $table->integer('max_stock_level')->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'batch_number')) {
                $table->string('batch_number')->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'expiry_date')) {
                $table->date('expiry_date')->nullable();
            }
            if (!Schema::hasColumn('variant_inventories', 'status')) {
                $table->string('status')->default('active');
            }

            // Add soft deletes
            if (!Schema::hasColumn('variant_inventories', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add indexes for better performance (only if they don't exist)
        if (Schema::hasColumn('variant_inventories', 'status') && Schema::hasColumn('variant_inventories', 'is_tracked')) {
            try {
                Schema::table('variant_inventories', function (Blueprint $table) {
                    $table->index(['status', 'is_tracked']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        if (Schema::hasColumn('variant_inventories', 'supplier_id')) {
            try {
                Schema::table('variant_inventories', function (Blueprint $table) {
                    $table->index(['supplier_id']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        if (Schema::hasColumn('variant_inventories', 'expiry_date')) {
            try {
                Schema::table('variant_inventories', function (Blueprint $table) {
                    $table->index(['expiry_date']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        if (Schema::hasColumn('variant_inventories', 'last_restocked_at')) {
            try {
                Schema::table('variant_inventories', function (Blueprint $table) {
                    $table->index(['last_restocked_at']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        if (Schema::hasColumn('variant_inventories', 'reorder_point')) {
            try {
                Schema::table('variant_inventories', function (Blueprint $table) {
                    $table->index(['reorder_point']);
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        // Add foreign key constraint for supplier
        if (Schema::hasColumn('variant_inventories', 'supplier_id') && Schema::hasTable('partners')) {
            try {
                Schema::table('variant_inventories', function (Blueprint $table) {
                    $table->foreign('supplier_id')->references('id')->on('partners')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
        }

        // Create stock_movements table
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('variant_inventory_id');
                $table->integer('quantity');
                $table->enum('type', ['in', 'out']);
                $table->string('reason');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamp('moved_at')->useCurrent();
                $table->timestamps();

                $table->foreign('variant_inventory_id')->references('id')->on('variant_inventories')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

                $table->index(['variant_inventory_id', 'moved_at']);
                $table->index(['type', 'reason']);
                $table->index(['moved_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');

        Schema::table('variant_inventories', function (Blueprint $table) {
            if (Schema::hasColumn('variant_inventories', 'supplier_id')) {
                try {
                    $table->dropForeign(['supplier_id']);
                } catch (\Throwable $e) {
                    // Ignore missing foreign key
                }
            }

            foreach ([
                ['status', 'is_tracked'],
                ['supplier_id'],
                ['expiry_date'],
                ['last_restocked_at'],
                ['reorder_point'],
            ] as $indexColumns) {
                try {
                    $table->dropIndex($indexColumns);
                } catch (\Throwable $e) {
                    // Ignore missing indexes
                }
            }

            $columnsToDrop = array_filter([
                Schema::hasColumn('variant_inventories', 'notes') ? 'notes' : null,
                Schema::hasColumn('variant_inventories', 'last_restocked_at') ? 'last_restocked_at' : null,
                Schema::hasColumn('variant_inventories', 'last_sold_at') ? 'last_sold_at' : null,
                Schema::hasColumn('variant_inventories', 'cost_per_unit') ? 'cost_per_unit' : null,
                Schema::hasColumn('variant_inventories', 'reorder_point') ? 'reorder_point' : null,
                Schema::hasColumn('variant_inventories', 'max_stock_level') ? 'max_stock_level' : null,
                Schema::hasColumn('variant_inventories', 'supplier_id') ? 'supplier_id' : null,
                Schema::hasColumn('variant_inventories', 'batch_number') ? 'batch_number' : null,
                Schema::hasColumn('variant_inventories', 'expiry_date') ? 'expiry_date' : null,
                Schema::hasColumn('variant_inventories', 'status') ? 'status' : null,
                Schema::hasColumn('variant_inventories', 'deleted_at') ? 'deleted_at' : null,
            ]);

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
