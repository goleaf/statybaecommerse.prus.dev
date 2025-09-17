<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Extend discount_campaigns (forward-only)
        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table): void {
                $columns = [
                    'total_views' => fn() => $table->unsignedBigInteger('total_views')->default(0)->after('budget_limit'),
                    'total_clicks' => fn() => $table->unsignedBigInteger('total_clicks')->default(0)->after('total_views'),
                    'total_conversions' => fn() => $table->unsignedBigInteger('total_conversions')->default(0)->after('total_clicks'),
                    'total_revenue' => fn() => $table->decimal('total_revenue', 15, 2)->default(0)->after('total_conversions'),
                    'conversion_rate' => fn() => $table->decimal('conversion_rate', 5, 4)->default(0)->after('total_revenue'),
                    'target_audience' => fn() => $table->json('target_audience')->nullable()->after('conversion_rate'),
                    'target_categories' => fn() => $table->json('target_categories')->nullable()->after('target_audience'),
                    'target_products' => fn() => $table->json('target_products')->nullable()->after('target_categories'),
                    'target_customer_groups' => fn() => $table->json('target_customer_groups')->nullable()->after('target_products'),
                    'display_priority' => fn() => $table->integer('display_priority')->default(0)->after('target_customer_groups'),
                    'banner_image' => fn() => $table->string('banner_image')->nullable()->after('display_priority'),
                    'banner_alt_text' => fn() => $table->string('banner_alt_text')->nullable()->after('banner_image'),
                    'cta_text' => fn() => $table->string('cta_text')->nullable()->after('banner_alt_text'),
                    'cta_url' => fn() => $table->string('cta_url')->nullable()->after('cta_text'),
                    'auto_start' => fn() => $table->boolean('auto_start')->default(false)->after('cta_url'),
                    'auto_end' => fn() => $table->boolean('auto_end')->default(false)->after('auto_start'),
                    'auto_pause_on_budget' => fn() => $table->boolean('auto_pause_on_budget')->default(false)->after('auto_end'),
                    'meta_title' => fn() => $table->string('meta_title')->nullable()->after('auto_pause_on_budget'),
                    'meta_description' => fn() => $table->text('meta_description')->nullable()->after('meta_title'),
                    'social_media_ready' => fn() => $table->boolean('social_media_ready')->default(false)->after('meta_description'),
                ];
                foreach ($columns as $name => $adder) {
                    if (!Schema::hasColumn('discount_campaigns', $name)) {
                        $adder();
                    }
                }
                foreach ([
                    ['status', 'starts_at', 'ends_at'],
                    ['is_featured', 'display_priority'],
                    ['channel_id', 'zone_id'],
                ] as $idx) {
                    try {
                        $table->index($idx);
                    } catch (\Throwable $e) {
                    }
                }
            });
        }

        if (Schema::hasTable('discount_campaigns') && !Schema::hasTable('campaign_views')) {
            Schema::create('campaign_views', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->string('session_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->string('referer')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->timestamp('viewed_at');

                $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['campaign_id', 'viewed_at']);
                $table->index(['session_id']);
                $table->index(['customer_id']);
            });
        }

        if (Schema::hasTable('discount_campaigns') && !Schema::hasTable('campaign_clicks')) {
            Schema::create('campaign_clicks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->string('session_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->string('click_type')->default('cta');
                $table->string('clicked_url')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->timestamp('clicked_at');

                $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['campaign_id', 'clicked_at']);
                $table->index(['session_id']);
                $table->index(['customer_id']);
                $table->index(['click_type']);
            });
        }

        if (Schema::hasTable('discount_campaigns') && !Schema::hasTable('campaign_conversions')) {
            Schema::create('campaign_conversions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('click_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('conversion_type')->default('purchase');
                $table->decimal('conversion_value', 15, 2)->default(0);
                $table->string('session_id')->nullable();
                $table->json('conversion_data')->nullable();
                $table->timestamp('converted_at');

                $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
                if (Schema::hasTable('campaign_clicks')) {
                    $table->foreign('click_id')->references('id')->on('campaign_clicks')->nullOnDelete()->cascadeOnUpdate();
                }
                if (Schema::hasTable('orders')) {
                    $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete()->cascadeOnUpdate();
                }
                $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();

                $table->index(['campaign_id', 'converted_at']);
                $table->index(['click_id']);
                $table->index(['order_id']);
                $table->index(['customer_id']);
                $table->index(['conversion_type']);
            });
        }

        if (Schema::hasTable('discount_campaigns') && !Schema::hasTable('campaign_customer_segments')) {
            Schema::create('campaign_customer_segments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('customer_group_id')->nullable();
                $table->string('segment_type');
                $table->json('segment_criteria')->nullable();
                $table->timestamps();

                $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
                if (Schema::hasTable('customer_groups')) {
                    $table->foreign('customer_group_id')->references('id')->on('customer_groups')->nullOnDelete()->cascadeOnUpdate();
                }

                $table->index(['campaign_id', 'segment_type']);
                $table->unique(['campaign_id', 'customer_group_id']);
            });
        }

        if (Schema::hasTable('discount_campaigns') && !Schema::hasTable('campaign_product_targets')) {
            Schema::create('campaign_product_targets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('target_type');
                $table->timestamps();

                $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->nullOnDelete()->cascadeOnUpdate();
                }
                if (Schema::hasTable('categories')) {
                    $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete()->cascadeOnUpdate();
                }

                $table->index(['campaign_id', 'target_type']);
                $table->unique(['campaign_id', 'product_id']);
                $table->unique(['campaign_id', 'category_id']);
            });
        }

        if (Schema::hasTable('discount_campaigns') && !Schema::hasTable('campaign_schedules')) {
            Schema::create('campaign_schedules', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('campaign_id');
                $table->string('schedule_type');
                $table->json('schedule_config')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
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
            Schema::table('discount_campaigns', function (Blueprint $table): void {
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
                $columns = [
                    'total_views',
                    'total_clicks',
                    'total_conversions',
                    'total_revenue',
                    'conversion_rate',
                    'target_audience',
                    'target_categories',
                    'target_products',
                    'target_customer_groups',
                    'display_priority',
                    'banner_image',
                    'banner_alt_text',
                    'cta_text',
                    'cta_url',
                    'auto_start',
                    'auto_end',
                    'auto_pause_on_budget',
                    'meta_title',
                    'meta_description',
                    'social_media_ready',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('discount_campaigns', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
