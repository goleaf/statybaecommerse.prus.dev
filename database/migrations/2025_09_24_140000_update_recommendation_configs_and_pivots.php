<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('recommendation_configs')) {
            Schema::table('recommendation_configs', function (Blueprint $table) {
                if (! Schema::hasColumn('recommendation_configs', 'decay_factor')) {
                    $table->decimal('decay_factor', 8, 6)->nullable()->after('min_score');
                }
                if (! Schema::hasColumn('recommendation_configs', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_active');
                }
                if (! Schema::hasColumn('recommendation_configs', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('priority');
                }
            });
        }

        if (! Schema::hasTable('recommendation_config_products')) {
            Schema::create('recommendation_config_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recommendation_config_id')->constrained('recommendation_configs')->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                $table->unique(['recommendation_config_id', 'product_id'], 'rcp_unique');
            });
        }

        if (! Schema::hasTable('recommendation_config_categories')) {
            Schema::create('recommendation_config_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recommendation_config_id')->constrained('recommendation_configs')->onDelete('cascade');
                $table->foreignId('category_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                $table->unique(['recommendation_config_id', 'category_id'], 'rcc_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recommendation_config_categories')) {
            Schema::dropIfExists('recommendation_config_categories');
        }
        if (Schema::hasTable('recommendation_config_products')) {
            Schema::dropIfExists('recommendation_config_products');
        }
        if (Schema::hasTable('recommendation_configs')) {
            Schema::table('recommendation_configs', function (Blueprint $table) {
                if (Schema::hasColumn('recommendation_configs', 'decay_factor')) {
                    $table->dropColumn('decay_factor');
                }
                if (Schema::hasColumn('recommendation_configs', 'is_default')) {
                    $table->dropColumn('is_default');
                }
                if (Schema::hasColumn('recommendation_configs', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
            });
        }
    }
};


