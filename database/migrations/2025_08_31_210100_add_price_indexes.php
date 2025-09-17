<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_price_lists')) {
            Schema::table('sh_price_lists', function (Blueprint $table) {
                try {
                    $table->index(['currency_id', 'zone_id', 'priority'], 'sh_price_lists_currency_zone_priority');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['is_enabled'], 'sh_price_lists_is_enabled');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_group_price_list')) {
            Schema::table('sh_group_price_list', function (Blueprint $table) {
                try {
                    $table->index(['group_id', 'price_list_id'], 'idx_gpl_group_price');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_partner_price_list')) {
            Schema::table('sh_partner_price_list', function (Blueprint $table) {
                try {
                    $table->index(['partner_id', 'price_list_id'], 'idx_ppl_partner_price');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_customer_group_user')) {
            Schema::table('sh_customer_group_user', function (Blueprint $table) {
                try {
                    $table->index(['group_id', 'user_id'], 'idx_cgu_group_user');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['user_id'], 'idx_cgu_user');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_partner_users')) {
            Schema::table('sh_partner_users', function (Blueprint $table) {
                try {
                    $table->index(['partner_id', 'user_id'], 'idx_pu_partner_user');
                } catch (\Throwable $e) {
                }
                try {
                    $table->index(['user_id'], 'idx_pu_user');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_price_list_items')) {
            Schema::table('sh_price_list_items', function (Blueprint $table) {
                try {
                    $table->index(['price_list_id', 'product_id'], 'idx_pli_price_product');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_currencies')) {
            Schema::table('sh_currencies', function (Blueprint $table) {
                try {
                    $table->index(['code'], 'idx_currencies_code');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive
    }
};
