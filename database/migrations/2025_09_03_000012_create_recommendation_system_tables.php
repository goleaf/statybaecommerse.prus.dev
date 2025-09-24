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
        // User behavior tracking table
        if (!Schema::hasTable('user_behaviors')) {
            Schema::create('user_behaviors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('session_id')->nullable()->index();
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('behavior_type');  // view, click, add_to_cart, purchase, wishlist, search
                $table->json('metadata')->nullable();  // Additional context data
                $table->string('referrer')->nullable();
                $table->string('user_agent')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['user_id', 'behavior_type', 'created_at']);
                $table->index(['session_id', 'behavior_type', 'created_at']);
                $table->index(['product_id', 'behavior_type', 'created_at']);
            });
        }

        // Product similarity matrix table
        if (!Schema::hasTable('product_similarities')) {
            Schema::create('product_similarities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->foreignId('similar_product_id')->constrained('products')->onDelete('cascade');
                $table->string('algorithm_type');  // content_based, collaborative, hybrid
                $table->decimal('similarity_score', 8, 6);  // 0.000000 to 1.000000
                $table->json('calculation_data')->nullable();  // Algorithm-specific data
                $table->timestamp('calculated_at');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['product_id', 'similar_product_id', 'algorithm_type'], 'product_similarity_unique');
                $table->index(['product_id', 'algorithm_type', 'similarity_score'], 'product_similarity_score_idx');
                $table->index(['similar_product_id', 'algorithm_type'], 'product_similarity_type_idx');
            });
        }

        // User preferences and profiles
        if (!Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('preference_type');  // category_preference, brand_preference, price_range, etc.
                $table->string('preference_key');  // category_id, brand_id, etc.
                $table->decimal('preference_score', 8, 6)->default(0);  // Weight/preference strength
                $table->json('metadata')->nullable();
                $table->timestamp('last_updated');
                $table->timestamps();

                $table->unique(['user_id', 'preference_type', 'preference_key']);
                $table->index(['user_id', 'preference_type', 'preference_score']);
            });
        }

        // Recommendation configurations
        if (!Schema::hasTable('recommendation_configs')) {
            Schema::create('recommendation_configs', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('type');  // algorithm type
                $table->json('config')->nullable();  // Algorithm-specific configuration
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);  // Higher number = higher priority
                $table->json('filters')->nullable();  // Product filters to apply
                $table->integer('max_results')->default(10);
                $table->decimal('min_score', 8, 6)->default(0.1);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['type', 'is_active', 'priority']);
            });
        }

        // Recommendation blocks (related_products, you_might_also_like, etc.)
        if (!Schema::hasTable('recommendation_blocks')) {
            Schema::create('recommendation_blocks', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();  // related_products, you_might_also_like, similar_products
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('config_ids');  // Array of recommendation_config IDs to use
                $table->boolean('is_active')->default(true);
                $table->integer('max_products')->default(4);
                $table->integer('cache_duration')->default(3600);  // seconds
                $table->json('display_settings')->nullable();  // Frontend display options
                $table->timestamps();

                $table->index(['is_active', 'name']);
            });
        }

        // Cached recommendations
        if (!Schema::hasTable('recommendation_cache')) {
            Schema::create('recommendation_cache', function (Blueprint $table) {
                $table->id();
                $table->string('cache_key')->unique();
                $table->foreignId('block_id')->nullable()->constrained('recommendation_blocks')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('context_type')->nullable();  // page_type, category, etc.
                $table->json('context_data')->nullable();  // Additional context
                $table->json('recommendations');  // Cached recommendation data
                $table->integer('hit_count')->default(0);
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['block_id', 'user_id', 'product_id']);
                $table->index(['expires_at']);
                $table->index(['hit_count']);
            });
        }

        // Recommendation performance analytics
        if (!Schema::hasTable('recommendation_analytics')) {
            Schema::create('recommendation_analytics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('block_id')->nullable()->constrained('recommendation_blocks')->onDelete('cascade');
                $table->foreignId('config_id')->nullable()->constrained('recommendation_configs')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('action');  // view, click, add_to_cart, purchase
                $table->decimal('ctr', 5, 4)->nullable();  // Click-through rate
                $table->decimal('conversion_rate', 5, 4)->nullable();  // Conversion rate
                $table->json('metrics')->nullable();  // Additional performance metrics
                $table->date('date');
                $table->timestamps();

                $table->index(['block_id', 'date']);
                $table->index(['config_id', 'date']);
                $table->index(['action', 'date']);
            });
        }

        // Product feature vectors for content-based recommendations
        if (!Schema::hasTable('product_features')) {
            Schema::create('product_features', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->string('feature_type');  // category, brand, price_range, attributes, etc.
                $table->string('feature_key');
                $table->text('feature_value');
                $table->decimal('weight', 5, 4)->default(1.0);  // Feature importance weight
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['product_id', 'feature_type', 'feature_key']);
                $table->index(['feature_type', 'feature_key']);
            });
        }

        // User-item interaction matrix for collaborative filtering
        if (!Schema::hasTable('user_product_interactions')) {
            Schema::create('user_product_interactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->string('interaction_type');  // view, cart, purchase, wishlist, review
                $table->decimal('rating', 3, 2)->nullable();  // 1.00 to 5.00
                $table->integer('count')->default(1);  // Number of interactions
                $table->timestamp('first_interaction');
                $table->timestamp('last_interaction');
                $table->timestamps();

                $table->unique(['user_id', 'product_id', 'interaction_type'], 'user_product_interaction_unique');
                $table->index(['user_id', 'interaction_type', 'last_interaction'], 'user_interactions_last_idx');
                $table->index(['product_id', 'interaction_type', 'count'], 'user_interactions_product_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_product_interactions');
        Schema::dropIfExists('product_features');
        Schema::dropIfExists('recommendation_analytics');
        Schema::dropIfExists('recommendation_cache');
        Schema::dropIfExists('recommendation_blocks');
        Schema::dropIfExists('recommendation_configs');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('product_similarities');
        Schema::dropIfExists('user_behaviors');
    }
};
