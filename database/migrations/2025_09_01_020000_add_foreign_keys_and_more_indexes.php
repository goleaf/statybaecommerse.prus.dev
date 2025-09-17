<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_orders')) {
            Schema::table('sh_orders', function (Blueprint $table) {
                try {
                    $table->index(['number', 'created_at'], 'idx_orders_number_created');
                } catch (\Throwable $e) {
                }
            });
        } elseif (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                try {
                    $table->index(['number', 'created_at'], 'idx_orders_number_created');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_order_items')) {
            Schema::table('sh_order_items', function (Blueprint $table) {
                try {
                    $table->index(['order_id', 'product_id'], 'idx_order_items_order_product');
                } catch (\Throwable $e) {
                }
                if (Schema::hasTable('sh_orders')) {
                    try {
                        $table->foreign('order_id')->references('id')->on('sh_orders')->cascadeOnUpdate()->cascadeOnDelete();
                    } catch (\Throwable $e) {
                    }
                } elseif (Schema::hasTable('orders')) {
                    try {
                        $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
                    } catch (\Throwable $e) {
                    }
                }
            });
        }

        if (Schema::hasTable('sh_prices')) {
            Schema::table('sh_prices', function (Blueprint $table) {
                try {
                    $table->index(['currency_id', 'amount'], 'idx_prices_currency_amount');
                } catch (\Throwable $e) {
                }
                if (Schema::hasTable('sh_currencies')) {
                    try {
                        $table->foreign('currency_id')->references('id')->on('sh_currencies')->cascadeOnUpdate();
                    } catch (\Throwable $e) {
                    }
                }
            });
        }

        if (Schema::hasTable('sh_inventories')) {
            Schema::table('sh_inventories', function (Blueprint $table) {
                try {
                    $table->index(['country_id', 'is_default'], 'idx_inventories_country_default');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_zones')) {
            Schema::table('sh_zones', function (Blueprint $table) {
                try {
                    $table->index(['code'], 'idx_zones_code');
                } catch (\Throwable $e) {
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sh_orders')) {
            Schema::table('sh_orders', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_orders_number_created');
                } catch (\Throwable $e) {
                }
            });
        } elseif (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_orders_number_created');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_order_items')) {
            Schema::table('sh_order_items', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_order_items_order_product');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropForeign(['order_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_prices')) {
            Schema::table('sh_prices', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_prices_currency_amount');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropForeign(['currency_id']);
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_inventories')) {
            Schema::table('sh_inventories', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_inventories_country_default');
                } catch (\Throwable $e) {
                }
            });
        }

        if (Schema::hasTable('sh_zones')) {
            Schema::table('sh_zones', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_zones_code');
                } catch (\Throwable $e) {
                }
            });
        }
    }
};
