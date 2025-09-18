<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sh_discounts')) {
            Schema::table('sh_discounts', function (Blueprint $table) {
                if (! Schema::hasColumn('sh_discounts', 'priority')) {
                    $table->unsignedInteger('priority')->default(100)->after('stacking_policy');
                }
                if (! Schema::hasColumn('sh_discounts', 'exclusive')) {
                    $table->boolean('exclusive')->default(false)->after('priority');
                }
                if (! Schema::hasColumn('sh_discounts', 'applies_to_shipping')) {
                    $table->boolean('applies_to_shipping')->default(false)->after('exclusive');
                }
                if (! Schema::hasColumn('sh_discounts', 'free_shipping')) {
                    $table->boolean('free_shipping')->default(false)->after('applies_to_shipping');
                }
                if (! Schema::hasColumn('sh_discounts', 'first_order_only')) {
                    $table->boolean('first_order_only')->default(false)->after('free_shipping');
                }
                if (! Schema::hasColumn('sh_discounts', 'per_customer_limit')) {
                    $table->unsignedInteger('per_customer_limit')->nullable()->after('first_order_only');
                }
                if (! Schema::hasColumn('sh_discounts', 'per_code_limit')) {
                    $table->unsignedInteger('per_code_limit')->nullable()->after('per_customer_limit');
                }
                if (! Schema::hasColumn('sh_discounts', 'per_day_limit')) {
                    $table->unsignedInteger('per_day_limit')->nullable()->after('per_code_limit');
                }
                if (! Schema::hasColumn('sh_discounts', 'channel_restrictions')) {
                    $table->json('channel_restrictions')->nullable()->after('per_day_limit');
                }
                if (! Schema::hasColumn('sh_discounts', 'currency_restrictions')) {
                    $table->json('currency_restrictions')->nullable()->after('channel_restrictions');
                }
                if (! Schema::hasColumn('sh_discounts', 'weekday_mask')) {
                    $table->string('weekday_mask')->nullable()->after('currency_restrictions');
                }
                if (! Schema::hasColumn('sh_discounts', 'time_window')) {
                    $table->json('time_window')->nullable()->after('weekday_mask');
                }

                // Indexes for performance (if not already present)
                $table->index(['status', 'starts_at', 'ends_at'], 'sh_discounts_status_window_index');
                $table->index(['priority'], 'sh_discounts_priority_index');
            });
        }

        if (Schema::hasTable('sh_discounts') && ! Schema::hasTable('sh_discount_conditions')) {
            Schema::create('sh_discount_conditions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('discount_id')->constrained('sh_discounts')->cascadeOnDelete();
                $table->string('type'); // product|category|brand|collection|attribute_value|cart_total|item_qty|zone|channel|currency|customer_group|user|partner_tier|first_order|day_time|custom_script
                $table->string('operator'); // equals_to|not_equals_to|less_than|greater_than|starts_with|ends_with|contains|not_contains
                $table->json('value')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();

                $table->index(['discount_id', 'type']);
            });
        }

        if (Schema::hasTable('sh_discounts') && ! Schema::hasTable('sh_discount_codes')) {
            Schema::create('sh_discount_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('discount_id')->constrained('sh_discounts')->cascadeOnDelete();
                $table->string('code')->unique();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('max_uses')->nullable();
                $table->unsignedInteger('usage_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['discount_id']);
            });
        }

        if (Schema::hasTable('sh_discounts') && ! Schema::hasTable('sh_discount_redemptions')) {
            Schema::create('sh_discount_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('discount_id')->constrained('sh_discounts');
                $table->foreignId('code_id')->nullable()->constrained('sh_discount_codes');
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->decimal('amount_saved', 12, 2);
                $table->char('currency_code', 3);
                $table->timestamp('redeemed_at');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['order_id']);
                $table->index(['user_id']);
                $table->index(['discount_id', 'code_id']);
            });
        }

        if (Schema::hasTable('sh_discounts') && ! Schema::hasTable('sh_discount_campaigns')) {
            Schema::create('sh_discount_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->unsignedBigInteger('channel_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->string('status')->default('active');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('discount_campaigns')) {
            Schema::create('discount_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->unsignedBigInteger('channel_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->string('status')->default('active');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['status']);
                $table->index(['channel_id']);
                $table->index(['zone_id']);
            });
        }

        if (Schema::hasTable('sh_discounts') && ! Schema::hasTable('sh_campaign_discount')) {
            Schema::create('sh_campaign_discount', function (Blueprint $table) {
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('discount_id');
                $table->primary(['campaign_id', 'discount_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_campaign_discount');
        Schema::dropIfExists('sh_discount_campaigns');
        Schema::dropIfExists('sh_discount_redemptions');
        Schema::dropIfExists('sh_discount_codes');
        Schema::dropIfExists('sh_discount_conditions');
        Schema::dropIfExists('discount_campaigns');

        if (Schema::hasTable('sh_discounts')) {
            Schema::table('sh_discounts', function (Blueprint $table) {
                foreach ([
                    'priority',
                    'exclusive',
                    'applies_to_shipping',
                    'free_shipping',
                    'first_order_only',
                    'per_customer_limit',
                    'per_code_limit',
                    'per_day_limit',
                    'channel_restrictions',
                    'currency_restrictions',
                    'weekday_mask',
                    'time_window',
                ] as $column) {
                    if (Schema::hasColumn('sh_discounts', $column)) {
                        $table->dropColumn($column);
                    }
                }

                // drop indexes if exist
                try {
                    $table->dropIndex('sh_discounts_status_window_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('sh_discounts_priority_index');
                } catch (\Throwable $e) {
                }
            });
        }
    }
};
