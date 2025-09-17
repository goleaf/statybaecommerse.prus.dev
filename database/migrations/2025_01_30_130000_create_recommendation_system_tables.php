<?php

declare(strict_types=1);

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
        // User behavior tracking table (forward-only)
        Schema::create('user_behaviors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('behavior_type'); // view, click, add_to_cart, purchase, wishlist, search
            $table->json('metadata')->nullable(); // Additional context data
            $table->string('referrer')->nullable();
            $table->string('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->index();
            
            $table->index(['user_id', 'behavior_type', 'created_at'], 'ub_user_type_created_idx');
            $table->index(['session_id', 'behavior_type', 'created_at'], 'ub_session_type_created_idx');
            $table->index(['product_id', 'behavior_type', 'created_at'], 'ub_product_type_created_idx');
        });

        // Product similarity matrix table
        Schema::create('product_similarities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('similar_product_id');
            $table->string('algorithm_type'); // content_based, collaborative, hybrid
            $table->decimal('similarity_score', 8, 6); // 0.000000 to 1.000000
            $table->json('calculation_data')->nullable(); // Algorithm-specific data
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            // Shorten index name to fit MySQL 64-char limit
            $table->unique(['product_id', 'similar_product_id', 'algorithm_type'], 'prod_sim_pid_spid_algo_unique');
            $table->index(['product_id', 'algorithm_type', 'similarity_score'], 'prod_sim_pid_algo_score_idx');
            $table->index(['similar_product_id', 'algorithm_type'], 'prod_sim_spid_algo_idx');
        });

        // User preferences and profiles
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('preference_type'); // category_preference, brand_preference, price_range, etc.
            $table->string('preference_key'); // category_id, brand_id, etc.
            $table->decimal('preference_score', 8, 6)->default(0); // Weight/preference strength
            $table->json('metadata')->nullable();
            $table->timestamp('last_updated');
            $table->timestamps();
            
            $table->unique(['user_id', 'preference_type', 'preference_key'], 'user_pref_user_type_key_unique');
            $table->index(['user_id', 'preference_type', 'preference_score'], 'user_pref_user_type_score_idx');
        });

        // Recommendation configurations
        Schema::create('recommendation_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type'); // algorithm type
            $table->json('config')->nullable(); // Algorithm-specific configuration
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher number = higher priority
            $table->json('filters')->nullable(); // Product filters to apply
            $table->integer('max_results')->default(10);
            $table->decimal('min_score', 8, 6)->default(0.1);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active', 'priority'], 'rec_configs_type_active_prio_idx');
        });

        // Recommendation blocks (related_products, you_might_also_like, etc.)
        Schema::create('recommendation_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // related_products, you_might_also_like, similar_products
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('config_ids'); // Array of recommendation_config IDs to use
            $table->boolean('is_active')->default(true);
            $table->integer('max_products')->default(4);
            $table->integer('cache_duration')->default(3600); // seconds
            $table->json('display_settings')->nullable(); // Frontend display options
            $table->timestamps();
            
            $table->index(['is_active', 'name'], 'rec_blocks_active_name_idx');
        });

        // Cached recommendations
        Schema::create('recommendation_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->unique();
            $table->foreignId('block_id')->nullable()->constrained('recommendation_blocks')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('context_type')->nullable(); // page_type, category, etc.
            $table->json('context_data')->nullable(); // Additional context
            $table->json('recommendations'); // Cached recommendation data
            $table->integer('hit_count')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['block_id', 'user_id', 'product_id'], 'rec_cache_block_user_product_idx');
            $table->index(['expires_at'], 'rec_cache_expires_idx');
            $table->index(['hit_count'], 'rec_cache_hits_idx');
        });

        // Recommendation performance analytics
        Schema::create('recommendation_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->nullable()->constrained('recommendation_blocks')->onDelete('cascade');
            $table->foreignId('config_id')->nullable()->constrained('recommendation_configs')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('action'); // view, click, add_to_cart, purchase
            $table->decimal('ctr', 5, 4)->nullable(); // Click-through rate
            $table->decimal('conversion_rate', 5, 4)->nullable(); // Conversion rate
            $table->json('metrics')->nullable(); // Additional performance metrics
            $table->date('date');
            $table->timestamps();
            
            $table->index(['block_id', 'date'], 'rec_an_block_date_idx');
            $table->index(['config_id', 'date'], 'rec_an_config_date_idx');
            $table->index(['action', 'date'], 'rec_an_action_date_idx');
        });

        // Product feature vectors for content-based recommendations
        Schema::create('product_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('feature_type'); // category, brand, price_range, attributes, etc.
            $table->string('feature_key');
            $table->decimal('feature_value', 10, 6); // Normalized feature value
            $table->decimal('weight', 5, 4)->default(1.0); // Feature importance weight
            $table->timestamps();
            
            $table->unique(['product_id', 'feature_type', 'feature_key'], 'prod_feat_pid_type_key_unique');
            $table->index(['feature_type', 'feature_key', 'feature_value'], 'prod_feat_type_key_value_idx');
        });

        // User-item interaction matrix for collaborative filtering
        Schema::create('user_product_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->string('interaction_type'); // view, cart, purchase, wishlist, review
            $table->decimal('rating', 3, 2)->nullable(); // 1.00 to 5.00
            $table->integer('count')->default(1); // Number of interactions
            $table->timestamp('first_interaction');
            $table->timestamp('last_interaction');
            $table->timestamps();
            
            $table->unique(['user_id', 'product_id', 'interaction_type'], 'upi_user_product_type_unique');
            $table->index(['user_id', 'interaction_type', 'last_interaction'], 'upi_user_type_last_idx');
            $table->index(['product_id', 'interaction_type', 'count'], 'upi_product_type_count_idx');
        });
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
