<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                // Composite status + featured + priority
                $table->index(['status', 'is_featured', 'display_priority'], 'discount_campaigns_status_featured_priority_idx');
                // Dates window
                $table->index(['starts_at', 'ends_at'], 'discount_campaigns_dates_idx');
                // Channel + status
                $table->index(['channel_id', 'status'], 'discount_campaigns_channel_status_idx');
            });
        }

        if (Schema::hasTable('campaign_views')) {
            Schema::table('campaign_views', function (Blueprint $table) {
                $table->index(['ip_address'], 'campaign_views_ip_idx');
                $table->index(['campaign_id', 'customer_id'], 'campaign_views_campaign_customer_idx');
            });
        }

        if (Schema::hasTable('campaign_clicks')) {
            Schema::table('campaign_clicks', function (Blueprint $table) {
                $table->index(['ip_address'], 'campaign_clicks_ip_idx');
                $table->index(['campaign_id', 'customer_id'], 'campaign_clicks_campaign_customer_idx');
            });
        }

        if (Schema::hasTable('campaign_conversions')) {
            Schema::table('campaign_conversions', function (Blueprint $table) {
                $table->index(['session_id'], 'campaign_conversions_session_idx');
            });
        }

        if (Schema::hasTable('campaign_customer_segments')) {
            Schema::table('campaign_customer_segments', function (Blueprint $table) {
                $table->index(['is_active', 'segment_type'], 'campaign_customer_segments_active_segment_idx');
                $table->index(['sort_order'], 'campaign_customer_segments_sort_idx');
            });
        }

        if (Schema::hasTable('campaign_product_targets')) {
            Schema::table('campaign_product_targets', function (Blueprint $table) {
                $table->index(['product_id'], 'campaign_product_targets_product_idx');
                $table->index(['category_id'], 'campaign_product_targets_category_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                try {
                    $table->dropIndex('discount_campaigns_status_featured_priority_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('discount_campaigns_dates_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('discount_campaigns_channel_status_idx');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('campaign_views')) {
            Schema::table('campaign_views', function (Blueprint $table) {
                try {
                    $table->dropIndex('campaign_views_ip_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('campaign_views_campaign_customer_idx');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('campaign_clicks')) {
            Schema::table('campaign_clicks', function (Blueprint $table) {
                try {
                    $table->dropIndex('campaign_clicks_ip_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('campaign_clicks_campaign_customer_idx');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('campaign_conversions')) {
            Schema::table('campaign_conversions', function (Blueprint $table) {
                try {
                    $table->dropIndex('campaign_conversions_session_idx');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('campaign_customer_segments')) {
            Schema::table('campaign_customer_segments', function (Blueprint $table) {
                try {
                    $table->dropIndex('campaign_customer_segments_active_segment_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('campaign_customer_segments_sort_idx');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('campaign_product_targets')) {
            Schema::table('campaign_product_targets', function (Blueprint $table) {
                try {
                    $table->dropIndex('campaign_product_targets_product_idx');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('campaign_product_targets_category_idx');
                } catch (\Throwable $e) {
                }
            });
        }
    }
};
