<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_product_targets')) {
            return;
        }

        Schema::table('campaign_product_targets', function (Blueprint $table): void {
            if (! Schema::hasColumn('campaign_product_targets', 'brand_id')) {
                $table->foreignId('brand_id')
                    ->nullable()
                    ->constrained('brands')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('campaign_product_targets', 'collection_id')) {
                $table->foreignId('collection_id')
                    ->nullable()
                    ->constrained('collections')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('campaign_product_targets', 'priority')) {
                $table->integer('priority')->default(0)->index();
            }

            if (! Schema::hasColumn('campaign_product_targets', 'weight')) {
                $table->integer('weight')->default(0);
            }

            if (! Schema::hasColumn('campaign_product_targets', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }

            if (! Schema::hasColumn('campaign_product_targets', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }

            if (! Schema::hasColumn('campaign_product_targets', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->index();
            }

            if (! Schema::hasColumn('campaign_product_targets', 'conditions')) {
                $table->text('conditions')->nullable();
            }

            if (! Schema::hasColumn('campaign_product_targets', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (Schema::hasColumn('campaign_product_targets', 'product_id')) {
                try {
                    $table->dropUnique('campaign_product_targets_campaign_id_product_id_unique');
                } catch (\Throwable) {
                    // Index may already be absent when running in different environments.
                }
            }

            if (Schema::hasColumn('campaign_product_targets', 'category_id')) {
                try {
                    $table->dropUnique('campaign_product_targets_campaign_id_category_id_unique');
                } catch (\Throwable) {
                    // Index may already be absent when running in different environments.
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_product_targets')) {
            return;
        }

        Schema::table('campaign_product_targets', function (Blueprint $table): void {
            if (Schema::hasColumn('campaign_product_targets', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('campaign_product_targets', 'conditions')) {
                $table->dropColumn('conditions');
            }

            if (Schema::hasColumn('campaign_product_targets', 'is_featured')) {
                $table->dropColumn('is_featured');
            }

            if (Schema::hasColumn('campaign_product_targets', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('campaign_product_targets', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('campaign_product_targets', 'weight')) {
                $table->dropColumn('weight');
            }

            if (Schema::hasColumn('campaign_product_targets', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::hasColumn('campaign_product_targets', 'collection_id')) {
                $table->dropForeign(['collection_id']);
                $table->dropColumn('collection_id');
            }

            if (Schema::hasColumn('campaign_product_targets', 'brand_id')) {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');
            }

            if (Schema::hasColumn('campaign_product_targets', 'product_id')) {
                $table->unique(['campaign_id', 'product_id']);
            }

            if (Schema::hasColumn('campaign_product_targets', 'category_id')) {
                $table->unique(['campaign_id', 'category_id']);
            }
        });
    }
};
