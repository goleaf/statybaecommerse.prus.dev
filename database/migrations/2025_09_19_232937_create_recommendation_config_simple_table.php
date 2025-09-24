<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recommendation_config_simples', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('algorithm_type')->default('collaborative');
            $table->decimal('min_score', 8, 6)->default(0.1);
            $table->integer('max_results')->default(10);
            $table->decimal('decay_factor', 8, 6)->default(0.9);
            $table->boolean('exclude_out_of_stock')->default(true);
            $table->boolean('exclude_inactive')->default(true);
            $table->decimal('price_weight', 8, 6)->default(0.2);
            $table->decimal('rating_weight', 8, 6)->default(0.3);
            $table->decimal('popularity_weight', 8, 6)->default(0.2);
            $table->decimal('recency_weight', 8, 6)->default(0.1);
            $table->decimal('category_weight', 8, 6)->default(0.2);
            $table->decimal('custom_weight', 8, 6)->default(0.0);
            $table->integer('cache_duration')->default(60);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index(['algorithm_type', 'is_active']);
            $table->index(['is_default']);
        });

        // Pivot table for products
        Schema::create('recommendation_config_simple_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recommendation_config_simple_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table
                ->foreign('recommendation_config_simple_id', 'fk_rcsp_rcs')
                ->references('id')
                ->on('recommendation_config_simples')
                ->onDelete('cascade');
            $table
                ->foreign('product_id', 'fk_rcsp_product')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->unique(['recommendation_config_simple_id', 'product_id'], 'rcsp_unique');
        });

        // Pivot table for categories
        Schema::create('recommendation_config_simple_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recommendation_config_simple_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            $table
                ->foreign('recommendation_config_simple_id', 'fk_rcsc_rcs')
                ->references('id')
                ->on('recommendation_config_simples')
                ->onDelete('cascade');
            $table
                ->foreign('category_id', 'fk_rcsc_category')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');

            $table->unique(['recommendation_config_simple_id', 'category_id'], 'rcsc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_config_simple_categories');
        Schema::dropIfExists('recommendation_config_simple_products');
        Schema::dropIfExists('recommendation_config_simples');
    }
};
