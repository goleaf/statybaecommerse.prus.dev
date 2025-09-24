<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('recommendation_configs')) {
            Schema::table('recommendation_configs', function (Blueprint $table) {
                if (!Schema::hasColumn('recommendation_configs', 'decay_factor')) {
                    $table->decimal('decay_factor', 8, 6)->nullable()->after('min_score');
                }
                if (!Schema::hasColumn('recommendation_configs', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('recommendation_configs', 'sort_order')) {
                    $table->integer('sort_order')->default(0)->after('priority');
                }
            });
        }

        if (!Schema::hasTable('recommendation_config_products')) {
            Schema::create('recommendation_config_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('recommendation_config_id');
                $table->unsignedBigInteger('product_id');
                $table->timestamps();
                $table->unique(['recommendation_config_id', 'product_id'], 'rcp_unique');

                // Short foreign key names to avoid MySQL 64-char limit
                $table
                    ->foreign('recommendation_config_id', 'rcp_rc_fk')
                    ->references('id')
                    ->on('recommendation_configs')
                    ->onDelete('cascade');
                $table
                    ->foreign('product_id', 'rcp_prod_fk')
                    ->references('id')
                    ->on('products')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('recommendation_config_categories')) {
            Schema::create('recommendation_config_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('recommendation_config_id');
                $table->unsignedBigInteger('category_id');
                $table->timestamps();
                $table->unique(['recommendation_config_id', 'category_id'], 'rcc_unique');

                // Short foreign key names to avoid MySQL 64-char limit
                $table
                    ->foreign('recommendation_config_id', 'rcc_rc_fk')
                    ->references('id')
                    ->on('recommendation_configs')
                    ->onDelete('cascade');
                $table
                    ->foreign('category_id', 'rcc_cat_fk')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('cascade');
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
