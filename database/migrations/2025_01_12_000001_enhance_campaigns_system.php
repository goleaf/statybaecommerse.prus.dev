<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add campaign analytics and tracking
        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                // Campaign performance metrics
                if (!Schema::hasColumn('discount_campaigns', 'total_views')) {
                    $table->unsignedBigInteger('total_views')->default(0)->after('budget_limit');
                }
                if (!Schema::hasColumn('discount_campaigns', 'total_clicks')) {
                    $table->unsignedBigInteger('total_clicks')->default(0)->after('total_views');
                }
                if (!Schema::hasColumn('discount_campaigns', 'total_conversions')) {
                    $table->unsignedBigInteger('total_conversions')->default(0)->after('total_clicks');
                }
                if (!Schema::hasColumn('discount_campaigns', 'total_revenue')) {
                    $table->decimal('total_revenue', 12, 2)->default(0)->after('total_conversions');
                }
                if (!Schema::hasColumn('discount_campaigns', 'conversion_rate')) {
                    $table->decimal('conversion_rate', 5, 2)->default(0)->after('total_revenue');
                }

                // Campaign targeting
                if (!Schema::hasColumn('discount_campaigns', 'target_audience')) {
                    $table->json('target_audience')->nullable()->after('conversion_rate');
                }
                if (!Schema::hasColumn('discount_campaigns', 'target_categories')) {
                    $table->json('target_categories')->nullable()->after('target_audience');
                }
                if (!Schema::hasColumn('discount_campaigns', 'target_products')) {
                    $table->json('target_products')->nullable()->after('target_categories');
                }
                if (!Schema::hasColumn('discount_campaigns', 'target_customer_groups')) {
                    $table->json('target_customer_groups')->nullable()->after('target_products');
                }

                // Campaign display settings
                if (!Schema::hasColumn('discount_campaigns', 'display_priority')) {
                    $table->integer('display_priority')->default(0)->after('target_customer_groups');
                }
                if (!Schema::hasColumn('discount_campaigns', 'banner_image')) {
                    $table->string('banner_image')->nullable()->after('display_priority');
                }
                if (!Schema::hasColumn('discount_campaigns', 'banner_alt_text')) {
                    $table->string('banner_alt_text')->nullable()->after('banner_image');
                }
                if (!Schema::hasColumn('discount_campaigns', 'cta_text')) {
                    $table->string('cta_text')->nullable()->after('banner_alt_text');
                }
                if (!Schema::hasColumn('discount_campaigns', 'cta_url')) {
                    $table->string('cta_url')->nullable()->after('cta_text');
                }

                // Campaign automation
                if (!Schema::hasColumn('discount_campaigns', 'auto_start')) {
                    $table->boolean('auto_start')->default(false)->after('cta_url');
                }
                if (!Schema::hasColumn('discount_campaigns', 'auto_end')) {
                    $table->boolean('auto_end')->default(false)->after('auto_start');
                }
                if (!Schema::hasColumn('discount_campaigns', 'auto_pause_on_budget')) {
                    $table->boolean('auto_pause_on_budget')->default(false)->after('auto_end');
                }

                // SEO and marketing
                if (!Schema::hasColumn('discount_campaigns', 'meta_title')) {
                    $table->string('meta_title')->nullable()->after('auto_pause_on_budget');
                }
                if (!Schema::hasColumn('discount_campaigns', 'meta_description')) {
                    $table->text('meta_description')->nullable()->after('meta_title');
                }
                if (!Schema::hasColumn('discount_campaigns', 'social_media_ready')) {
                    $table->boolean('social_media_ready')->default(false)->after('meta_description');
                }

                // Add indexes for performance
                $table->index(['status', 'starts_at', 'ends_at'], 'campaigns_status_dates_idx');
                $table->index(['is_featured', 'display_priority'], 'campaigns_featured_priority_idx');
                $table->index(['channel_id', 'zone_id'], 'campaigns_channel_zone_idx');
            });
        }

        // Create campaign views tracking table
        if (!Schema::hasTable('campaign_views')) {
            Schema::create('campaign_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->string('session_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->string('referer')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('viewed_at');

                $table->index(['campaign_id', 'viewed_at']);
                $table->index(['session_id']);
                $table->index(['customer_id']);
            });
        }

        // Create campaign clicks tracking table
        if (!Schema::hasTable('campaign_clicks')) {
            Schema::create('campaign_clicks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->string('session_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->string('click_type')->default('cta');  // cta, banner, link
                $table->string('clicked_url')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('clicked_at');

                $table->index(['campaign_id', 'clicked_at']);
                $table->index(['session_id']);
                $table->index(['customer_id']);
                $table->index(['click_type']);
            });
        }

        // Create campaign conversions tracking table
        if (!Schema::hasTable('campaign_conversions')) {
            Schema::create('campaign_conversions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('click_id')->nullable()->constrained('campaign_clicks')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('conversion_type')->default('purchase');  // purchase, signup, download
                $table->decimal('conversion_value', 12, 2)->default(0);
                $table->string('session_id')->nullable();
                $table->json('conversion_data')->nullable();
                $table->timestamp('converted_at');

                $table->index(['campaign_id', 'converted_at']);
                $table->index(['click_id']);
                $table->index(['order_id']);
                $table->index(['customer_id']);
                $table->index(['conversion_type']);
            });
        }

        // Create campaign customer segments table
        if (!Schema::hasTable('campaign_customer_segments')) {
            Schema::create('campaign_customer_segments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
                $table->string('segment_type');  // group, location, behavior, custom
                $table->json('segment_criteria')->nullable();
                $table->timestamps();

                $table->index(['campaign_id', 'segment_type']);
                $table->unique(['campaign_id', 'customer_group_id']);
            });
        }

        // Create campaign product targets table
        if (!Schema::hasTable('campaign_product_targets')) {
            Schema::create('campaign_product_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('target_type');  // product, category, brand, collection
                $table->timestamps();

                $table->index(['campaign_id', 'target_type']);
                $table->unique(['campaign_id', 'product_id']);
                $table->unique(['campaign_id', 'category_id']);
            });
        }

        // Create campaign schedule table for recurring campaigns
        if (!Schema::hasTable('campaign_schedules')) {
            Schema::create('campaign_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
                $table->string('schedule_type');  // daily, weekly, monthly, custom
                $table->json('schedule_config')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['campaign_id', 'is_active']);
                $table->index(['next_run_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_schedules');
        Schema::dropIfExists('campaign_product_targets');
        Schema::dropIfExists('campaign_customer_segments');
        Schema::dropIfExists('campaign_conversions');
        Schema::dropIfExists('campaign_clicks');
        Schema::dropIfExists('campaign_views');

        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                $columns = [
                    'total_views', 'total_clicks', 'total_conversions', 'total_revenue', 'conversion_rate',
                    'target_audience', 'target_categories', 'target_products', 'target_customer_groups',
                    'display_priority', 'banner_image', 'banner_alt_text', 'cta_text', 'cta_url',
                    'auto_start', 'auto_end', 'auto_pause_on_budget', 'meta_title', 'meta_description',
                    'social_media_ready'
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('discount_campaigns', $column)) {
                        $table->dropColumn($column);
                    }
                }

                // Drop indexes
                try {
                    $table->dropIndex('campaigns_status_dates_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('campaigns_featured_priority_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('campaigns_channel_zone_idx');
                } catch (\Throwable $e) {
                }
            });
        }
    }
};
