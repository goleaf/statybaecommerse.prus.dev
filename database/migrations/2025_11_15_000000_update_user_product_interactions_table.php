<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_product_interactions')) {
            return;
        }

        Schema::table('user_product_interactions', function (Blueprint $table): void {
            if (Schema::hasColumn('user_product_interactions', 'interaction_type') && ! Schema::hasColumn('user_product_interactions', 'event')) {
                $table->renameColumn('interaction_type', 'event');
            }
        });

        Schema::table('user_product_interactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_product_interactions', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }

            if (! Schema::hasColumn('user_product_interactions', 'meta')) {
                $table->json('meta')->nullable()->after('product_variant_id');
            }

            if (! Schema::hasColumn('user_product_interactions', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable()->after('meta');
            }
        });

        // Refresh indexes so they reference the renamed column.
        Schema::table('user_product_interactions', function (Blueprint $table): void {
            foreach ([
                'user_product_interaction_unique' => ['user_id', 'product_id', 'event'],
                'user_interactions_last_idx'      => ['user_id', 'event', 'last_interaction'],
                'user_interactions_product_idx'   => ['product_id', 'event', 'count'],
            ] as $indexName => $columns) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Throwable $exception) {
                    // The index might not exist (SQLite automatically rebuilds them), so ignore the failure.
                }

                $table->index($columns, $indexName);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_product_interactions')) {
            return;
        }

        Schema::table('user_product_interactions', function (Blueprint $table): void {
            if (Schema::hasColumn('user_product_interactions', 'occurred_at')) {
                $table->dropColumn('occurred_at');
            }

            if (Schema::hasColumn('user_product_interactions', 'meta')) {
                $table->dropColumn('meta');
            }

            if (Schema::hasColumn('user_product_interactions', 'product_variant_id')) {
                $table->dropForeign(['product_variant_id']);
                $table->dropColumn('product_variant_id');
            }
        });

        Schema::table('user_product_interactions', function (Blueprint $table): void {
            foreach ([
                'user_product_interaction_unique' => ['user_id', 'product_id', 'interaction_type'],
                'user_interactions_last_idx'      => ['user_id', 'interaction_type', 'last_interaction'],
                'user_interactions_product_idx'   => ['product_id', 'interaction_type', 'count'],
            ] as $indexName => $columns) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Throwable $exception) {
                    // Ignore missing indexes when rolling back.
                }

                $table->index($columns, $indexName);
            }
        });

        Schema::table('user_product_interactions', function (Blueprint $table): void {
            if (Schema::hasColumn('user_product_interactions', 'event') && ! Schema::hasColumn('user_product_interactions', 'interaction_type')) {
                $table->renameColumn('event', 'interaction_type');
            }
        });
    }
};
