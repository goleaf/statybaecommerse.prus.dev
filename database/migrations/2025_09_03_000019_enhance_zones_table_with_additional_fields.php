<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasCodeColumn = Schema::hasColumn('zones', 'code');
        $hasMetadataColumn = Schema::hasColumn('zones', 'metadata');
        $hasIsDefaultColumn = Schema::hasColumn('zones', 'is_default');
        $hasSortOrderColumn = Schema::hasColumn('zones', 'sort_order');
        $hasTypeIsActiveIndex = Schema::hasIndex('zones', 'zones_type_is_active_index');
        $hasCurrencyIsActiveIndex = Schema::hasIndex('zones', 'zones_currency_is_active_index');
        $hasPrioritySortOrderIndex = Schema::hasIndex('zones', 'zones_priority_sort_order_index');

        Schema::table('zones', function (Blueprint $table) use (
            $hasCodeColumn,
            $hasMetadataColumn,
            $hasIsDefaultColumn,
            $hasSortOrderColumn,
            $hasTypeIsActiveIndex,
            $hasCurrencyIsActiveIndex,
            $hasPrioritySortOrderIndex
        ) {
            if (! Schema::hasColumn('zones', 'type')) {
                $column = $table->string('type')->default('shipping');

                if ($hasCodeColumn) {
                    $column->after('code');
                }
            }

            if (! $hasSortOrderColumn && ! Schema::hasColumn('zones', 'sort_order')) {
                $column = $table->integer('sort_order')->default(0);

                if ($hasMetadataColumn) {
                    $column->after('metadata');
                } elseif ($hasCodeColumn) {
                    $column->after('code');
                }
            }

            if (! Schema::hasColumn('zones', 'priority')) {
                $column = $table->integer('priority')->default(0);

                if ($hasSortOrderColumn || Schema::hasColumn('zones', 'sort_order')) {
                    $column->after('sort_order');
                } elseif ($hasIsDefaultColumn) {
                    $column->after('is_default');
                } elseif ($hasCodeColumn) {
                    $column->after('code');
                }
            }

            if (! Schema::hasColumn('zones', 'min_order_amount')) {
                $column = $table->decimal('min_order_amount', 10, 2)->nullable();

                if (Schema::hasColumn('zones', 'shipping_rate')) {
                    $column->after('shipping_rate');
                }
            }

            if (! Schema::hasColumn('zones', 'max_order_amount')) {
                $column = $table->decimal('max_order_amount', 10, 2)->nullable();

                if (Schema::hasColumn('zones', 'min_order_amount')) {
                    $column->after('min_order_amount');
                }
            }

            if (! Schema::hasColumn('zones', 'free_shipping_threshold')) {
                $column = $table->decimal('free_shipping_threshold', 10, 2)->nullable();

                if (Schema::hasColumn('zones', 'max_order_amount')) {
                    $column->after('max_order_amount');
                }
            }

            if (! Schema::hasColumn('zones', 'is_active')) {
                $column = $table->boolean('is_active')->default(true);

                if ($hasIsDefaultColumn) {
                    $column->after('is_default');
                }
            }

            if (! $hasTypeIsActiveIndex) {
                $table->index(['type', 'is_active'], 'zones_type_is_active_index');
            }

            if (! $hasCurrencyIsActiveIndex) {
                $table->index(['currency_id', 'is_active'], 'zones_currency_is_active_index');
            }

            if (! $hasPrioritySortOrderIndex) {
                $table->index(['priority', 'sort_order'], 'zones_priority_sort_order_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            if (Schema::hasIndex('zones', 'zones_type_is_active_index')) {
                $table->dropIndex('zones_type_is_active_index');
            }

            if (Schema::hasIndex('zones', 'zones_currency_is_active_index')) {
                $table->dropIndex('zones_currency_is_active_index');
            }

            if (Schema::hasIndex('zones', 'zones_priority_sort_order_index')) {
                $table->dropIndex('zones_priority_sort_order_index');
            }

            $columnsToDrop = [];

            foreach ([
                'type',
                'priority',
                'min_order_amount',
                'max_order_amount',
                'free_shipping_threshold',
                'is_active',
            ] as $column) {
                if (Schema::hasColumn('zones', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
