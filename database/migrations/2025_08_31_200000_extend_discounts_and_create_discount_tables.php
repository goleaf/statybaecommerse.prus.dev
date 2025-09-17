<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('discounts')) {
            Schema::table('discounts', function (Blueprint $table): void {
                $columns = [
                    'priority' => fn() => $table->unsignedInteger('priority')->default(100)->after('stacking_policy'),
                    'exclusive' => fn() => $table->boolean('exclusive')->default(false)->after('priority'),
                    'applies_to_shipping' => fn() => $table->boolean('applies_to_shipping')->default(false)->after('exclusive'),
                    'free_shipping' => fn() => $table->boolean('free_shipping')->default(false)->after('applies_to_shipping'),
                    'first_order_only' => fn() => $table->boolean('first_order_only')->default(false)->after('free_shipping'),
                    'per_customer_limit' => fn() => $table->unsignedInteger('per_customer_limit')->nullable()->after('first_order_only'),
                    'per_code_limit' => fn() => $table->unsignedInteger('per_code_limit')->nullable()->after('per_customer_limit'),
                    'per_day_limit' => fn() => $table->unsignedInteger('per_day_limit')->nullable()->after('per_code_limit'),
                    'channel_restrictions' => fn() => $table->json('channel_restrictions')->nullable()->after('per_day_limit'),
                    'currency_restrictions' => fn() => $table->json('currency_restrictions')->nullable()->after('channel_restrictions'),
                    'weekday_mask' => fn() => $table->string('weekday_mask')->nullable()->after('currency_restrictions'),
                    'time_window' => fn() => $table->json('time_window')->nullable()->after('weekday_mask'),
                ];
                foreach ($columns as $name => $adder) {
                    if (!Schema::hasColumn('discounts', $name)) {
                        $adder();
                    }
                }
                try {
                    $table->index(['status', 'starts_at', 'ends_at'], 'discounts_status_window_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['priority'], 'discounts_priority_index');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('discounts') && !Schema::hasTable('discount_conditions')) {
            Schema::create('discount_conditions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('type');
                $table->string('operator');
                $table->json('value')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();

                $table->index(['discount_id', 'type']);
            });
        }

        if (Schema::hasTable('discounts') && !Schema::hasTable('discount_codes')) {
            Schema::create('discount_codes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('code')->unique();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('max_uses')->nullable();
                $table->unsignedInteger('usage_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['discount_id']);
            });
        }

        if (Schema::hasTable('discounts') && Schema::hasTable('orders') && !Schema::hasTable('discount_redemptions')) {
            Schema::create('discount_redemptions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('discount_id')->constrained('discounts')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('code_id')->nullable()->constrained('discount_codes')->nullOnDelete()->cascadeOnUpdate();
                $table->foreignId('order_id')->constrained('orders', 'id')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
                $table->decimal('amount_saved', 15, 2);
                $table->char('currency_code', 3);
                $table->timestamp('redeemed_at');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['order_id']);
                $table->index(['user_id']);
                $table->index(['discount_id', 'code_id']);
            });
        }

        if (!Schema::hasTable('sh_discount_campaigns')) {
            Schema::create('sh_discount_campaigns', function (Blueprint $table): void {
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
            if (Schema::hasTable('sh_channels')) {
                Schema::table('sh_discount_campaigns', function (Blueprint $table): void {
                    $table->foreign('channel_id')->references('id')->on('sh_channels')->nullOnDelete()->cascadeOnUpdate();
                });
            }
        }

        if (Schema::hasTable('sh_discount_campaigns') && Schema::hasTable('discounts') && !Schema::hasTable('sh_campaign_discount')) {
            Schema::create('sh_campaign_discount', function (Blueprint $table): void {
                $table->unsignedBigInteger('campaign_id');
                $table->unsignedBigInteger('discount_id');
                $table->primary(['campaign_id', 'discount_id']);

                $table->foreign('campaign_id')->references('id')->on('sh_discount_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('discount_id')->references('id')->on('discounts')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_campaign_discount');
        Schema::dropIfExists('sh_discount_campaigns');
        Schema::dropIfExists('discount_redemptions');
        Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('discount_conditions');

        if (Schema::hasTable('discounts')) {
            Schema::table('discounts', function (Blueprint $table): void {
                try {
                    $table->dropIndex('discounts_status_window_index');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('discounts_priority_index');
                } catch (\Throwable $e) {
                }
                $columns = [
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
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('discounts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
